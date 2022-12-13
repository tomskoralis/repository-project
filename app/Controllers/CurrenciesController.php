<?php

namespace App\Controllers;

use App\Template;
use App\Services\CurrenciesService;
use const App\{CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE};

class CurrenciesController
{
    private CurrenciesService $currenciesService;

    public function __construct(CurrenciesService $currenciesService)
    {
        $this->currenciesService = $currenciesService;
    }

    public function displayCurrenciesList(): Template
    {
        $currencies = $this->currenciesService->fetchCurrencies(CRYPTOCURRENCY_SYMBOLS);
        return new Template ('templates/list.twig', [
            'currencies' => $currencies->getCurrencies(),
            'currencyCode' => CURRENCY_CODE,
        ]);
    }
}