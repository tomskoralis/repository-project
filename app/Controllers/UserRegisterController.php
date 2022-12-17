<?php

namespace App\Controllers;

use App\{Template, Redirect, Session};
use App\Models\User;
use App\Services\UserRegisterService;

class UserRegisterController
{
    private UserRegisterService $userRegisterService;

    public function __construct(UserRegisterService $userRegisterService)
    {
        $this->userRegisterService = $userRegisterService;
    }

    public function showRegisterForm(): Template
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
        return new Template('templates/register.twig');
    }

    public function register(): Redirect
    {
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $passwordRepeated = $_POST['passwordRepeated'] ?? '';
        $user = new User($password, $email, $name, $passwordRepeated);

        $userId = $this->userRegisterService->registerAndGetId($user);
        Session::addErrors($this->userRegisterService->getErrors());
        if (Session::has('errors') || $userId === 0) {
            return new Redirect('/register');
        }

        Session::add($userId, 'userId');
        return new Redirect(Session::get('redirectUrl') ?? '/');
    }
}