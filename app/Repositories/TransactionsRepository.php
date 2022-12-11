<?php

namespace App\Repositories;

use App\Models\Collections\AccountBalancesCollection;
use App\Models\Transaction;

interface TransactionsRepository
{
    public function getErrorMessage(): ?string;

    public function getBalances(int $userId): AccountBalancesCollection;

    public function addTransaction(Transaction $transaction): void;
}