<?php

namespace App\Controllers;

use App\{Models\Transaction, Redirect, Session, Template};
use App\Services\TransactionsService;
use const App\CURRENCY_CODE;

class TransactionsController
{
    private TransactionsService $transactionsService;

    public function __construct(TransactionsService $transactionsService)
    {
        $this->transactionsService = $transactionsService;
    }

    public function displayTransactionsList()
    {
        if (!Session::has('userId')) {
            return new Redirect('/login');
        }

        $transactionsService = $this->transactionsService;
        $transactions = iterator_to_array(
            $transactionsService->getUserTransactions(Session::get('userId'))->getTransactions()
        );

        if (!empty($transactions)) {
            $costsByCurrency = [];
            $amountsByCurrency = [];
            $amountsInWallet = [];

            $totalCost = 0;
            $revenue = 0;
            foreach ($transactions as $transaction) {
                /** @var Transaction $transaction */
                if ($transaction->getAmount() > 0) {
                    $totalCost += $transaction->getAmount() * $transaction->getPrice();
                } else {
                    $revenue -= $transaction->getAmount() * $transaction->getPrice();
                }
                $costsByCurrency [$transaction->getSymbol()] += $transaction->getPrice() * abs($transaction->getAmount());
                $amountsByCurrency [$transaction->getSymbol()] += abs($transaction->getAmount());
                $amountsInWallet [$transaction->getSymbol()] += $transaction->getAmount();
            }

            $averageCurrencyCosts = [];
            $currencyValueInWallet = 0;
            foreach ($costsByCurrency as $key => $currencyCost) {
                $averageCurrencyCosts [$key] = $currencyCost / $amountsByCurrency [$key];
                $amountsInWallet[$key] = floor($amountsInWallet[$key] * 100000000) / 100000000;
                $currencyValueInWallet += $amountsInWallet[$key] * $averageCurrencyCosts [$key];
            }

            $profit = $revenue + $currencyValueInWallet - $totalCost;
        }

        return new Template ('templates/transactions.twig', [
            'currencyCode' => CURRENCY_CODE,
            'transactions' => $transactions,
            'totalCost' => $totalCost ?? 0,
            'revenue' => $revenue ?? 0,
            'currencyValueInWallet' => $currencyValueInWallet ?? 0,
            'profit' => $profit ?? 0,
            'averageCurrencyCosts' => $averageCurrencyCosts ?? [],
        ]);
    }
}