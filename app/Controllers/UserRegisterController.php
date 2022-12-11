<?php

namespace App\Controllers;

use App\{Redirect, Session, Template, UserValidation};
use App\Models\User;
use App\Services\UsersService;

class UserRegisterController
{
    public function displayRegisterForm(): Template
    {
        return new Template('templates/register.twig');
    }

    public function store(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $passwordRepeated = $_POST['passwordRepeated'] ?? '';

        $user = new User($name, $email, $password, $passwordRepeated);
        $validation = new UserValidation($user);

        $validation->isUserValid();
        if (Session::has('errors')) {
            return new Redirect('/register');
        }

        $validation->isEmailTaken();
        if (Session::has('errors')) {
            return new Redirect('/register');
        }

        $usersService = new UsersService();
        $usersService->insertUser($user);
        if (Session::has('errors')) {
            return new Redirect('/register');
        }
        Session::add($usersService->searchIdByEmail($user), 'userId');
        return new Redirect('/');
    }
}