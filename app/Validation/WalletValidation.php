<?php

namespace App\Validation;

use App\Models\Error;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;

class WalletValidation
{
    private UsersRepository $usersRepository;
    private ErrorsCollection $errors;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
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
                new Error('Amount must be a number!', 'wallet')
            );
            return false;
        }

        if (floor((float)$amount * 100) / 100 <= 0) {
            $this->errors->add(
                new Error('Amount must be higher than 0.01!', 'wallet')
            );
            return false;
        }

        if ((float)$amount !== floor((float)$amount * 100) / 100) {
            $this->errors->add(
                new Error('Amount must have 2 decimal digits at most!', 'wallet')
            );
            return false;
        }
        return true;
    }

    public function canWithdraw(string $amount, int $userId): bool
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return false;
        }
        $user = $this->usersRepository::fetchUser($userId);
        if (
            isset($user) &&
            $user->getWallet() < floor((float)$amount * 100) / 100
        ) {
            $this->errors->add(
                new Error('Not enough money in the wallet!', 'wallet')
            );
            return false;
        }
        return true;
    }
}