<?php

namespace App\Repositories;

use App\Models\Error;
use App\Models\Collections\CurrenciesCollection;

interface CurrenciesRepository
{
    public static function getError(): ?Error;

    public static function fetchCurrencies(array $symbols, string $currencyConvertType): CurrenciesCollection;
}