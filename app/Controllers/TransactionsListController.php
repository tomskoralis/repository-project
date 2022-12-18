<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\TransactionsListService;
use const App\CURRENCY_CODE;

class TransactionsListController
{
    private TransactionsListService $transactionsListService;

    public function __construct(TransactionsListService $transactionsListService)
    {
        $this->transactionsListService = $transactionsListService;
    }

    public function showTransactionsList()
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to view transactions',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $transactions = $this->transactionsListService->getTransactions(Session::get('userId'))->getAll();
        Session::addErrors($this->transactionsListService->getErrors());

        return new Template ('templates/transactions.twig', [
            'transactions' => $transactions,
            'currencyCode' => CURRENCY_CODE,
        ]);
    }
}