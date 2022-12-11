<?php

namespace App\Controllers;

use App\Template;
use App\Services\CurrenciesService;
use const App\{CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE};

class ListCurrenciesController
{
    public function index(): Template
    {
        $currenciesService = new CurrenciesService();
        $currencies = $currenciesService->fetchCurrencies(CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE);
        return new Template ('templates/list.twig', [
            'currencies' => $currencies->getCurrencies(),
            'currencyCode' => CURRENCY_CODE,
        ]);
    }
}