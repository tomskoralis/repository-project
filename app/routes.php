<?php

namespace App;

const ROUTES_MAP = [
    ["GET", "/", ["App\Controllers\CurrenciesController", "index"]],
    ["GET", "/register", ["App\Controllers\UserRegisterController", "displayRegisterForm"]],
    ["POST", "/register", ["App\Controllers\UserRegisterController", "store"]],
    ["GET", "/login", ["App\Controllers\UserLoginController", "displayLoginForm"]],
    ["POST", "/login", ["App\Controllers\UserLoginController", "login"]],
    ["GET", "/logout", ["App\Controllers\UserLoginController", "logout"]],
    ["GET", "/account", ["App\Controllers\UserUpdateController", "displayAccount"]],
    ["POST", "/update", ["App\Controllers\UserUpdateController", "update"]],
    ["POST", "/update-password", ["App\Controllers\UserUpdateController", "updatePassword"]],
    ["POST", "/delete", ["App\Controllers\UserUpdateController", "delete"]],
];