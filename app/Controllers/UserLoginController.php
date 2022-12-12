<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Models\User;
use App\Services\UsersService;
use App\Validation\UserValidation;

class UserLoginController
{
    private UsersService $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    public function displayLoginForm(): Template
    {
        return new Template('templates/login.twig');
    }

    public function login(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';

        $user = new User(null, $email, $password);
        $validation = new UserValidation($user, $this->usersService);

        if (!$validation->isUserValid() || Session::has('errors')) {
            return new Redirect('/login');
        }

        $userId = $this->usersService->searchIdByEmail($user);
        if (!$validation->isPasswordMatchingHash($userId)) {
            return new Redirect('/login');
        }
        Session::add($userId, 'userId');
        return new Redirect('/');
    }

    public function logout(): Redirect
    {
        Session::remove('userId');
        return new Redirect('/');
    }
}