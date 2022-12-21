<?php

namespace App\Services;

use App\Container;
use App\Models\Transaction;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\{UsersRepository, CurrenciesRepository, TransactionsRepository};
use App\Validation\TransactionValidation;
use const App\MAINTENANCE_MARGIN;

class CurrencyTradeService
{
    private UsersRepository $usersRepository;
    private CurrenciesRepository $currenciesRepository;
    private TransactionsRepository $transactionsRepository;
    private ErrorsCollection $errors;

    public function __construct(
        UsersRepository        $usersRepository,
        CurrenciesRepository   $currenciesRepository,
        TransactionsRepository $transactionsRepository
    )
    {
        $this->usersRepository = $usersRepository;
        $this->currenciesRepository = $currenciesRepository;
        $this->transactionsRepository = $transactionsRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function buyCurrency(
        int    $userId,
        string $symbol,
        string $amount,
        string $currencyType
    ): ?Transaction
    {
        if ($this->repositoriesHaveErrors()) {
            return null;
        }

        $transaction = new Transaction(
            $userId,
            $symbol,
            $this->currenciesRepository
                ::fetchCurrencies([$symbol], $currencyType)
                ->getAll()
                ->current()
                ->getPrice(),
            round((float)$amount, 8)
        );

        $validation = Container::get(TransactionValidation::class);
        /** @var TransactionValidation $validation */
        if (
            !$validation->isAmountValid($amount) ||
            !$validation->canBuyCurrency($transaction)
        ) {
            $this->errors = $validation->getErrors();
            return null;
        }

        $this->transactionsRepository::add($transaction);
        $this->usersRepository::addMoney(
            $transaction->getUserId(),
            -1 * floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100
        );
        return $transaction;
    }

    public function sellCurrency(
        int    $userId,
        string $symbol,
        string $amount,
        string $currencyType,
        float  $commission
    ): ?Transaction
    {
        if ($this->repositoriesHaveErrors()) {
            return null;
        }
        $transaction = new Transaction(
            $userId,
            $symbol,
            $this->currenciesRepository
                ::fetchCurrencies([$symbol], $currencyType)
                ->getAll()
                ->current()
                ->getPrice(),
            -1 * round((float)$amount, 8)
        );

        $validation = Container::get(TransactionValidation::class);
        /** @var TransactionValidation $validation */
        if (
            !$validation->isAmountValid($amount) ||
            !$validation->canSellCurrency($transaction, MAINTENANCE_MARGIN)
        ) {
            $this->errors = $validation->getErrors();
            return null;
        }

        $balance = $this->transactionsRepository::fetchBalances(
            $transaction->getUserId(),
            $transaction->getSymbol()
        )->getAll()->current();

        $amountToCommission = abs($transaction->getAmount()) -
            (isset($balance) && $balance->getAmount() > 0 ? $balance->getAmount() : 0);
        $commissionFee = $amountToCommission * $transaction->getPrice() * $commission;

        $this->transactionsRepository::add($transaction);

        $transaction->setCommission($commissionFee);

        $this->usersRepository::addMoney(
            $transaction->getUserId(),
            -1 * floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100 - $commissionFee
        );
        return $transaction;
    }

    private function repositoriesHaveErrors(): bool
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return true;
        }
        $error = $this->currenciesRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return true;
        }
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return true;
        }
        return false;
    }
}