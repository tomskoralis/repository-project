<?php

namespace App\Models\Collections;

use App\Models\Transaction;

class TransactionsCollection extends Collection
{
    public function add(Transaction $item): void
    {
        $this->items [] = $item;
    }
}