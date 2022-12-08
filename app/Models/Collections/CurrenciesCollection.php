<?php

namespace App\Models\Collections;

use App\Models\Currency;

class CurrenciesCollection
{
    private array $currencies;

    public function __construct(array $currencies = [])
    {
        $this->currencies = $currencies;
    }

    public function getCurrencies(): \Generator
    {
        foreach ($this->currencies as $currency) {
            yield $currency;
        }
    }

    public function addCurrency(Currency $currency): void
    {
        $this->currencies [] = $currency;
    }
}