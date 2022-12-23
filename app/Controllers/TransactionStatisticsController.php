<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\TransactionStatisticsService;
use const App\CURRENCY_CODE;

class TransactionStatisticsController
{
    private TransactionStatisticsService $statisticsService;

    public function __construct(TransactionStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function showStatistics()
    {
        if (!Session::has('userId')) {
            Session::add(
                'Need to be logged in to view statistics!',
                'errors', 'auth'
            );
            return new Redirect('/login');
        }

        $statistics = $this->statisticsService->getTransactionStatistics(Session::get('userId'));
        Session::addErrors($this->statisticsService->getErrors());

        return new Template ('templates/statistics.twig', [
            'statistics' => $statistics,
            'currencyCode' => CURRENCY_CODE,
        ]);
    }
}