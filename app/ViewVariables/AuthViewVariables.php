<?php

namespace App\ViewVariables;

use App\Session;
use App\Services\UsersService;

class AuthViewVariables implements ViewVariables
{
    private UsersService $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    public function getName(): string
    {
        return 'auth';
    }

    public function getValue(): array
    {
        if (!Session::has('userId')) {
            return [];
        }
        $user = $this->usersService->getUser(Session::get('userId'));
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'wallet' => $user->getWallet(),
        ];
    }
}