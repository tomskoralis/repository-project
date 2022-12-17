<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Models\User;
use App\Services\UserLoginService;

class UserLoginController
{
    private UserLoginService $userLoginService;

    public function __construct(UserLoginService $userLoginService)
    {
        $this->userLoginService = $userLoginService;
    }

    public function showLoginForm(): Template
    {
        $urlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        if (
            isset($urlPath) &&
            $urlPath !== '' &&
            $urlPath !== '/' &&
            $urlPath !== '/login' &&
            $urlPath !== '/register'
        ) {
            Session::add($urlPath, 'redirectUrl');
        }
        return new Template('templates/login.twig');
    }

    public function login(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $user = new User($password, $email);

        $userId = $this->userLoginService->loginAndGetId($user);
        Session::addErrors($this->userLoginService->getErrors());
        if (Session::has('errors') || $userId === 0) {
            return new Redirect('/login');
        }

        Session::add($userId, 'userId');
        return new Redirect(Session::get('redirectUrl') ?? '/');
    }
}