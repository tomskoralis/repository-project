<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Services\UserRegisterService;

class UserRegisterController
{
    private UserRegisterService $userRegisterService;

    public function __construct(UserRegisterService $userRegisterService)
    {
        $this->userRegisterService = $userRegisterService;
    }

    public function showRegisterForm()
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
        return new Template('templates/register.twig');
    }

    public function register(): Redirect
    {
        $userId = $this->userRegisterService->registerAndGetId(
            $_POST['name'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $_POST['passwordRepeated'] ?? ''
        );
        Session::addErrors($this->userRegisterService->getErrors());
        if (Session::has('errors') || $userId === 0) {
            return new Redirect('/register');
        }

        Session::add($userId, 'userId');
        Session::add('true', 'redirect', 'success');
        return new Redirect(Session::get('redirect', 'url') ?: '/');
    }
}