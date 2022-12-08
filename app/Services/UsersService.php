<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UsersDatabaseRepository;
use Dotenv\Dotenv;

class UsersService
{
    private UsersDatabaseRepository $database;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__, "../../.env");
        $dotenv->load();
        $this->database = new UsersDatabaseRepository($dotenv);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
    }

    public function getUser(int $userId): User
    {
        $user = $this->database->getUser($userId);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
        return $user;
    }

    public function insertUser(User $user): void
    {
        $this->database->insertUser($user);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
    }

    public function updateUser(User $user, int $userId): void
    {
        $this->database->updateUser($user, $userId);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
    }

    public function deleteUser(int $userId): void
    {
        $this->database->deleteUser($userId);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
    }

    public function searchIdByEmail(User $user): int
    {
        $userId = $this->database->searchIdByEmail($user);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
        return $userId;
    }

    public function getEmailsExcept(int $userId): \Generator
    {
        $emails = $this->database->getEmailsExcept($userId);
        if ($this->database->getErrorMessage() !== null) {
            $_SESSION["errors"]["database"] = $this->database->getErrorMessage();
        }
        foreach ($emails as $email) {
            yield $email["email"];
        }
    }
}