<?php

namespace App\Repositories;

use App\Models\{Transaction, Error};
use App\Models\Collections\{BalancesCollection, TransactionsCollection};

interface TransactionsRepository
{
    public static function getError(): ?Error;

    public static function fetchTransactionsById(int $userId): TransactionsCollection;

    public static function fetchBalancesById(int $userId, ?string $symbol = null): BalancesCollection;

    public static function add(Transaction $transaction): void;
}