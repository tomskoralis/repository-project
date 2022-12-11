<?php

namespace App\Services;

use App\Models\Collections\CurrenciesCollection;
use App\Session;
use App\Repositories\{CoinMarketCapCurrenciesRepository, CurrenciesRepository};

class CurrenciesService
{
    private ?CurrenciesRepository $currenciesRepository;

    public function __construct()
    {
        $this->currenciesRepository = new CoinMarketCapCurrenciesRepository();
        $this->addErrorMessageToSession();
    }

    public function fetchCurrencies(array $symbols, string $currencyType): CurrenciesCollection
    {
        $currencies = (isset($this->currenciesRepository))
            ? $this->currenciesRepository->fetchCurrencies($symbols, $currencyType)
            : new CurrenciesCollection();
        $this->addErrorMessageToSession();
        return $currencies;
    }

    private function addErrorMessageToSession(): void
    {
        $errorMessage = $this->currenciesRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'currencies');
        }
    }
}