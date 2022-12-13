<?php

namespace App;

const ROUTES_MAP = [
    ['GET', '/', [\App\Controllers\CurrenciesController::class, 'displayCurrenciesList']],
    ['GET', '/register', [\App\Controllers\UserRegisterController::class, 'displayRegisterForm']],
    ['POST', '/register', [\App\Controllers\UserRegisterController::class, 'store']],
    ['GET', '/login', [\App\Controllers\UserLoginController::class, 'displayLoginForm']],
    ['POST', '/login', [\App\Controllers\UserLoginController::class, 'login']],
    ['GET', '/logout', [\App\Controllers\UserLoginController::class, 'logout']],
    ['GET', '/account', [\App\Controllers\UserUpdateController::class, 'displayAccount']],
    ['POST', '/account/update', [\App\Controllers\UserUpdateController::class, 'update']],
    ['POST', '/account/update-password', [\App\Controllers\UserUpdateController::class, 'updatePassword']],
    ['POST', '/account/delete', [\App\Controllers\UserUpdateController::class, 'delete']],
    ['POST', '/currency/{symbol}', [\App\Controllers\CurrencyController::class, 'displayCurrency']],
    ['GET', '/currency/{symbol}', [\App\Controllers\CurrencyController::class, 'displayCurrency']],
    ['POST', '/buy-currency', [\App\Controllers\CurrencyController::class, 'buyCurrency']],
    ['POST', '/sell-currency', [\App\Controllers\CurrencyController::class, 'sellCurrency']],
    ['GET', '/wallet', [\App\Controllers\WalletController::class, 'displayWallet']],
    ['POST', '/wallet/add-money', [\App\Controllers\WalletController::class, 'addMoney']],
    ['GET', '/transactions', [\App\Controllers\TransactionsController::class, 'displayTransactionsList']],
];