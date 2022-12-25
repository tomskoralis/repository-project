<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\UserLoginService;

class UserLoginController
{
    private UserLoginService $userLoginService;

    public function __construct(UserLoginService $userLoginService)
    {
        $this->userLoginService = $userLoginService;
    }

    public function showLoginForm()
    {
        if (Session::has('userId')) {
            return new Redirect('/');
        }

        $urlPath = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH);
        if ($urlPath && substr($urlPath, -1) === '/') {
            $urlPath = substr($urlPath, 0, -1);
        }
        if (
            $urlPath !== '' &&
            $urlPath !== '/login' &&
            $urlPath !== '/register'
        ) {
            Session::add($urlPath, 'redirect', 'url');
        }
        return new Template('templates/login.twig');
    }

    public function login(): Redirect
    {
        $userId = $this->userLoginService->loginAndGetId(
            $_POST['email'] ?? '',
            $_POST['password'] ?? ''
        );
        Session::addErrors($this->userLoginService->getErrors());
        if (Session::has('errors') || $userId === 0) {
            return new Redirect('/login');
        }

        Session::add($userId, 'userId');
        Session::add('true', 'redirect', 'success');
        return new Redirect(Session::get('redirect', 'url') ?: '/');
    }
}