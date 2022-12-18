<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Services\UserProfileService;
use App\Services\CurrencyGiftService;

class UserProfileController
{
    private UserProfileService $userShowService;
    private CurrencyGiftService $currencyGiftService;

    public function __construct(
        UserProfileService  $userShowService,
        CurrencyGiftService $currencyGiftService
    )
    {
        $this->userShowService = $userShowService;
        $this->currencyGiftService = $currencyGiftService;
    }

    public function showUser(array $userId): Template
    {
        $user = $this->userShowService->getUser($userId['userId']);
        Session::addErrors($this->userShowService->getErrors());

        return new Template ('templates/profile.twig', [
            'user' => $user
        ]);
    }

    public function giftCurrency(array $userId): Redirect
    {
        $symbol = strtoupper($_POST['symbol']) ?? '';
        $amount = $_POST['amountToGift'] ?? '0';
        $recipientId = (int)$userId['userId'];

        $userName = $this->currencyGiftService->giftCurrencyToUserAndGetName(
            Session::get('userId'),
            $recipientId,
            $symbol,
            $amount,
            $_POST['password'] ?? ''
        );

        Session::addErrors($this->currencyGiftService->getErrors());
        if (Session::has('errors')) {
            return new Redirect('/profile/' . $recipientId);
        }

        Session::add(
            'Successfully gifted ' .
            rtrim(rtrim(number_format($amount, 8, '.', ''), '0'), '.') .
            ' ' . $symbol . ' to ' . $userName,
            'flashMessages', 'gift'
        );
        return new Redirect('/profile/' . $recipientId);
    }
}