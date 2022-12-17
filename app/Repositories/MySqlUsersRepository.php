<?php

namespace App\Repositories;

use App\Models\{User, Error};
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use Doctrine\DBAL\{Connection, DriverManager, Exception};

class MySqlUsersRepository implements UsersRepository
{
    private static ?MySqlUsersRepository $instance = null;
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

    public static function fetchUser(int $userId): ?User
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from('users')
                ->where('id = ?')
                ->setParameter(0, $userId);
            $user = $queryBuilder->executeQuery()->fetchAssociative();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
            return null;
        }
        return new User(
            $user['password'],
            $user['email'],
            $user['name'],
            null,
            $user['wallet']
        );
    }

    public static function add(User $user): void
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->insert('users')
                ->values([
                    'name' => '?',
                    'email' => '?',
                    'password' => '?',
                    'wallet' => 0,
                ])
                ->setParameter(0, $user->getName())
                ->setParameter(1, $user->getEmail())
                ->setParameter(2, password_hash($user->getPassword(), PASSWORD_BCRYPT));
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
        }
    }

    public static function update(User $user, int $userId): void
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder->update('users');

            $key = 0;
            if ($user->getName() !== null) {
                $queryBuilder
                    ->set('name', '?')
                    ->setParameter($key, $user->getName());
                $key++;
            }

            if ($user->getEmail() !== null) {
                $queryBuilder
                    ->set('email', '?')
                    ->setParameter($key, $user->getEmail());
                $key++;
            }

            if ($user->getPassword() !== null) {
                $queryBuilder
                    ->set('password', '?')
                    ->setParameter(0, password_hash($user->getPassword(), PASSWORD_BCRYPT));
                $key++;
            }

            $queryBuilder
                ->where('id = ?')
                ->setParameter($key, $userId)
                ->executeQuery();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
        }
    }

    public static function delete(int $userId): void
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->delete('users')
                ->where('id = ?')
                ->setParameter(1, $userId);
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
        }
    }

    public static function searchId(User $user): int
    {
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('id')
                ->from('users')
                ->where('email = ?')
                ->setParameter(0, $user->getEmail());
            return $queryBuilder->executeQuery()->fetchAssociative()['id'] ?? 0;
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
        }
        return 0;
    }

    public static function fetchEmailsExcept(int $userId = 0): \Generator
    {
        $emails = [];
        try {
            $queryBuilder = self::getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('email')
                ->from('users');
            if ($userId !== 0) {
                $queryBuilder
                    ->where('id != ?')
                    ->setParameter(0, $userId);
            }
            $emails = $queryBuilder->executeQuery()->fetchAllAssociative();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
        }
        foreach ($emails as $email) {
            yield $email['email'];
        }
    }

    public static function addMoneyToWallet(int $userId, float $amount): void
    {
        try {
            $sql = "UPDATE users SET wallet = wallet + ? WHERE id = ?";
            $statement = self::getConnection()->prepare($sql);
            $statement->bindValue(1, $amount);
            $statement->bindValue(2, $userId);
            $statement->executeQuery();
        } catch (Exception $e) {
            self::$error = new Error(
                'Database Exception: ' . $e->getMessage(),
                'repository',
                'users'
            );
        }
    }

    private static function getInstance(): ?MySqlUsersRepository
    {
        if (!isset(self::$instance)) {
            self::$instance = new MySqlUsersRepository();
        }
        return self::$instance;
    }

    private static function getConnection(): Connection
    {
        return self::getInstance()::$connection;
    }
}