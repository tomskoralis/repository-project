<?php

namespace App\Services;

use App\Session;
use App\Models\{User, Transaction};
use App\Models\Collections\{TransactionsCollection, BalancesCollection};
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
        $this->addUsersErrorMessagesToSession();
        $this->transactionsRepository = $transactionsRepository;
        $this->addTransactionsErrorMessagesToSession();
        $this->currenciesRepository = $currenciesRepository;
        $this->addCurrenciesErrorMessagesToSession();
    }

    public function commitTransaction(Transaction $transaction): void
    {
        if (isset($this->transactionsRepository)) {
            $this->transactionsRepository->addTransaction($transaction);
        }
        $this->addTransactionsErrorMessagesToSession();
        if (!Session::has('errors') && isset($this->usersRepository)) {
            $this->usersRepository->addMoneyToWallet(
                Session::get('userId'),
                -1 * floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100
            );
            $this->addUsersErrorMessagesToSession();
        }
    }

    public function getUserTransactions(int $userId): TransactionsCollection
    {
        $transactions = (isset($this->transactionsRepository))
            ? $this->transactionsRepository->fetchTransactions($userId)
            : new TransactionsCollection();
        $this->addTransactionsErrorMessagesToSession();
        if ($this->getCount($transactions->getTransactions()) === 0) {
            Session::add('No transactions found!',
                'errors', 'transactions'
            );
        }
        return $transactions;
    }

    public function getUserBalances(int $userId): BalancesCollection
    {
        $balances = (isset($this->transactionsRepository))
            ? $this->transactionsRepository->fetchBalances($userId)
            : new BalancesCollection();
        $this->addTransactionsErrorMessagesToSession();
        return $balances;
    }

    public function getPriceBySymbol(string $symbol): float
    {
        $currency = isset($this->transactionsRepository)
            ? iterator_to_array(
                $this->currenciesRepository
                    ->fetchCurrencies([$symbol], CURRENCY_CODE)
                    ->getCurrencies()
            )
            : 0;
        if (empty($currency)) {
            Session::add('No market data found when searching for ' . $symbol,
                'errors', 'currencies'
            );
        }
        $this->addCurrenciesErrorMessagesToSession();
        return (!empty($currency)) ? $currency[0]->getPrice() : 0;
    }

    public function getUser(int $userId): User
    {
        $user = (isset($this->usersRepository))
            ? $this->usersRepository->fetchUser($userId)
            : new User();
        $this->addUsersErrorMessagesToSession();
        return $user;
    }

    private function addUsersErrorMessagesToSession(): void
    {
        $errorMessage = $this->usersRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'repository', 'users');
        }
    }

    private function addTransactionsErrorMessagesToSession(): void
    {
        $errorMessage = $this->transactionsRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'repository', 'transactions');
        }
    }

    private function addCurrenciesErrorMessagesToSession(): void
    {
        $errorMessage = $this->currenciesRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'repository', 'currencies');
        }
    }

    private function getCount(\Generator $functor): int
    {
        $count = 0;
        foreach ($functor as $value) {
            $count++;
        }
        return $count;
    }
}