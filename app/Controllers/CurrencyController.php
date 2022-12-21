<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\{CurrencySearchService, CurrencyTradeService};
use const App\{CURRENCY_CODE, SHORT_SELL_COMMISSION};

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
            CURRENCY_CODE,
            Session::get('userId')
        );

        Session::addErrors($this->currencySearchService->getErrors());

        return new Template ('templates/currency.twig', [
            'currency' => $currency,
            'currencyCode' => CURRENCY_CODE,
        ]);
    }

    public function buyCurrency(array $symbol): Redirect
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to buy currency!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $symbol = strtoupper($symbol['symbol']);
        $transaction = $this->currencyTradeService->buyCurrency(
            Session::get('userId'),
            $symbol,
            $_POST['amountToBuy'] ?? '0',
            CURRENCY_CODE
        );
        Session::addErrors($this->currencyTradeService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        Session::add(
            'Successfully bought ' . round($transaction->getAmount(), 8) . ' ' .
            $transaction->getSymbol() . ' for ' . $this->getCurrencySymbol() .
            number_format(round($transaction->getAmount() * $transaction->getPrice(), 2), 2, '.', ''),
            'flashMessages', 'currency'
        );
        return new Redirect('/currency/' . $symbol);
    }

    public function sellCurrency(array $symbol): Redirect
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to sell currency!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $symbol = strtoupper($symbol['symbol']);
        $transaction = $this->currencyTradeService->sellCurrency(
            Session::get('userId'),
            $symbol,
            $_POST['amountToSell'] ?? '0',
            CURRENCY_CODE,
            SHORT_SELL_COMMISSION
        );

        Session::addErrors($this->currencyTradeService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/currency/' . $symbol);
        }

        Session::add(
            'Successfully sold ' . round(abs($transaction->getAmount()), 8) . ' ' .
            $transaction->getSymbol() . ' for ' . $this->getCurrencySymbol() .
            number_format(round(abs($transaction->getAmount()) * $transaction->getPrice(), 2), 2, '.', '') .
            ($transaction->getCommission() > 0 ? ' (' . SHORT_SELL_COMMISSION * 100 . '% commission ' . $this->getCurrencySymbol() .
                number_format(round(abs($transaction->getCommission()), 2), 2, '.', '') . ')' : ''),
            'flashMessages', 'currency'
        );
        return new Redirect('/currency/' . $symbol);
    }

    private function getCurrencySymbol(): string
    {
        return (new \NumberFormatter(\Locale::getDefault() . '@currency=' . CURRENCY_CODE, \NumberFormatter::CURRENCY))
            ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }
}