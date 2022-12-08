<?php

namespace App\Controllers;

use App\Template;
use App\Services\CurrenciesService;
use const App\{CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE};

class CurrenciesController
{
    public function index(): Template
    {
        $currenciesService = new CurrenciesService();
        $currencies = $currenciesService->fetchCurrencies(CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE);
        $errorMessage = $currenciesService->getErrorMessage();
        return new Template ("templates/currencies.twig", [
            "currencies" => $currencies,
            "currencyCode" => CURRENCY_CODE,
            "currenciesError" => $errorMessage,
        ]);
    }
}