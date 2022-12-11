<?php

namespace App\Repositories;

use App\Models\AccountBalance;
use App\Models\Collections\AccountBalancesCollection;
use App\Models\Transaction;
use Doctrine\DBAL\{Connection, DriverManager, Exception};
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;

class DatabaseTransactionsRepository implements TransactionsRepository
{
    private static ?Connection $connection;
    private ?string $errorMessage = null;

    public function __construct()
    {
        if (!isset(self::$connection)) {
            self::$connection = $this->getConnection();
        }
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getBalances(int $userId): AccountBalancesCollection
    {
        if (!isset(self::$connection)) {
            return new AccountBalancesCollection();
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from('balances')
                ->where('id = ?')
                ->setParameter(0, $userId);
            $balances = $queryBuilder->executeQuery()->fetchAllAssociative() ?? [];
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
            return new AccountBalancesCollection();
        }
        $userBalances = new AccountBalancesCollection();
        foreach ($balances as $balance) {
            $userBalances->addBalance(new AccountBalance(
                $balance['symbol'],
                $balance['balance'],
            ));
        }
        return $userBalances;
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (!isset(self::$connection)) {
            return;
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->insert('transactions')
                ->values([
                    'user_id' => '?',
                    'currency_symbol' => '?',
                    'currency_price' => '?',
                    'currency_amount' => '?',
                    'time' => '?',
                ])
                ->setParameter(0, $transaction->getUserId())
                ->setParameter(1, $transaction->getSymbol())
                ->setParameter(2, $transaction->getPrice())
                ->setParameter(3, $transaction->getAmount())
                ->setParameter(4, $transaction->getDateTime());
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
    }

    private function getConnection(): ?Connection
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
            $this->errorMessage = 'Dotenv Exception: ' . $e->getMessage();
            return null;
        }

        try {
            return DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
        return null;
    }
}