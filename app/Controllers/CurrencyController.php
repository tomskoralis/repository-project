<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Models\{Balance, Transaction};
use App\Services\{CurrenciesService, TransactionsService};
use App\Validation\TransactionValidation;
use const App\CURRENCY_CODE;

class CurrencyController
{
    private CurrenciesService $currenciesService;
    private TransactionsService $transactionsService;

    public function __construct(
        CurrenciesService   $currenciesService,
        TransactionsService $transactionsService
    )
    {
        $this->currenciesService = $currenciesService;
        $this->transactionsService = $transactionsService;
    }

    public function displayCurrency(array $symbol): Template
    {
        $symbol = strtoupper($symbol["symbol"]);
        $currency = $this->currenciesService->fetchSingleCurrency($symbol);
        $amountOwned = (Session::has('userId') && !empty($currency)) ? $this->getAmountOwned($symbol) : 0;
        return new Template ('templates/currency.twig', [
            'currency' => $currency,
            'currencyCode' => CURRENCY_CODE,
            'amountOwned' => $amountOwned,
        ]);
    }

    public function buyCurrency(): Redirect
    {
        $amountToBuy = floor((float)$_POST['amountToBuy'] * 100000000) / 100000000;
        $symbol = $this->getSymbolFromUrl();
        $price = $this->transactionsService->getPriceBySymbol($symbol);
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        $transaction = new Transaction(
            Session::get('userId'),
            $symbol,
            $price,
            $amountToBuy,
            (new \DateTime())->format('c'),
        );

        if (!(new TransactionValidation($transaction, $this->transactionsService))->canBuyCurrency()) {
            return new Redirect('/currency/' . $symbol);
        }

        $this->transactionsService->commitTransaction($transaction);
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        $amountToBuy = rtrim(number_format($amountToBuy, 8, '.', ''), '0.');
        $cost = floor($amountToBuy * $price * 100) / 100;
        Session::add(
            "Successfully bought $amountToBuy $symbol for {$this->getCurrencySymbol()}$cost",
            'flashMessages', 'wallet'
        );
        return new Redirect('/wallet');
    }

    public function sellCurrency(): Redirect
    {
        $amountToSell = floor((float)$_POST['amountToSell'] * 100000000) / 100000000;
        $symbol = $this->getSymbolFromUrl();
        $price = $this->transactionsService->getPriceBySymbol($symbol);
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        $transaction = new Transaction(
            Session::get('userId'),
            $symbol,
            $price,
            -1 * $amountToSell,
            (new \DateTime())->format('c'),
        );

        if (!(new TransactionValidation($transaction, $this->transactionsService))->canSellCurrency()) {
            return new Redirect('/currency/' . $symbol);
        }

        $this->transactionsService->commitTransaction($transaction);
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        $amountToSell = rtrim(number_format($amountToSell, 8, '.', ''), '0.');
        $cost = floor($amountToSell * $price * 100) / 100;
        Session::add(
            "Successfully sold $amountToSell $symbol for {$this->getCurrencySymbol()}$cost",
            'flashMessages', 'wallet'
        );
        return new Redirect('/wallet');
    }

    private function getAmountOwned(string $symbol): float
    {
        foreach ($this->transactionsService->getUserBalances(Session::get('userId'))->getBalances() as $balance) {
            /** @var Balance $balance */
            if ($balance->getSymbol() === $symbol) {
                return $balance->getAmount();
            }
        }
        return 0;
    }

    private function getSymbolFromUrl(): string
    {
        $urlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        $urlTokens = explode('/', $urlPath);
        return strtoupper(end($urlTokens));
    }

    private function getCurrencySymbol(): string
    {
        return (new \NumberFormatter(\Locale::getDefault() . "@currency=" . CURRENCY_CODE, \NumberFormatter::CURRENCY))
            ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }
}