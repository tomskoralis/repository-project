<?php

namespace App\Services;

use App\Models\Collections\AccountBalancesCollection;
use App\Models\Transaction;
use App\Repositories\DatabaseTransactionsRepository;
use App\Session;

class TransactionsService
{
    private ?DatabaseTransactionsRepository $database;

    public function __construct()
    {
        $this->database = new DatabaseTransactionsRepository();
        $this->addErrorMessageToSession();
    }

    public function getUserBalances(int $userId): AccountBalancesCollection
    {
        $balances = (isset($this->database))
            ? $this->database->getBalances($userId)
            : new AccountBalancesCollection();
        $this->addErrorMessageToSession();
        return $balances;
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (isset($this->database)) {
            $this->database->addTransaction($transaction);
        }
        $this->addErrorMessageToSession();
    }

    private function addErrorMessageToSession(): void
    {
        $errorMessage = $this->database->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'database');
        }
    }
}