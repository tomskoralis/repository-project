<?php

namespace App\Repositories;

use App\Models\{Balance, Transaction, Error};
use App\Models\Collections\{BalancesCollection, TransactionsCollection};
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use Doctrine\DBAL\{Connection, DriverManager, Exception};

class MySqlTransactionsRepository implements TransactionsRepository
{
    private static ?MySqlTransactionsRepository $instance = null;
    private static Connection $connection;
    private static ?Error $error = null;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
        $dotenv->load();
        $connectionParams = [
            'dbname' => $_ENV['DATABASE_NAME'],
            'user' => $_ENV['DATABASE_USER'],
            'password' => $_ENV['DATABASE_PASSWORD'],
            'host' => $_ENV['DATABASE_HOST'] ?: 'localhost',
            'driver' => $_ENV['DATABASE_DRIVER'] ?: 'pdo_mysql',
        ];

        try {
            $dotenv->required(['DATABASE_NAME', 'DATABASE_USER', 'DATABASE_PASSWORD',])->notEmpty();
        } catch (ValidationException $e) {
            self::$error = new Error(
                'Dotenv Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
        }

        try {
            self::$connection = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
        }
    }

    public static function getError(): ?Error
    {
        return self::$error;
    }

    public static function fetchTransactions(int $userId, string $symbol = ''): TransactionsCollection
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from('transactions');

            if ($symbol === '') {
                $queryBuilder
                    ->where('user_id = ?')
                    ->setParameter(0, $userId);
            } else {
                $queryBuilder
                    ->where(
                        $queryBuilder->expr()->and(
                            $queryBuilder->expr()->eq('user_id', '?'),
                            $queryBuilder->expr()->eq('symbol', '?')
                        )
                    )
                    ->setParameter(0, $userId)
                    ->setParameter(1, $symbol);
            }

            $transactions = $queryBuilder->executeQuery()->fetchAllAssociative() ?? [];
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
            return new TransactionsCollection();
        }
        $userTransactions = new TransactionsCollection();
        foreach ($transactions as $transaction) {
            $userTransactions->add(new Transaction(
                $transaction['user_id'],
                $transaction['symbol'],
                $transaction['price'],
                $transaction['amount'],
                $transaction['from_user_id'],
                $transaction['time']
            ));
        }
        return $userTransactions;
    }

    public static function fetchBalances(int $userId, string $symbol = ''): BalancesCollection
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from('balances');

            if ($symbol === '') {
                $queryBuilder
                    ->where('id = ?')
                    ->setParameter(0, $userId);
            } else {
                $queryBuilder
                    ->where(
                        $queryBuilder->expr()->and(
                            $queryBuilder->expr()->eq('id', '?'),
                            $queryBuilder->expr()->eq('symbol', '?')
                        )
                    )
                    ->setParameter(0, $userId)
                    ->setParameter(1, $symbol);
            }

            $balances = $queryBuilder->executeQuery()->fetchAllAssociative() ?? [];
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
            return new BalancesCollection();
        }
        $userBalances = new BalancesCollection();
        foreach ($balances as $balance) {
            $userBalances->add(new Balance(
                $balance['id'],
                $balance['symbol'],
                $balance['amount'],
                $balance['value'],
            ));
        }
        return $userBalances;
    }

    public static function add(Transaction $transaction): void
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->insert('transactions')
                ->values([
                    'user_id' => '?',
                    'symbol' => '?',
                    'price' => '?',
                    'amount' => '?',
                    'from_user_id' => '?',
                ])
                ->setParameter(0, $transaction->getUserId())
                ->setParameter(1, $transaction->getSymbol())
                ->setParameter(2, $transaction->getPrice())
                ->setParameter(3, $transaction->getAmount())
                ->setParameter(4, $transaction->getSenderId());
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
        }
    }

    private static function getInstance(): ?MySqlTransactionsRepository
    {
        if (!isset(self::$instance)) {
            self::$instance = new MySqlTransactionsRepository();
        }
        return self::$instance;
    }

    private static function getConnection(): Connection
    {
        return self::getInstance()::$connection;
    }
}