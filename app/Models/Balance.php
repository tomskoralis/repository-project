<?php

namespace App\Models;

class Balance
{
    private int $id;
    private string $symbol;
    private float $amount;
    private ?float $value;

    public function __construct(
        int    $id,
        string $symbol,
        float  $amount,
        ?float $value = null
    )
    {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->amount = $amount;
        $this->value = $value;
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

    public function getValue(): ?float
    {
        return $this->value;
    }
}