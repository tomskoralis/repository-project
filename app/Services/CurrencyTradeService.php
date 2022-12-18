<?php

namespace App\Services;

use App\Container;
use App\Models\Transaction;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\{UsersRepository, CurrenciesRepository, TransactionsRepository};
use App\Validation\TransactionValidation;

class CurrencyTradeService
{
    private UsersRepository $usersRepository;
    private CurrenciesRepository $currenciesRepository;
    private TransactionsRepository $transactionsRepository;
    private ErrorsCollection $errors;
    private float $price;

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
    ): void
    {
        if ($this->repositoriesHaveErrors()) {
            return;
        }

        $this->price = $this->currenciesRepository
            ->fetchCurrencies([$symbol], $currencyType)
            ->getAll()
            ->current()
            ->getPrice();

        $transaction = new Transaction(
            $userId,
            $symbol,
            $this->price,
            floor((float)$amount * 100000000) / 100000000
        );

        $validation = Container::get(TransactionValidation::class);
        /** @var TransactionValidation $validation */
        if (
            !$validation->isAmountValid($amount) ||
            !$validation->canBuyCurrency($transaction)
        ) {
            $this->errors = $validation->getErrors();
            return;
        }

        $this->transactionsRepository::add($transaction);
        $this->usersRepository::addMoneyToWallet(
            $transaction->getUserId(),
            -1 * floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100
        );
    }

    public function sellCurrency(
        int    $userId,
        string $symbol,
        string $amount,
        string $currencyType
    ): void
    {
        if ($this->repositoriesHaveErrors()) {
            return;
        }

        $this->price = $this->currenciesRepository
            ->fetchCurrencies([$symbol], $currencyType)
            ->getAll()
            ->current()
            ->getPrice();

        $transaction = new Transaction(
            $userId,
            $symbol,
            $this->price,
            -1 * floor((float)$amount * 100000000) / 100000000
        );

        $validation = Container::get(TransactionValidation::class);
        /** @var TransactionValidation $validation */
        if (
            !$validation->isAmountValid($amount) ||
            !$validation->canSellCurrency($transaction)
        ) {
            $this->errors = $validation->getErrors();
            return;
        }

        $this->transactionsRepository::add($transaction);
        $this->usersRepository::addMoneyToWallet(
            $transaction->getUserId(),
            -1 * floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100
        );
    }

    public function getPrice(): float
    {
        return $this->price ?? 0;
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