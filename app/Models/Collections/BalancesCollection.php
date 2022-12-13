<?php

namespace App\Models\Collections;

use App\Models\Balance;

class BalancesCollection
{
    private array $balances;

    public function __construct(array $balances = [])
    {
        $this->balances = $balances;
    }

    public function getBalances(): \Generator
    {
        foreach ($this->balances as $balance) {
            yield $balance;
        }
    }

    public function addBalance(Balance $balance): void
    {
        $this->balances [] = $balance;
    }
}