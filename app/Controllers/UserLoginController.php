<?php

namespace App\Controllers;

use App\{Redirect, Session, Template, UserValidation};
use App\Models\User;
use App\Services\UsersService;

class UserLoginController
{
    public function displayLoginForm(): Template
    {
        return new Template('templates/login.twig');
    }

    public function login(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';

        $user = new User(null, $email, $password);
        $validation = new UserValidation($user);

        $validation->isUserValid();
        if (Session::has('errors')) {
            return new Redirect('/login');
        }

        $userId = (new UsersService())->searchIdByEmail($user);
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