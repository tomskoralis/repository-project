<?php

namespace App\Repositories;

use App\Models\Collections\CurrenciesCollection;

interface CurrenciesRepository
{
    public function fetchCurrencies(array $symbols, string $currencyType): CurrenciesCollection;

    public function getErrorMessage(): ?string;
}