<?php

namespace App\Services;

use App\Models\{Error, Price, Transaction, TransactionStatistics};
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

    public function getTransactionStatistics(int $userId): ?TransactionStatistics
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return null;
        }

        $transactions = $this->transactionsRepository::fetchTransactionsById($userId);
        if ($transactions->getCount() === 0) {
            $this->errors->add(
                new Error('No transactions found!', 'nothingFound')
            );
            return null;
        }

        $costsByCurrency = [];
        $amountsByCurrency = [];
        $amountsInWallet = [];

        $totalCost = 0;
        $revenue = 0;
        foreach ($transactions->getAll() as $transaction) {
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

        $currencyValueInWallet = 0;
        $averagePrices = new PriceCollection();
        foreach ($costsByCurrency as $key => $currencyCost) {
            $averagePrice = new Price($key, $currencyCost / $amountsByCurrency [$key]);
            $averagePrices->add($averagePrice);
            $currencyValueInWallet += $amountsInWallet[$key] * $averagePrice->getPrice();
        }
        $profit = $revenue + $currencyValueInWallet - $totalCost;

        return new TransactionStatistics(
            $totalCost,
            $revenue,
            $currencyValueInWallet,
            $profit,
            $averagePrices
        );
    }
}