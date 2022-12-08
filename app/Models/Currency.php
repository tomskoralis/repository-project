<?php

namespace App\Models;

class Currency
{
    private string $name;
    private float $price;
    private float $change;

    public function __construct(string $name, float $price, float $change)
    {
        $this->name = $name;
        $this->price = $price;
        $this->change = $change;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getChange(): float
    {
        return $this->change;
    }
}