<?php

namespace App\Models;

class CurrencyStatistic
{
    private string $symbol;
    private float $amount;
    private float $averagePrice;
    private float $income;
    private float $expenditure;
    private float $walletValue;
    private float $profit;

    public function __construct(
        string $symbol,
        float  $amount,
        float  $averagePrice,
        float  $income,
        float  $expenditure,
        float  $walletValue,
        float  $profit
    )
    {
        $this->symbol = $symbol;
        $this->amount = $amount;
        $this->averagePrice = $averagePrice;
        $this->income = $income;
        $this->expenditure = $expenditure;
        $this->walletValue = $walletValue;
        $this->profit = $profit;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getAveragePrice(): float
    {
        return $this->averagePrice;
    }

    public function getIncome(): float
    {
        return $this->income;
    }

    public function getExpenditure(): float
    {
        return $this->expenditure;
    }

    public function getWalletValue(): float
    {
        return $this->walletValue;
    }

    public function getProfit(): float
    {
        return $this->profit;
    }
}