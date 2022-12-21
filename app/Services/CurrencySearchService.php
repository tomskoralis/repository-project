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

    public function getCurrency(string $symbol, string $currencyType, ?int $userId = null): ?Currency
    {
        $error = $this->currenciesRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return null;
        }

        /** @var Currency $currency */
        $currency = $this->currenciesRepository::fetchCurrencies([$symbol], $currencyType)->getAll()->current();
        if (empty($currency)) {
            $this->errors->add(
                new Error(
                    'No market data found when searching for ' . $symbol,
                    'nothingFound'
                )
            );
            return null;
        }

        $currency->setAmountOwned(
            (isset($userId))
                ? $this->getAmountOwned($userId, $currency->getSymbol())
                : 0
        );

        return $currency;
    }

    private function getAmountOwned($userId, $symbol): float
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return 0;
        }
        $balance = $this->transactionsRepository::fetchBalances($userId, $symbol)->getAll()->current();
        if (isset($balance) && $balance->getSymbol() === $symbol) {
            return $balance->getAmount();
        }
        return 0;
    }
}