<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Collections\{BalancesCollection, TransactionsCollection};

interface TransactionsRepository
{
    public function getErrorMessage(): ?string;

    public function fetchTransactions(int $userId): TransactionsCollection;

    public function fetchBalances(int $userId): BalancesCollection;

    public function addTransaction(Transaction $transaction): void;
}