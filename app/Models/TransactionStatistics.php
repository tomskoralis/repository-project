<?php

namespace App\Models;

use App\Models\Collections\PriceCollection;

class TransactionStatistics
{

    private float $revenue;
    private float $value;
    private float $expenses;
    private float $profit;
    private ?PriceCollection $averages;

    public function __construct(
        float            $totalRevenue = 0,
        float            $walletValue = 0,
        float            $totalExpenses = 0,
        float            $totalProfit = 0,
        ?PriceCollection $averagePrices = null
    )
    {
        $this->revenue = $totalRevenue;
        $this->value = $walletValue;
        $this->expenses = $totalExpenses;
        $this->profit = $totalProfit;
        $this->averages = $averagePrices;
    }

    public function getRevenue(): float
    {
        return $this->revenue;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getExpenses(): float
    {
        return $this->expenses;
    }

    public function getProfit(): float
    {
        return $this->profit;
    }

    public function getAverages(): ?PriceCollection
    {
        return $this->averages;
    }
}