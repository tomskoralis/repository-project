<?php

namespace App\Controllers;

use App\{Template, Session};
use App\Services\CurrenciesListService;
use const App\{CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE};

class CurrenciesController
{
    private CurrenciesListService $currenciesListService;

    public function __construct(CurrenciesListService $currenciesListService)
    {
        $this->currenciesListService = $currenciesListService;
    }

    public function showCurrenciesList(): Template
    {
        $currencies = $this->currenciesListService->getCurrencies(CRYPTOCURRENCY_SYMBOLS, CURRENCY_CODE);
        Session::addErrors($this->currenciesListService->getErrors());

        return new Template ('templates/list.twig', [
            'currencies' => $currencies->getAll(),
            'currencyCode' => CURRENCY_CODE,
        ]);
    }
}