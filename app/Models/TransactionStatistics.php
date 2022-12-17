<?php

namespace App\Models;

use App\Models\Collections\PriceCollection;

class TransactionStatistics
{

    private float $spent;
    private float $revenue;
    private float $walletValue;
    private float $profit;
    private PriceCollection $averagePrices;

    public function __construct(
        float           $spent,
        float           $revenue,
        float           $walletValue,
        float           $profit,
        PriceCollection $averagePrices
    )
    {
        $this->spent = $spent;
        $this->revenue = $revenue;
        $this->walletValue = $walletValue;
        $this->profit = $profit;
        $this->averagePrices = $averagePrices;
    }

    public function getSpent(): float
    {
        return $this->spent;
    }

    public function getRevenue(): float
    {
        return $this->revenue;
    }

    public function getWalletValue(): float
    {
        return $this->walletValue;
    }

    public function getProfit(): float
    {
        return $this->profit;
    }

    public function getAveragePrices(): PriceCollection
    {
        return $this->averagePrices;
    }
}