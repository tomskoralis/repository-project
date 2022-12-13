<?php

namespace App\Models\Collections;

use App\Models\Transaction;

class TransactionsCollection
{
    private array $transactions;

    public function __construct(array $transactions = [])
    {
        $this->transactions = $transactions;
    }

    public function getTransactions(): \Generator
    {
        foreach ($this->transactions as $transaction) {
            yield $transaction;
        }
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions [] = $transaction;
    }
}