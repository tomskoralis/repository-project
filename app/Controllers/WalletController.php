<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Services\{UsersService, TransactionsService};
use const App\CURRENCY_CODE;

class WalletController
{
    private UsersService $usersService;
    private TransactionsService $transactionsService;

    public function __construct(
        UsersService        $usersService,
        TransactionsService $transactionsService
    )
    {
        $this->usersService = $usersService;
        $this->transactionsService = $transactionsService;
    }

    public function displayWallet()
    {
        if (!Session::has('userId')) {
            return new Redirect('/login');
        }
        $transactionsService = $this->transactionsService;
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
            $this->usersService->addMoneyToWallet(Session::get('userId'), $amountToAdd);
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