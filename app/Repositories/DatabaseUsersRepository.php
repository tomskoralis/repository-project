<?php

namespace App\Repositories;

use App\Models\User;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use Doctrine\DBAL\{Connection, DriverManager, Exception};

class DatabaseUsersRepository implements UsersRepository
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

    public function fetchUser(int $userId): User
    {
        if (!isset(self::$connection)) {
            return new User();
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from('users')
                ->where('id = ?')
                ->setParameter(0, $userId);
            $user = $queryBuilder->executeQuery()->fetchAssociative() ?? [];
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
            return new User();
        }
        return new User(
            $user['name'],
            $user['email'],
            $user['password'],
            $user['password'],
            $user['wallet']
        );
    }

    public function addUser(User $user): void
    {
        if (!isset(self::$connection)) {
            return;
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
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
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
    }

    public function updateUser(User $user, int $userId): void
    {
        if (!isset(self::$connection)) {
            return;
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
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
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
    }

    public function deleteUser(int $userId): void
    {
        if (!isset(self::$connection)) {
            return;
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->delete('users')
                ->where('id = ?')
                ->setParameter(1, $userId);
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
    }

    public function searchIdByEmail(User $user): int
    {
        if (!isset(self::$connection)) {
            return 0;
        }
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->select('id')
                ->from('users')
                ->where('email = ?')
                ->setParameter(0, $user->getEmail());
            return $queryBuilder->executeQuery()->fetchAssociative()['id'] ?? 0;
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
        return 0;
    }

    public function fetchEmailsExcept(int $userId = 0): \Generator
    {
        if (!isset(self::$connection)) {
            return;
        }
        $emails = [];
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->select('email')
                ->from('users')
                ->where('id != ?')
                ->setParameter(0, $userId);
            $emails = $queryBuilder->executeQuery()->fetchAllAssociative();
        } catch (Exception $e) {
            $this->errorMessage = 'Database Exception: ' . $e->getMessage();
        }
        foreach ($emails as $email) {
            yield $email['email'];
        }
    }

    public function addMoneyToWallet(int $userId, float $amount): void
    {
        if (!isset(self::$connection)) {
            return;
        }
        try {
            $sql = "UPDATE users SET wallet = wallet + ? WHERE id = ?";
            $statement = self::$connection->prepare($sql);
            $statement->bindValue(1, $amount);
            $statement->bindValue(2, $userId);
            $statement->executeQuery();
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