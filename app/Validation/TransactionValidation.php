<?php

namespace App\Validation;

use App\Models\{Transaction, Balance, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\{UsersRepository, TransactionsRepository};
use const App\CURRENCY_CODE;

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

        if (round((float)$amount, 8) !== (float)$amount) {
            $this->errors->add(
                new Error('Amount can have 8 decimal digits at most!', 'currency')
            );
            return false;
        }
        return true;
    }

    public function canBuyCurrency(Transaction $transaction): bool
    {
        if ($transaction->getAmount() * $transaction->getPrice() < 0.01) {
            $this->errors->add(
                new Error(
                    'Total cost cannot be below ' . $this->getCurrencySymbol() . '0.01!',
                    'currency'
                )
            );
            return false;
        }

        if (
            $this->usersRepository::fetchUser($transaction->getUserId())->getWallet() <
            $transaction->getAmount() * $transaction->getPrice()
        ) {
            $this->errors->add(
                new Error('Not enough money in wallet!', 'currency')
            );
            return false;
        }
        return true;
    }

    public function canSellCurrency(Transaction $transaction, float $minMargin): bool
    {
        if (abs($transaction->getAmount()) * $transaction->getPrice() < 0.01) {
            $this->errors->add(
                new Error(
                    'Total cost cannot be below ' . $this->getCurrencySymbol() . '0.01!',
                    'currency'
                )
            );
            return false;
        }

        $currentBalance = $this->transactionsRepository::fetchBalances(
            $transaction->getUserId(),
            $transaction->getSymbol()
        )->getAll()->current();

        $walletValue = 0;
        foreach ($this->transactionsRepository::fetchBalances($transaction->getUserId())->getAll() as $balance) {
            /** @var Balance $balance */
            $walletValue += $balance->getValue();
        }

        if (
            (isset($currentBalance) ? $currentBalance->getAmount() : 0) < abs($transaction->getAmount()) &&
            $this->usersRepository::fetchUser($transaction->getUserId())->getWallet() + $walletValue < $minMargin
        ) {
            $this->errors->add(
                new Error(
                    'Insufficient wallet value for short selling! Maintenance margin is ' .
                    $this->getCurrencySymbol() . number_format($minMargin, 2, '.', ''),
                    'currency'
                )
            );
            return false;
        }
        return true;
    }

    public function hasUserEnoughBalance(Balance $balanceToRemove): bool
    {
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

    private function getCurrencySymbol(): string
    {
        return (new \NumberFormatter(\Locale::getDefault() . '@currency=' . CURRENCY_CODE, \NumberFormatter::CURRENCY))
            ->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }
}