<?php

namespace App\Services;

use App\Models\{Currency, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\{CurrenciesRepository, TransactionsRepository};

class CurrencySearchService
{
    private CurrenciesRepository $currenciesRepository;
    private TransactionsRepository $transactionsRepository;
    private ErrorsCollection $errors;

    public function __construct(
        CurrenciesRepository   $currenciesRepository,
        TransactionsRepository $transactionsRepository
    )
    {
        $this->currenciesRepository = $currenciesRepository;
        $this->transactionsRepository = $transactionsRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function fetchSingleCurrency(string $symbol, string $currencyType): ?Currency
    {
        $error = $this->currenciesRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return null;
        }

        $currency = $this->currenciesRepository::fetchCurrencies([$symbol], $currencyType)->getAll()->current();

        if (empty($currency)) {
            $this->errors->add(
                new Error(
                    'No market data found when searching for ' . $symbol,
                    'nothingFound'
                )
            );
        }
        return $currency;
    }

    public function getAmountOwned($userId, $symbol): float
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return 0;
        }
        $balance = $this->transactionsRepository::fetchBalancesById($userId, $symbol)->getAll()->current();
        if (isset($balance) && $balance->getSymbol() === $symbol) {
            return $balance->getAmount();

        }
        return 0;
    }
}