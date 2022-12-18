<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Services\{WalletShowService, WalletUpdateService};
use const App\CURRENCY_CODE;

class WalletController
{
    private WalletShowService $walletShowService;
    private WalletUpdateService $walletUpdateService;

    public function __construct(
        WalletShowService   $walletShowService,
        WalletUpdateService $walletUpdateService
    )
    {
        $this->walletShowService = $walletShowService;
        $this->walletUpdateService = $walletUpdateService;
    }

    public function showWallet()
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to view wallet',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $balances = $this->walletShowService->getUserBalances(Session::get('userId'))->getAll();
        Session::addErrors($this->walletShowService->getErrors());

        return new Template ('templates/wallet.twig', [
            'currencyCode' => CURRENCY_CODE,
            'balances' => $balances,
        ]);
    }

    public function depositMoney(): Redirect
    {
        $amount = $_POST['amount'] ?? '0';

        $this->walletUpdateService->addMoneyToWallet(Session::get('userId'), $amount);
        Session::addErrors($this->walletUpdateService->getErrors());

        if (!Session::has('errors')) {
            Session::add(
                'Successfully deposited ' . $this->getCurrencySymbol() .
                number_format($amount, 2, '.', ''),
                'flashMessages', 'wallet'
            );
        }
        return new Redirect('/wallet');
    }

    public function withdrawMoney(): Redirect
    {
        $amount = $_POST['amount'] ?? '0';

        $this->walletUpdateService->subtractMoneyFromWallet(Session::get('userId'), $amount);
        Session::addErrors($this->walletUpdateService->getErrors());

        if (!Session::has('errors')) {
            Session::add(
                'Successfully withdrew ' . $this->getCurrencySymbol() .
                number_format($amount, 2, '.', ''),
                'flashMessages', 'wallet'
            );
        }
        return new Redirect('/wallet');
    }

    private function getCurrencySymbol(): string
    {
        return (new \NumberFormatter(
            \Locale::getDefault() . '@currency=' . CURRENCY_CODE,
            \NumberFormatter::CURRENCY
        ))->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }
}