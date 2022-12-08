<?php

namespace App\ViewVariables;

use App\Services\UsersService;

class AuthViewVariables implements ViewVariables
{
    public function getName(): string
    {
        return "auth";
    }

    public function getValue(): array
    {
        if (!isset($_SESSION["userId"])) {
            return [];
        }
        $user = (new UsersService())->getUser($_SESSION["userId"]);
        return [
            "name" => $user->getName(),
            "email" => $user->getEmail(),
        ];
    }
}