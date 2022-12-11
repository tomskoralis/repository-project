<?php

namespace App\Controllers;

use App\{Models\AccountBalance, Template, Redirect, Session};
use App\Models\Transaction;
use App\Services\{CurrenciesService, TransactionsService, UsersService};
use const App\CURRENCY_CODE;

class CurrencyController
{
    public function displayCurrency(array $currency): Template
    {
        $symbol = strtoupper($currency["symbol"]);
        $currency = (new CurrenciesService())->fetchCurrencies([$symbol], CURRENCY_CODE);
        $amountOwned = $this->getAmountOwned($symbol);
        return new Template ('templates/currency.twig', [
            'currency' => iterator_to_array($currency->getCurrencies())[0],
            'currencyCode' => CURRENCY_CODE,
            'amountOwned' => $amountOwned,
        ]);
    }

    public function buyCurrency(): Redirect
    {
        $amountToBuy = floor((float)$_POST['amountToBuy'] * 100000000) / 100000000;
        $symbol = $this->getSymbolFromUrl();

        if ($amountToBuy <= 0) {
            Session::add('Incorrect amount!', 'errors', 'currency');
            return new Redirect('/currency/' . $symbol);
        }

        $price = $this->getPriceBySymbol($symbol);
        $usersService = new UsersService();
        $moneyAvailable = $usersService->getUser(Session::get('userId'))->getWallet();
        $cost = floor($amountToBuy * $price * 100) / 100;

        if ($cost < 0.01) {
            Session::add('Too small cost to buy!', 'errors', 'currency');
            return new Redirect('/currency/' . $symbol);
        }

        if ($moneyAvailable < $cost) {
            Session::add('Not enough money win wallet!', 'errors', 'currency');
            return new Redirect('/currency/' . $symbol);
        }

        (new TransactionsService())->addTransaction(new Transaction(
            Session::get('userId'),
            $symbol,
            $price,
            $amountToBuy,
            (new \DateTime())->format('c'),
        ));

        $usersService->addMoneyToWallet(Session::get('userId'), -1 * abs($cost));

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

        if ($this->canSellCurrency($symbol, $amountToSell)) {
            (new TransactionsService())->addTransaction(new Transaction(
                Session::get('userId'),
                $symbol,
                $price,
                -1 * abs($amountToSell),
                (new \DateTime())->format('c'),
            ));
        } else {
            Session::add('You do not own enough to sell that much!', 'errors', 'currency');
            return new Redirect('/currency/' . $symbol);
        }

        $cost = floor($amountToSell * $price * 100) / 100;

        if ($cost < 0.01) {
            Session::add('Too small cost to sell!', 'errors', 'currency');
            return new Redirect('/currency/' . $symbol);
        }

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
        $balancesOwned = (new TransactionsService())->getUserBalances(Session::get('userId'))->getBalances();
        foreach ($balancesOwned as $balance) {
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

    private function canSellCurrency(string $symbol, float $amount): bool
    {
        $transactionsService = new TransactionsService();
        foreach ($transactionsService->getUserBalances(Session::get('userId'))->getBalances() as $balance) {
            /** @var AccountBalance $balance */
            if ($balance->getSymbol() === $symbol && $balance->getAmount() >= $amount)
                return true;
        }
        return false;
    }
}