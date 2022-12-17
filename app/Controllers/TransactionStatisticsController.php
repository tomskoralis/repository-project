<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\TransactionStatisticsService;
use const App\CURRENCY_CODE;

class TransactionStatisticsController
{
    private TransactionStatisticsService $transactionStatisticsService;

    public function __construct(TransactionStatisticsService $transactionStatisticsService)
    {
        $this->transactionStatisticsService = $transactionStatisticsService;
    }

    public function showStatistics()
    {
        if (!Session::has('userId')) {
            Session::add(
                "Need to be logged in to view statistics",
                'flashMessages', 'login'
            );
            return new Redirect('/login');
        }

        $statistics = $this->transactionStatisticsService->getTransactionStatistics(Session::get('userId'));
        Session::addErrors($this->transactionStatisticsService->getErrors());

        return new Template ('templates/statistics.twig', [
            'statistics' => $statistics,
            'currencyCode' => CURRENCY_CODE,
        ]);
    }
}