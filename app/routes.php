<?php

namespace App;

const ROUTES_MAP = [
    ['GET', '/', [\App\Controllers\CurrenciesController::class, 'showCurrenciesList']],
    ['GET', '/register', [\App\Controllers\UserRegisterController::class, 'showRegisterForm']],
    ['POST', '/register', [\App\Controllers\UserRegisterController::class, 'register']],
    ['GET', '/login', [\App\Controllers\UserLoginController::class, 'showLoginForm']],
    ['POST', '/login', [\App\Controllers\UserLoginController::class, 'login']],
    ['GET', '/logout', [\App\Controllers\UserLogoutController::class, 'logout']],
    ['GET', '/account', [\App\Controllers\UserUpdateController::class, 'showAccount']],
    ['POST', '/account/update', [\App\Controllers\UserUpdateController::class, 'updateNameAndEmail']],
    ['POST', '/account/update-password', [\App\Controllers\UserUpdateController::class, 'updatePassword']],
    ['POST', '/account/delete', [\App\Controllers\UserUpdateController::class, 'delete']],
    [['GET', 'POST'], '/currency/', [\App\Controllers\CurrenciesController::class, 'showCurrenciesList']],
    [['GET', 'POST'], '/currency/{symbol}', [\App\Controllers\CurrencyController::class, 'showCurrency']],
    ['POST', '/buy-currency', [\App\Controllers\CurrencyController::class, 'buyCurrency']],
    ['POST', '/sell-currency', [\App\Controllers\CurrencyController::class, 'sellCurrency']],
    ['GET', '/wallet', [\App\Controllers\WalletController::class, 'showWallet']],
    ['POST', '/wallet/deposit', [\App\Controllers\WalletController::class, 'depositMoney']],
    ['POST', '/wallet/withdraw', [\App\Controllers\WalletController::class, 'withdrawMoney']],
    ['GET', '/transactions', [\App\Controllers\TransactionsListController::class, 'showTransactionsList']],
    ['GET', '/statistics', [\App\Controllers\TransactionStatisticsController::class, 'showStatistics']],
];