<?php

namespace App\Models;

class CurrencyPrice
{
    private string $symbol;
    private float $price;

    public function __construct(
        string $symbol,
        float  $price
    )
    {
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}