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
    ['POST', '/account/delete', [\App\Controllers\UserUpdateController::class, 'deleteUser']],

    ['GET', '/currency', [\App\Controllers\CurrenciesController::class, 'showCurrenciesList']],
    [['GET', 'POST'], '/currency/{symbol}', [\App\Controllers\CurrencyController::class, 'showCurrency']],
    ['POST', '/currency/{symbol}/buy', [\App\Controllers\CurrencyController::class, 'buyCurrency']],
    ['POST', '/currency/{symbol}/sell', [\App\Controllers\CurrencyController::class, 'sellCurrency']],

    ['GET', '/wallet', [\App\Controllers\WalletController::class, 'showWallet']],
    ['POST', '/wallet/deposit', [\App\Controllers\WalletController::class, 'depositMoney']],
    ['POST', '/wallet/withdraw', [\App\Controllers\WalletController::class, 'withdrawMoney']],

    ['GET', '/transactions', [\App\Controllers\TransactionsListController::class, 'showTransactionsList']],
    ['GET', '/statistics', [\App\Controllers\TransactionStatisticsController::class, 'showStatistics']],

    ['GET', '/users', [\App\Controllers\UsersListController::class, 'showUsersList']],
    [['GET', 'POST'], '/users/{page}', [\App\Controllers\UsersListController::class, 'showUsersList']],
    [['GET', 'POST'], '/search/{query}', [\App\Controllers\UsersSearchController::class, 'searchUsers']],
    [['GET', 'POST'], '/search/{query}/{page}', [\App\Controllers\UsersSearchController::class, 'searchUsers']],

    ['GET', '/profile/{userId}', [\App\Controllers\UserProfileController::class, 'showUser']],
    ['POST', '/profile/{userId}/gift', [\App\Controllers\UserProfileController::class, 'giftCurrency']],
];