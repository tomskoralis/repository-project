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

    public function canBuyCurrency(Transaction $transaction): bool
    {
        $cost = floor($transaction->getAmount() * $transaction->getPrice() * 100) / 100;
        if ($cost < 0.01) {
            $this->errors->add(
                new Error('Too small cost to buy!', 'currency')
            );
            return false;
        }

        $user = $this->usersRepository::fetchUser($transaction->getUserId());
        if (isset($user) && $user->getWallet() < $cost) {
            $this->errors->add(
                new Error('Not enough money in wallet!', 'currency')
            );
            return false;
        }
        return true;
    }

    public function canSellCurrency(Transaction $transaction): bool
    {
        if (floor(abs($transaction->getAmount()) * $transaction->getPrice() * 100) / 100 < 0.01) {
            $this->errors->add(
                new Error('Too small cost to sell!', 'currency')
            );
            return false;
        }

        if (!$this->hasUserEnoughBalance(
            new Balance(
                $transaction->getUserId(),
                $transaction->getSymbol(),
                abs($transaction->getAmount())
            )
        )) {
            return false;
        }
        return true;
    }

    public function hasUserEnoughBalance(Balance $balanceToRemove): bool
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return false;
        }

        $currentBalance = $this->transactionsRepository::fetchBalances(
            $balanceToRemove->getId(),
            $balanceToRemove->getSymbol()
        )->getAll()->current();

        if (
            isset($currentBalance) &&
            $currentBalance->getSymbol() === $balanceToRemove->getSymbol() &&
            $currentBalance->getAmount() >= $balanceToRemove->getAmount()
        ) {
            return true;
        }

        $this->errors->add(
            new Error('Not enough ' . $balanceToRemove->getSymbol() . ' in the wallet!', 'currency')
        );
        return false;
    }
}