<?php

namespace App\Controllers;

use App\{Redirect, Template, Validation};
use App\Services\UsersService;
use App\Models\User;

class UserLoginController
{
    public function displayLoginForm(): Template
    {
        return new Template("templates/login.twig");
    }

    public function login(): Redirect
    {
        $password = $_POST["password"] ?? "";
        $email = $_POST["email"] ?? "";

        $user = new User(null, $email, $password);
        $validation = new Validation($user);

        $validation->isUserValid();
        if (!empty($_SESSION["errors"])) {
            return new Redirect("/login");
        }

        $userId = (new UsersService())->searchIdByEmail($user);
        if (!$validation->isPasswordMatchingHash($userId)) {
            return new Redirect("/login");
        }

        $_SESSION["userId"] = $userId;
        return new Redirect("/");
    }

    public function logout(): Redirect
    {
        unset($_SESSION["userId"]);
        return new Redirect("/");
    }
}