<?php

namespace App\Controllers;

use App\{Redirect, Session, Template};
use App\Models\User;
use App\Services\UsersService;
use App\Validation\UserValidation;

class UserRegisterController
{
    private UsersService $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    public function displayRegisterForm(): Template
    {
        $urlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        if (isset($urlPath) && $urlPath !== '/' && $urlPath !== '/register') {
            Session::add($urlPath, 'urlPath');
        }
        return new Template('templates/register.twig');
    }

    public function store(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $passwordRepeated = $_POST['passwordRepeated'] ?? '';

        $user = new User($name, $email, $password, $passwordRepeated);
        $validation = new UserValidation($user, $this->usersService);

        if (!$validation->isUserValid() || Session::has('errors')) {
            return new Redirect('/register');
        }

        $validation->isEmailTaken();
        if (Session::has('errors')) {
            return new Redirect('/register');
        }

        $this->usersService->insertUser($user);
        if (Session::has('errors')) {
            return new Redirect('/register');
        }
        Session::add($this->usersService->searchIdByEmail($user), 'userId');
        return new Redirect(Session::get('urlPath') ?? '/');
    }
}