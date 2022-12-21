<?php

namespace App\Models;

use App\Models\Collections\CurrencyStatisticsCollection;

class TransactionStatistics
{
    private float $totalAmount;
    private float $averagePrice;
    private float $totalIncome;
    private float $totalExpenditure;
    private float $walletValue;
    private float $totalProfit;
    private CurrencyStatisticsCollection $statistics;

    public function __construct(
        float                        $totalAmount,
        float                        $averagePrice,
        float                        $totalRevenue,
        float                        $totalExpenses,
        float                        $walletValue,
        float                        $totalProfit,
        CurrencyStatisticsCollection $statistics
    )
    {
        $this->totalAmount = $totalAmount;
        $this->averagePrice = $averagePrice;
        $this->totalIncome = $totalRevenue;
        $this->totalExpenditure = $totalExpenses;
        $this->walletValue = $walletValue;
        $this->totalProfit = $totalProfit;
        $this->statistics = $statistics;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getAveragePrice(): float
    {
        return $this->averagePrice;
    }

    public function getTotalIncome(): float
    {
        return $this->totalIncome;
    }

    public function getTotalExpenditure(): float
    {
        return $this->totalExpenditure;
    }

    public function getWalletValue(): float
    {
        return $this->walletValue;
    }

    public function getTotalProfit(): float
    {
        return $this->totalProfit;
    }

    public function getStatistics(): CurrencyStatisticsCollection
    {
        return $this->statistics;
    }
}