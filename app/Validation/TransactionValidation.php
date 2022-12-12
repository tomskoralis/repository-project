<?php

namespace App\Validation;

use App\Session;
use App\Models\{AccountBalance, Transaction};
use App\Services\TransactionsService;

class TransactionValidation
{
    private Transaction $transaction;
    private TransactionsService $transactionsService;

    public function __construct(Transaction $transaction, TransactionsService $transactionsService)
    {
        $this->transaction = $transaction;
        $this->transactionsService = $transactionsService;
    }

    public function canBuyCurrency(): bool
    {
        if ($this->transaction->getAmount() <= 0) {
            Session::add('Incorrect amount!', 'errors', 'currency');
            return false;
        }
        $cost = floor($this->transaction->getAmount() * $this->transaction->getPrice() * 100) / 100;
        if ($cost < 0.01) {
            Session::add('Too small cost to buy!', 'errors', 'currency');
            return false;
        }
        $moneyAvailable = $this->transactionsService->getUser(Session::get('userId'))->getWallet();
        if ($moneyAvailable < $cost) {
            Session::add('Not enough money in wallet!', 'errors', 'currency');
            return false;
        }
        return true;
    }

    public function canSellCurrency(): bool
    {
        if ($this->transaction->getAmount() >= 0) {
            Session::add('Incorrect amount!', 'errors', 'currency');
            return false;
        }
        if (floor(abs($this->transaction->getAmount()) * $this->transaction->getPrice() * 100) / 100 < 0.01) {
            Session::add('Too small cost to sell!', 'errors', 'currency');
            return false;
        }
        if (!$this->userHasEnoughCurrency($this->transaction->getSymbol(), abs($this->transaction->getAmount()))) {
            Session::add('You do not own enough to sell that much!', 'errors', 'currency');
            return false;
        }
        return true;
    }

    private function userHasEnoughCurrency(string $symbol, float $amount): bool
    {
        foreach ($this->transactionsService->getUserBalances(Session::get('userId'))->getBalances() as $balance) {
            /** @var AccountBalance $balance */
            if ($balance->getSymbol() === $symbol && $balance->getAmount() >= $amount)
                return true;
        }
        return false;
    }
}