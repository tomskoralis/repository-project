<?php

namespace App\Controllers;

use App\{Redirect, Template, Validation};
use App\Services\UsersService;
use App\Models\User;

class UserRegisterController
{
    public function displayRegisterForm(): Template
    {
        return new Template("templates/register.twig");
    }

    public function store(): Redirect
    {
        $password = $_POST["password"] ?? "";
        $email = $_POST["email"] ?? "";
        $name = $_POST["name"] ?? "";
        $passwordRepeated = $_POST["passwordRepeated"] ?? "";

        $user = new User($name, $email, $password, $passwordRepeated);
        $validation = new Validation($user);

        $validation->isUserValid();
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/register");
        }

        $validation->isEmailTaken();
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/register");
        }

        $usersService = new UsersService();
        $usersService->insertUser($user);
        if (isset($_SESSION["errors"])) {
            return new Redirect("/register");
        }

        $_SESSION["userId"] = $usersService->searchIdByEmail($user);
        return new Redirect("/");
    }
}