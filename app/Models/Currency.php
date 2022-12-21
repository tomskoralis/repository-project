<?php

namespace App\Models;

class Currency
{
    private string $symbol;
    private string $name;
    private float $price;
    private float $change1h;
    private float $change24h;
    private float $change7d;
    private ?float $amountOwned;

    public function __construct(
        string $symbol,
        string $name,
        float  $price,
        float  $change1h,
        float  $change24h,
        float  $change7d,
        ?float $amountOwned = null
    )
    {
        $this->symbol = $symbol;
        $this->name = $name;
        $this->price = $price;
        $this->change1h = $change1h;
        $this->change24h = $change24h;
        $this->change7d = $change7d;
        $this->amountOwned = $amountOwned;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getChange1h(): float
    {
        return $this->change1h;
    }

    public function getChange24h(): float
    {
        return $this->change24h;
    }

    public function getChange7d(): float
    {
        return $this->change7d;
    }

    public function getAmountOwned(): ?float
    {
        return $this->amountOwned;
    }

    public function setAmountOwned(float $amountOwned): void
    {
        $this->amountOwned = $amountOwned;
    }
}