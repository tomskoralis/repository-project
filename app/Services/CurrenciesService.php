<?php

namespace App\Services;

use App\Models\Collections\CurrenciesCollection;
use App\Repositories\{MarketCapCurrenciesRepository, CurrenciesRepository};
use Dotenv\Dotenv;

class CurrenciesService
{
    private CurrenciesRepository $currenciesRepository;
    private ?string $errorMessage;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__, "../../.env");
        $dotenv->load();
        $this->currenciesRepository = new MarketCapCurrenciesRepository($dotenv);
        $this->errorMessage = $this->currenciesRepository->getErrorMessage();
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage ?? null;
    }

    public function fetchCurrencies(array $symbols, string $currencyType): CurrenciesCollection
    {
        if ($this->errorMessage) {
            return new CurrenciesCollection();
        }
        $currencies = $this->currenciesRepository->fetchCurrencies($symbols, $currencyType);
        $this->errorMessage = $this->currenciesRepository->getErrorMessage();
        return $currencies;
    }
}