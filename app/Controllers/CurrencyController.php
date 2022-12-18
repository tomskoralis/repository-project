<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\{CurrencySearchService, CurrencyTradeService};
use const App\CURRENCY_CODE;

class CurrencyController
{
    private CurrencySearchService $currencySearchService;
    private CurrencyTradeService $currencyTradeService;

    public function __construct(
        CurrencySearchService $currencySearchService,
        CurrencyTradeService  $currencyTradeService
    )
    {
        $this->currencySearchService = $currencySearchService;
        $this->currencyTradeService = $currencyTradeService;
    }

    public function showCurrency(array $symbol): Template
    {
        $currency = $this->currencySearchService->getCurrency(
            strtoupper($symbol['symbol']),
            CURRENCY_CODE
        );
        $amountOwned = (Session::has('userId') && !empty($currency))
            ? $this->currencySearchService->getAmountOwned(
                Session::get('userId'),
                $currency->getSymbol()
            )
            : 0;
        Session::addErrors($this->currencySearchService->getErrors());

        return new Template ('templates/currency.twig', [
            'currency' => $currency,
            'currencyCode' => CURRENCY_CODE,
            'amountOwned' => $amountOwned,
        ]);
    }

    public function buyCurrency(): Redirect
    {
        $amountToBuy = $_POST['amountToBuy'] ?? '0';
        $symbol = $this->getSymbolFromUrl();

        $this->currencyTradeService->buyCurrency(
            Session::get('userId'),
            $symbol,
            $amountToBuy,
            CURRENCY_CODE
        );
        Session::addErrors($this->currencyTradeService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        Session::add(
            'Successfully bought ' .
            rtrim(rtrim(number_format($amountToBuy, 8, '.', ''), '0'), '.') .
            ' ' . $symbol . ' for ' . $this->getCurrencySymbol() .
            floor($amountToBuy * $this->currencyTradeService->getPrice() * 100) / 100,
            'flashMessages', 'currency'
        );
        return new Redirect('/currency/' . $symbol);
    }

    public function sellCurrency(): Redirect
    {
        $amountToSell = floor((float)$_POST['amountToSell'] * 100000000) / 100000000;
        $symbol = $this->getSymbolFromUrl();

        $this->currencyTradeService->sellCurrency(
            Session::get('userId'),
            $symbol,
            $amountToSell,
            CURRENCY_CODE
        );
        Session::addErrors($this->currencyTradeService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        Session::add(
            'Successfully sold ' .
            rtrim(rtrim(number_format($amountToSell, 8, '.', ''), '0'), '.') .
            ' ' . $symbol . ' for ' . $this->getCurrencySymbol() .
            floor($amountToSell * $this->currencyTradeService->getPrice() * 100) / 100,
            'flashMessages', 'currency'
        );
        return new Redirect('/currency/' . $symbol);
    }

    private function getSymbolFromUrl(): string
    {
        $urlTokens = explode(
            '/',
            parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH)
        );
        return strtoupper(end($urlTokens));
    }

    private function getCurrencySymbol(): string
    {
        return (new \NumberFormatter(\Locale::getDefault() . '@currency=' . CURRENCY_CODE, \NumberFormatter::CURRENCY))
            ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }
}