<?php

namespace App;

use App\Repositories\{
    CurrenciesRepository,
    CoinMarketCapCurrenciesRepository,
    UsersRepository,
    MySqlUsersRepository,
    TransactionsRepository,
    MySqlTransactionsRepository
};
use function DI\create;

class Container
{
    private static ?Container $instance = null;
    private static \DI\Container $container;

    private function __construct()
    {
        self::$container = new \DI\Container();
        self::$container->set(CurrenciesRepository::class, create(CoinMarketCapCurrenciesRepository::class));
        self::$container->set(UsersRepository::class, create(MySqlUsersRepository::class));
        self::$container->set(TransactionsRepository::class, create(MySqlTransactionsRepository::class));
    }

    public static function get(string $className)
    {
        return self::getContainer()->get($className);
    }

    private static function getInstance(): ?Container
    {
        if (!isset(self::$instance)) {
            self::$instance = new Container();
        }
        return self::$instance;
    }

    private static function getContainer(): \DI\Container
    {
        return self::getInstance()::$container;
    }
}