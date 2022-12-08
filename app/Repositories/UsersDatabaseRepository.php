<?php

namespace App\Repositories;

use App\Models\User;
use Doctrine\DBAL\{Connection, DriverManager, Exception};
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;

class UsersDatabaseRepository implements UsersRepository
{
    private static Connection $connection;
    private static ?string $errorMessage = null;

    public function __construct(Dotenv $dotenv)
    {
        self::$connection = self::getConnection($dotenv);
    }

    public function getErrorMessage(): ?string
    {
        return self::$errorMessage;
    }

    public function getUser(int $userId): User
    {
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from('users')
                ->where('id = ?')
                ->setParameter(0, $userId);
            $user = $queryBuilder->executeQuery()->fetchAssociative() ?? [];
        } catch (Exception $e) {
            self::$errorMessage = "Database Exception: " . $e->getMessage();
            return new User();
        }
        return new User(
            $user["name"],
            $user["email"],
            $user["password"],
        );
    }

    public function insertUser(User $user): void
    {
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->insert("users")
                ->values([
                    'name' => '?',
                    'email' => '?',
                    'password' => '?',
                ])
                ->setParameter(0, $user->getName())
                ->setParameter(1, $user->getEmail())
                ->setParameter(2, password_hash($user->getPassword(), PASSWORD_BCRYPT));
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            self::$errorMessage = "Database Exception: " . $e->getMessage();
        }
    }

    public function updateUser(User $user, int $userId): void
    {
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
            self::$errorMessage = "Database Exception: " . $e->getMessage();
        }
    }

    public function deleteUser(int $userId): void
    {
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->delete('users')
                ->where('id = ?')
                ->setParameter(1, $userId);
            $queryBuilder->executeQuery();
        } catch (Exception $e) {
            self::$errorMessage = "Database Exception: " . $e->getMessage();
        }
    }

    public function searchIdByEmail(User $user): int
    {
        try {
            $queryBuilder = self::$connection->createQueryBuilder();
            $queryBuilder
                ->select('id')
                ->from('users')
                ->where('email = ?')
                ->setParameter(0, $user->getEmail());
            return $queryBuilder->executeQuery()->fetchAssociative()["id"] ?? 0;
        } catch (Exception $e) {
            self::$errorMessage = "Database Exception: " . $e->getMessage();
        }
        return 0;
    }

    public function getEmailsExcept(int $userId): \Generator
    {
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
            $_SESSION["errors"]["database"] = "UsersDatabaseRepository Exception: " . $e->getMessage();
        }
        foreach ($emails as $email) {
            yield $email["email"];
        }
    }

    private function getConnection(Dotenv $dotenv): ?Connection
    {
        if (!isset(self::$connection)) {
            $connectionParams = [
                "dbname" => $_ENV["DATABASE_NAME"],
                "user" => $_ENV["DATABASE_USER"],
                "password" => $_ENV["DATABASE_PASSWORD"],
                "host" => $_ENV["DATABASE_HOST"] ?: "localhost",
                "driver" => $_ENV["DATABASE_DRIVER"] ?: "pdo_mysql",
            ];

            try {
                $dotenv->required(["DATABASE_NAME", "DATABASE_USER", "DATABASE_PASSWORD",])->notEmpty();
            } catch (ValidationException $e) {
                self::$errorMessage = "Dotenv Validation Exception: {$e->getMessage()}";
                return null;
            } catch (\Exception $e) {
                self::$errorMessage = "Exception: {$e->getMessage()}";
                return null;
            }

            try {
                self::$connection = DriverManager::getConnection($connectionParams);
                self::$errorMessage = null;
            } catch (Exception $e) {
                self::$errorMessage = "Database Exception: " . $e->getMessage();
                return null;
            }
        }
        return self::$connection;
    }
}