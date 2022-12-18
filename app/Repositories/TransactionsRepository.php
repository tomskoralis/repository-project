<?php

namespace App\Repositories;

use App\Models\{Transaction, Error};
use App\Models\Collections\{BalancesCollection, TransactionsCollection};

interface TransactionsRepository
{
    public static function getError(): ?Error;

    public static function fetchTransactions(int $userId, string $symbol = ''): TransactionsCollection;

    public static function fetchBalances(int $userId, string $symbol = ''): BalancesCollection;

    public static function add(Transaction $transaction): void;
}