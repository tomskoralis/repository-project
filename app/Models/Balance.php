<?php

namespace App\Models;

class Balance
{
    private int $id;
    private string $symbol;
    private float $amount;

    public function __construct(
        int    $id,
        string $symbol,
        float  $amount
    )
    {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->amount = $amount;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}