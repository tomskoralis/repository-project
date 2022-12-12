<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Models\{AccountBalance, Transaction};
use App\Services\{CurrenciesService, TransactionsService, UsersService};
use App\Validation\TransactionValidation;
use const App\CURRENCY_CODE;

class CurrencyController
{
    public function displayCurrency(array $currency): Template
    {
        $symbol = strtoupper($currency["symbol"]);
        $currency = iterator_to_array((new CurrenciesService())->fetchCurrencies([$symbol], CURRENCY_CODE)
                ->getCurrencies())[0];
        if (!empty($currency)) {
            $amountOwned = $this->getAmountOwned($symbol);
        }
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
        $price = $this->getPriceBySymbol($symbol);

        $transaction = new Transaction(
            Session::get('userId'),
            $symbol,
            $price,
            $amountToBuy,
            (new \DateTime())->format('c'),
        );

        if (!(new TransactionValidation($transaction))->canBuyCurrency()) {
            return new Redirect('/currency/' . $symbol);
        }

        $cost = floor($amountToBuy * $price * 100) / 100;
        (new TransactionsService())->addTransaction($transaction);
        (new UsersService())->addMoneyToWallet(Session::get('userId'), -1 * abs($cost));

        if (!Session::has('errors')) {
            $amountToBuy = rtrim(number_format($amountToBuy, 8, '.', ''), '0');
            $currencySymbol = (new \NumberFormatter(\Locale::getDefault() . "@currency=" . CURRENCY_CODE, \NumberFormatter::CURRENCY))
                ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
            Session::add("Successfully bought $amountToBuy $symbol for {$currencySymbol}{$cost}", 'flashMessages', 'wallet');
        }
        return new Redirect('/wallet');
    }

    public function sellCurrency(): Redirect
    {
        $amountToSell = floor((float)$_POST['amountToSell'] * 100000000) / 100000000;
        $symbol = $this->getSymbolFromUrl();
        $price = $this->getPriceBySymbol($symbol);

        $transaction = new Transaction(
            Session::get('userId'),
            $symbol,
            $price,
            -1 * abs($amountToSell),
            (new \DateTime())->format('c'),
        );

        if (!(new TransactionValidation($transaction))->canSellCurrency()) {
            return new Redirect('/currency/' . $symbol);
        }

        $cost = floor($amountToSell * $price * 100) / 100;
        (new TransactionsService())->addTransaction($transaction);
        (new UsersService())->addMoneyToWallet(Session::get('userId'), $cost);

        if (!Session::has('errors')) {
            $amountToSell = rtrim(number_format($amountToSell, 8, '.', ''), '0');
            $currencySymbol = (new \NumberFormatter(\Locale::getDefault() . "@currency=" . CURRENCY_CODE, \NumberFormatter::CURRENCY))
                ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
            Session::add("Successfully sold $amountToSell $symbol for {$currencySymbol}{$cost}", 'flashMessages', 'wallet');
        }
        return new Redirect('/wallet');
    }

    private function getAmountOwned(string $symbol): float
    {
        foreach ((new TransactionsService())->getUserBalances(Session::get('userId'))->getBalances() as $balance) {
            /** @var AccountBalance $balance */
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

    private function getPriceBySymbol(string $symbol): float
    {
        $currencies = (new CurrenciesService())->fetchCurrencies([$symbol], CURRENCY_CODE);
        $currency = iterator_to_array($currencies->getCurrencies())[0];
        return $currency->getPrice();
    }
}