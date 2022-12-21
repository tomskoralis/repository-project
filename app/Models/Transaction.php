<?php

namespace App\Models;

class Transaction
{
    private int $userId;
    private string $symbol;
    private float $price;
    private float $amount;
    private ?int $senderId;
    private ?string $dateTime;
    private ?string $senderName;
    private ?float $commission;

    public function __construct(
        int     $userId,
        string  $symbol,
        float   $price,
        float   $amount,
        ?int    $senderId = null,
        ?string $dateTime = null,
        ?string $senderName = null,
        ?float $commission = null
    )
    {
        $this->userId = $userId;
        $this->symbol = $symbol;
        $this->price = $price;
        $this->amount = $amount;
        $this->senderId = $senderId;
        $this->dateTime = $dateTime;
        $this->senderName = $senderName;
        $this->commission = $commission;
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

    public function getSenderId(): ?int
    {
        return $this->senderId;
    }

    public function getDateTime(): ?string
    {
        return $this->dateTime;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setSenderName(string $name): void
    {
        $this->senderName = $name;
    }

    public function setCommission(?float $commission): void
    {
        $this->commission = $commission;
    }
}