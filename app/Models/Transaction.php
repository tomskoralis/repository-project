<?php

namespace App\Models;

class Transaction
{
    private int $userId;
    private string $symbol;
    private float $price;
    private float $amount;
    private string $dateTime;

    public function __construct(
        int       $userId,
        string    $symbol,
        float     $price,
        float     $amount,
        string $dateTime
    )
    {
        $this->userId = $userId;
        $this->symbol = $symbol;
        $this->price = $price;
        $this->amount = $amount;
        $this->dateTime = $dateTime;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDateTime(): string
    {
        return $this->dateTime;
    }
}