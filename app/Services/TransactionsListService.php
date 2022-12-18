<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Error;
use App\Repositories\UsersRepository;
use App\Models\Collections\{ErrorsCollection, TransactionsCollection};
use App\Repositories\TransactionsRepository;

class TransactionsListService
{
    private TransactionsRepository $transactionsRepository;
    private UsersRepository $usersRepository;
    private ErrorsCollection $errors;

    public function __construct(
        TransactionsRepository $transactionsRepository,
        UsersRepository        $usersRepository
    )
    {
        $this->transactionsRepository = $transactionsRepository;
        $this->usersRepository = $usersRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function getTransactions(int $userId): TransactionsCollection
    {
        $transactions = new TransactionsCollection();
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return $transactions;
        }

        $transactions = $this->transactionsRepository::fetchTransactions($userId);

        if ($transactions->getCount() === 0) {
            $this->errors->add(
                new Error('No transactions found!', 'nothingFound')
            );
        }

        $error = $this->usersRepository::getError();
        if ($error !== null) {
            return $transactions;
        }

        foreach ($transactions->getAll() as $transaction) {
            /** @var Transaction $transaction */
            if ($transaction->getSenderId() !== null) {
                $transaction->setSenderName(
                    $this->usersRepository::fetchUser($transaction->getSenderId())->getName()
                );
            }
        }

        return $transactions;
    }
}