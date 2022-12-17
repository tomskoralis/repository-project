<?php

namespace App\Validation;

use App\Models\{Transaction, Balance, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\TransactionsRepository;
use App\Repositories\UsersRepository;

class TransactionValidation
{
    private UsersRepository $usersRepository;
    private TransactionsRepository $transactionsRepository;
    private ErrorsCollection $errors;

    public function __construct(
        UsersRepository        $usersRepository,
        TransactionsRepository $transactionsRepository
    )
    {
        $this->usersRepository = $usersRepository;
        $this->transactionsRepository = $transactionsRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function isAmountValid(string $amount): bool
    {
        if ((float)$amount !== filter_var($amount, FILTER_VALIDATE_FLOAT)) {
            $this->errors->add(
                new Error('Amount must be a number!', 'currency')
            );
            return false;
        }

        if (floor((float)$amount * 100000000) / 100000000 <= 0) {
            $this->errors->add(
                new Error('Amount must be higher than 0.00000001!', 'currency')
            );
            return false;
        }

        if ((float)$amount !== floor((float)$amount * 100000000) / 100000000) {
            $this->errors->add(
                new Error('Amount must have 8 decimal digits at most!', 'currency')
            );
            return false;
        }
        return true;
    }

    public function canBuyCurrency(Transaction $transaction, int $userId): bool
    {
        if ($transaction->getAmount() <= 0) {
            $this->errors->add(
                new Error('Incorrect amount!', 'currency')
            );
            return false;
        }
        $cost = floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100;
        if ($cost < 0.01) {
            $this->errors->add(
                new Error('Too small cost to buy!', 'currency')
            );
            return false;
        }
        $user = $this->usersRepository->fetchUser($userId);
        if (isset($user) && $user->getWallet() < $cost) {
            $this->errors->add(
                new Error('Not enough money in wallet!', 'currency')
            );
            return false;
        }
        return true;
    }

    public function canSellCurrency(Transaction $transaction, int $userId): bool
    {
        if ($transaction->getAmount() >= 0) {
            $this->errors->add(
                new Error('Incorrect amount!', 'currency')
            );
            return false;
        }
        if (floor(abs($transaction->getAmount()) * $transaction->getPrice() * 100) / 100 < 0.01) {
            $this->errors->add(
                new Error('Too small cost to sell!', 'currency')
            );
            return false;
        }
        if (!$this->hasUserEnoughBalance(
            $userId,
            new Balance($transaction->getSymbol(), abs($transaction->getAmount()))
        )) {
            $this->errors->add(
                new Error('Not enough ' . $transaction->getSymbol() . ' in the wallet!', 'currency')
            );
            return false;
        }
        return true;
    }

    private function hasUserEnoughBalance(int $userId, Balance $balanceToRemove): bool
    {
        $currentBalance = $this->transactionsRepository->fetchBalancesById(
            $userId,
            $balanceToRemove->getSymbol()
        )->getAll()->current();

        if (
            isset($currentBalance) &&
            $currentBalance->getSymbol() === $balanceToRemove->getSymbol() &&
            $currentBalance->getAmount() >= $balanceToRemove->getAmount()
        ) {
            return true;
        }
        return false;
    }
}