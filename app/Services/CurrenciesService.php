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
        $this->addErrorMessageToSession();
        if (empty($currency)) {
            Session::add('No market data found when searching for ' . $symbol,
                'errors', 'currencies'
            );
        }
        return (!empty($currency)) ? $currency[0] : null;
    }

    public function fetchCurrencies(array $symbols): CurrenciesCollection
    {
        $currencies = (isset($this->currenciesRepository))
            ? $this->currenciesRepository->fetchCurrencies($symbols, CURRENCY_CODE)
            : new CurrenciesCollection();
        $this->addErrorMessageToSession();
        if ($this->getCount($currencies->getCurrencies()) === 0) {
            Session::add('No market data found when searching for ' . join(',', $symbols),
                'errors', 'currencies'
            );
        }
        return $currencies;
    }

    private function addErrorMessageToSession(): void
    {
        $errorMessage = $this->currenciesRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'repository', 'currencies');
        }
    }

    private function getCount(\Generator $functor): int
    {
        $count = 0;
        foreach ($functor as $value) {
            $count++;
        }
        return $count;
    }
}