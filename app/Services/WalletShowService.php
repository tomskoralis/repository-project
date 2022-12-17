<?php

namespace App\Services;

use App\Models\Collections\{BalancesCollection, ErrorsCollection};
use App\Repositories\TransactionsRepository;

class WalletShowService
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

    public function getUserBalances(int $userId): BalancesCollection
    {
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return new BalancesCollection();
        }

        return $this->transactionsRepository::fetchBalancesById($userId);
    }
}