<?php

namespace App\Services;

use App\Session;
use App\Models\{User, Transaction};
use App\Models\Collections\AccountBalancesCollection;
use App\Repositories\{CurrenciesRepository, TransactionsRepository, UsersRepository};
use const App\CURRENCY_CODE;

class TransactionsService
{
    private ?UsersRepository $usersRepository;
    private ?TransactionsRepository $transactionsRepository;
    private ?CurrenciesRepository $currenciesRepository;

    public function __construct(
        UsersRepository        $usersRepository,
        TransactionsRepository $transactionsRepository,
        CurrenciesRepository   $currenciesRepository
    )
    {
        $this->usersRepository = $usersRepository;
        $this->transactionsRepository = $transactionsRepository;
        $this->currenciesRepository = $currenciesRepository;
        $this->addErrorMessagesToSession();
    }

    public function commitTransaction(Transaction $transaction): void
    {
        if (isset($this->transactionsRepository)) {
            $this->transactionsRepository->addTransaction($transaction);
        }
        $this->addErrorMessagesToSession();
        if (!Session::has('errors') && isset($this->usersRepository)) {
            $this->usersRepository->addMoneyToWallet(
                Session::get('userId'),
                -1 * floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100
            );
            $this->addErrorMessagesToSession();
        }
    }

    public function getUserBalances(int $userId): AccountBalancesCollection
    {
        $balances = (isset($this->transactionsRepository))
            ? $this->transactionsRepository->getBalances($userId)
            : new AccountBalancesCollection();
        $this->addErrorMessagesToSession();
        return $balances;
    }

    public function getPriceBySymbol(string $symbol): float
    {
        $currency = isset($this->transactionsRepository)
            ? iterator_to_array(
                $this->currenciesRepository
                    ->fetchCurrencies([$symbol], CURRENCY_CODE)
                    ->getCurrencies()
            )[0]->getPrice()
            : 0;
        $this->addErrorMessagesToSession();
        return $currency;
    }

    public function getUser(int $userId): User
    {
        $user = (isset($this->usersRepository))
            ? $this->usersRepository->getUser($userId)
            : new User();
        $this->addErrorMessagesToSession();
        return $user;
    }

    private function addErrorMessagesToSession(): void
    {
        $errorMessage = $this->usersRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'database');
        }
        $errorMessage = $this->transactionsRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'database');
        }
        $errorMessage = $this->currenciesRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'database');
        }
    }
}