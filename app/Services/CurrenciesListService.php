<?php

namespace App\Services;

use App\Models\Error;
use App\Models\Collections\{CurrenciesCollection, ErrorsCollection};
use App\Repositories\CurrenciesRepository;

class CurrenciesListService
{
    private CurrenciesRepository $currenciesRepository;
    private ErrorsCollection $errors;

    public function __construct(CurrenciesRepository $currenciesRepository)
    {
        $this->currenciesRepository = $currenciesRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function getCurrencies(array $symbols, string $currencyType): CurrenciesCollection
    {
        $error = $this->currenciesRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return new CurrenciesCollection();
        }

        $currencies = $this->currenciesRepository::fetchCurrencies($symbols, $currencyType);
        if ($currencies->getCount() === 0) {
            $this->errors->add(
                new Error(
                    'No market data found when searching for ' . join(',', $symbols),
                    'nothingFound'
                )
            );
        }
        return $currencies;
    }
}