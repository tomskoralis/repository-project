<?php

namespace App\Controllers;

use App\{Redirect, Services\UsersService, Session, Template};
use App\Services\TransactionsService;
use const App\CURRENCY_CODE;

class WalletController
{
    public function displayWallet()
    {
        if (!Session::has('userId')) {
            return new Redirect('/login');
        }
        $transactionsService = new TransactionsService();
        $balances = $transactionsService->getUserBalances(Session::get('userId'));
        return new Template ('templates/wallet.twig', [
            'currencyCode' => CURRENCY_CODE,
            'balances' => $balances->getBalances(),
        ]);
    }

    public function addMoney(): Redirect
    {
        $amountToAdd = floor((float)$_POST['amount'] * 100) / 100;
        if ($amountToAdd > 0) {
            (new UsersService())->addMoneyToWallet(Session::get('userId'), $amountToAdd);
        } else {
            Session::add('Incorrect amount!', 'errors', 'wallet');
        }
        if (!Session::has('errors')) {
            $symbol = (new \NumberFormatter(\Locale::getDefault() . "@currency=" . CURRENCY_CODE, \NumberFormatter::CURRENCY))
                ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
            $amountToAdd = number_format($amountToAdd, 2, '.', '');
            Session::add('Successfully added ' . $symbol . "$amountToAdd to the wallet!", 'flashMessages', 'wallet');
        }
        return new Redirect('/wallet');
    }
}