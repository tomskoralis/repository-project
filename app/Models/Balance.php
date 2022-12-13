<?php

namespace App\Models;

class Balance
{
    private string $symbol;
    private float $balance;

    public function __construct(string $symbol, float $balance)
    {
        $this->symbol = $symbol;
        $this->balance = $balance;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): float
    {
        return $this->balance;
    }
}