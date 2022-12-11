<?php

namespace App\Models\Collections;

use App\Models\AccountBalance;

class AccountBalancesCollection
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

    public function addBalance(AccountBalance $balance): void
    {
        $this->balances [] = $balance;
    }
}