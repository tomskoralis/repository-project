<?php

namespace App\Services;

use App\Models\{Error, CurrencyPrice, Transaction, TransactionStatistics};
use App\Models\Collections\{PriceCollection, ErrorsCollection};
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

    public function getTransactionStatistics(int $userId): TransactionStatistics
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return new TransactionStatistics();
        }

        $transactions = $this->transactionsRepository::fetchTransactions($userId);
        if ($transactions->getCount() === 0) {
            $this->errors->add(
                new Error('No transactions found!', 'nothingFound')
            );
            return new TransactionStatistics();
        }

        $costsByCurrency = [];
        $amountsByCurrency = [];
        $amountsInWallet = [];
        $totalSpent = 0;
        $totalEarned = 0;
        foreach ($transactions->getAll() as $transaction) {
            /** @var Transaction $transaction */
            if ($transaction->getAmount() > 0) {
                $totalSpent += $transaction->getAmount() * $transaction->getPrice();
            } else {
                $totalEarned -= $transaction->getAmount() * $transaction->getPrice();
            }

            if (!array_key_exists($transaction->getSymbol(), $costsByCurrency)) {
                $costsByCurrency [$transaction->getSymbol()] = 0;
            }
            $costsByCurrency [$transaction->getSymbol()] += $transaction->getPrice() * abs($transaction->getAmount());

            if (!array_key_exists($transaction->getSymbol(), $amountsByCurrency)) {
                $amountsByCurrency [$transaction->getSymbol()] = 0;
            }
            $amountsByCurrency [$transaction->getSymbol()] += abs($transaction->getAmount());

            if (!array_key_exists($transaction->getSymbol(), $amountsInWallet)) {
                $amountsInWallet [$transaction->getSymbol()] = 0;
            }
            $amountsInWallet [$transaction->getSymbol()] += $transaction->getAmount();
        }

        $walletValue = 0;
        $averagePrices = new PriceCollection();
        foreach ($costsByCurrency as $key => $currencyCost) {
            $averagePrice = new CurrencyPrice(
                $key,
                $currencyCost / $amountsByCurrency [$key]
            );
            $averagePrices->add($averagePrice);
            $walletValue += $amountsInWallet[$key] * $averagePrice->getPrice();
        }
        $totalProfit = $totalEarned + $walletValue - $totalSpent;

        return new TransactionStatistics(
            $totalEarned,
            $walletValue,
            $totalSpent,
            $totalProfit,
            $averagePrices
        );
    }
}