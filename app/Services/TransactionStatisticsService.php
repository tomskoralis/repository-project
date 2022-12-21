<?php

namespace App\Services;

use App\Models\{TransactionStatistics, Transaction, CurrencyStatistic, Error};
use App\Models\Collections\{CurrencyStatisticsCollection, ErrorsCollection};
use App\Repositories\TransactionsRepository;

class TransactionStatisticsService
{
    private TransactionsRepository $transactionsRepository;
    private ErrorsCollection $errors;

    public function __construct(TransactionsRepository $transactionsRepository)
    {
        $this->transactionsRepository = $transactionsRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function getTransactionStatistics(int $userId): ?TransactionStatistics
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return null;
        }

        $transactions = $this->transactionsRepository::fetchTransactions($userId);
        if ($transactions->getCount() === 0) {
            $this->errors->add(
                new Error('No transactions found!', 'nothingFound')
            );
            return null;
        }

        $spentByCurrency = [];
        $amountSpentByCurrency = [];
        $earnedByCurrency = [];
        $amountEarnedByCurrency = [];
        $amountsInWallet = [];
        $walletValues = [];

        foreach ($transactions->getAll() as $transaction) {
            /** @var Transaction $transaction */
            if (!isset($spentByCurrency[$transaction->getSymbol()])) {
                $spentByCurrency[$transaction->getSymbol()] = 0;
            }
            if (!isset($amountSpentByCurrency[$transaction->getSymbol()])) {
                $amountSpentByCurrency[$transaction->getSymbol()] = 0;
            }
            if (!isset($earnedByCurrency[$transaction->getSymbol()])) {
                $earnedByCurrency[$transaction->getSymbol()] = 0;
            }
            if (!isset($amountEarnedByCurrency[$transaction->getSymbol()])) {
                $amountEarnedByCurrency[$transaction->getSymbol()] = 0;
            }

            if ($transaction->getAmount() > 0) {
                $spentByCurrency[$transaction->getSymbol()] += $transaction->getPrice() * $transaction->getAmount();
                $amountSpentByCurrency[$transaction->getSymbol()] += $transaction->getAmount();
            } else {
                $earnedByCurrency[$transaction->getSymbol()] += $transaction->getPrice() * abs($transaction->getAmount());
                $amountEarnedByCurrency[$transaction->getSymbol()] += abs($transaction->getAmount());
            }

            if (!isset($amountsInWallet[$transaction->getSymbol()])) {
                $amountsInWallet[$transaction->getSymbol()] = 0;
            }
            $amountsInWallet[$transaction->getSymbol()] += $transaction->getAmount();

            if (!isset($walletValues[$transaction->getSymbol()])) {
                $walletValues[$transaction->getSymbol()] = 0;
            }
        }

        $currencyStatistics = new CurrencyStatisticsCollection();
        foreach ($spentByCurrency as $key => $spent) {
            $averagePrice = ($amountEarnedByCurrency[$key] + $amountSpentByCurrency[$key] !== 0)
                ? ($spent + $earnedByCurrency[$key]) / ($amountEarnedByCurrency[$key] + $amountSpentByCurrency[$key])
                : 0;
            $profit = $earnedByCurrency[$key] + $amountsInWallet[$key] * $averagePrice - $spent;
            $walletValues[$key] = $amountsInWallet[$key] * $averagePrice;

            $currencyStatistics->add(
                new CurrencyStatistic(
                    $key,
                    $amountEarnedByCurrency[$key] + $amountSpentByCurrency[$key],
                    $averagePrice,
                    $earnedByCurrency[$key],
                    $spent,
                    $walletValues[$key],
                    $profit
                )
            );
        }

        $amountTotal = array_sum($amountEarnedByCurrency) + array_sum($amountSpentByCurrency);
        $earnedTotal = array_sum($earnedByCurrency);
        $spentTotal = array_sum($spentByCurrency);
        $walletTotal = array_sum($walletValues);

        return new TransactionStatistics(
            $amountTotal,
            ($earnedTotal + $spentTotal) / $amountTotal,
            $earnedTotal,
            $spentTotal,
            $walletTotal,
            $earnedTotal + $walletTotal - $spentTotal,
            $currencyStatistics
        );
    }
}