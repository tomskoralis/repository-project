<?php

namespace App\ViewVariables;

use App\Services\UsersService;
use App\Session;

class AuthViewVariables implements ViewVariables
{
    public function getName(): string
    {
        return 'auth';
    }

    public function getValue(): array
    {
        if (!Session::has('userId')) {
            return [];
        }
        $user = (new UsersService())->getUser(Session::get('userId'));
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'wallet' => $user->getWallet(),
        ];
    }
}