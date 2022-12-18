<?php

namespace App\Services;

use App\Container;
use App\Models\{Transaction, User, Balance, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\{TransactionsRepository, UsersRepository};
use App\Validation\{TransactionValidation, UserValidation};

class CurrencyGiftService
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

    public function giftCurrencyToUserAndGetName(
        int    $senderId,
        int    $recipientId,
        string $symbol,
        string $amount,
        string $password
    ): string
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return '';
        }
        $error = $this->transactionsRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return '';
        }

        $transactions = $this->transactionsRepository::fetchTransactions($senderId, $symbol);
        if ($transactions->getCount() === 0) {
            $this->errors->add(
                new Error(
                    'No ' . $symbol . ' found in the wallet!',
                    'currency'
                )
            );
            return '';
        }

        $cost = 0;
        $amountInWallet = 0;
        foreach ($transactions->getAll() as $transaction) {
            /** @var Transaction $transaction */
            $cost += $transaction->getPrice() * abs($transaction->getAmount());
            $amountInWallet += abs($transaction->getAmount());
        }
        $averagePrice = $cost / $amountInWallet;
        $amountRounded = floor((float)$amount * 100000000) / 100000000;

        $senderTransaction = new Transaction(
            $senderId,
            $symbol,
            $averagePrice,
            -1 * $amountRounded,
        );

        $recipientTransaction = new Transaction(
            $recipientId,
            $symbol,
            $averagePrice,
            $amountRounded,
            $senderId
        );

        $transactionValidation = Container::get(TransactionValidation::class);
        /** @var TransactionValidation $transactionValidation */
        if (
            !$transactionValidation->isAmountValid($amount) ||
            !$transactionValidation->hasUserEnoughBalance(
                new Balance(
                    $senderId,
                    $symbol,
                    $amountRounded
                )
            )
        ) {
            $this->errors = $transactionValidation->getErrors();
            return '';
        }

        $userValidation = Container::get(UserValidation::class);
        /** @var UserValidation $userValidation */
        if (!$userValidation->isPasswordCorrect(
            new User(
                $senderId,
                null,
                null,
                $password
            )
        )) {
            $this->errors = $userValidation->getErrors();
            return '';
        }

        $this->transactionsRepository::add($senderTransaction);
        $this->transactionsRepository::add($recipientTransaction);
        return $this->usersRepository::fetchUser($recipientId)->getName();
    }
}