<?php

namespace App\Services;

use App\Session;
use App\Models\Currency;
use App\Models\Collections\CurrenciesCollection;
use App\Repositories\CurrenciesRepository;
use const App\CURRENCY_CODE;

class CurrenciesService
{
    private ?CurrenciesRepository $currenciesRepository;

    public function __construct(CurrenciesRepository $currenciesRepository)
    {
        $this->currenciesRepository = $currenciesRepository;
        $this->addErrorMessageToSession();
    }

    public function fetchSingleCurrency(string $symbol): ?Currency
    {
        $currency = iterator_to_array(
            $this->currenciesRepository
                ->fetchCurrencies([$symbol], CURRENCY_CODE)
                ->getCurrencies()
        );
        return (!empty($currency)) ? $currency[0] : null;
    }

    public function fetchCurrencies(array $symbols): ?CurrenciesCollection
    {
        $currencies = (isset($this->currenciesRepository))
            ? $this->currenciesRepository->fetchCurrencies($symbols, CURRENCY_CODE)
            : null;
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