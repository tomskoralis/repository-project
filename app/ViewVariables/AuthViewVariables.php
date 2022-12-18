<?php

namespace App\ViewVariables;

use App\Session;
use App\Repositories\UsersRepository;

class AuthViewVariables implements ViewVariables
{
    private UsersRepository $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
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
        $user = $this->usersRepository::fetchUser(Session::get('userId'));

        return (!$this->usersRepository::getError())
            ? [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'wallet' => $user->getWallet(),
            ]
            : [];
    }
}