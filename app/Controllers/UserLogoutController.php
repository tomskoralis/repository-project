<?php

namespace App\Controllers;

use App\{Redirect, Session};

class UserLogoutController
{
    public function logout(): Redirect
    {
        Session::remove('userId');
        $urlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        if (
            $urlPath === '/account' ||
            $urlPath === '/wallet' ||
            $urlPath === '/transactions' ||
            $urlPath === '/statistics'
        ) {
            return new Redirect('/');
        }
        return new Redirect($urlPath ?? '/');
    }
}