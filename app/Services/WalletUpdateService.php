<?php

namespace App\Services;

use App\Container;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;
use App\Validation\WalletValidation;

class WalletUpdateService
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

    public function addMoneyToWallet(int $userId, string $amount): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }

        $validation = Container::get(WalletValidation::class);
        /** @var WalletValidation $validation */
        if (!$validation->isAmountValid($amount)) {
            $this->errors = $validation->getErrors();
            return;
        }

        $amount = floor($amount * 100) / 100;
        $this->usersRepository::addMoney($userId, $amount);
    }

    public function subtractMoneyFromWallet(int $userId, string $amount): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }

        $validation = Container::get(WalletValidation::class);
        /** @var WalletValidation $validation */
        if (
            !$validation->isAmountValid($amount) ||
            !$validation->canWithdraw($amount, $userId)
        ) {
            $this->errors = $validation->getErrors();
            return;
        }

        $amount = floor((float)$amount * 100) / 100;
        $this->usersRepository::addMoney($userId, -1 * $amount);
    }
}