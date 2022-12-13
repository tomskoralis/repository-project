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
        $urlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        if (isset($urlPath) && $urlPath !== '/' && $urlPath !== '/login' && $urlPath !== '/register') {
            Session::add($urlPath, 'urlPath');
        }
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
        return new Redirect(Session::get('urlPath') ?? '/');
    }

    public function logout(): Redirect
    {
        Session::remove('userId');
        $urlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        if ($urlPath === '/account' || $urlPath === '/wallet' || $urlPath === '/transactions') {
            return new Redirect('/');
        }
        return new Redirect($urlPath ?? '/');
    }
}