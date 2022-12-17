<?php

namespace App\Services;

use App\Models\Error;
use App\Models\Collections\{ErrorsCollection, TransactionsCollection};
use App\Repositories\TransactionsRepository;

class TransactionsListService
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

    public function getTransactions(int $userId): TransactionsCollection
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return new TransactionsCollection();
        }

        $transactions = $this->transactionsRepository::fetchTransactionsById($userId);

        if ($transactions->getCount() === 0) {
            $this->errors->add(
                new Error('No transactions found!', 'nothingFound')
            );
            return new TransactionsCollection();
        }
        return $transactions;
    }
}