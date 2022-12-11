<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\DatabaseUsersRepository;
use App\Session;

class UsersService
{
    private ?DatabaseUsersRepository $database;

    public function __construct()
    {
        $this->database = new DatabaseUsersRepository();
        $this->addErrorMessageToSession();
    }

    public function getUser(int $userId): User
    {
        $user = (isset($this->database))
            ? $this->database->getUser($userId)
            : new User();
        $this->addErrorMessageToSession();
        return $user;
    }

    public function insertUser(User $user): void
    {
        if (isset($this->database)) {
            $this->database->addUser($user);
        }
        $this->addErrorMessageToSession();
    }

    public function updateUser(User $user, int $userId): void
    {
        if (isset($this->database)) {
            $this->database->updateUser($user, $userId);
        }
        $this->addErrorMessageToSession();
    }

    public function deleteUser(int $userId): void
    {
        if (isset($this->database)) {
            $this->database->deleteUser($userId);
        }
        $this->addErrorMessageToSession();
    }

    public function searchIdByEmail(User $user): int
    {
        $userId = (isset($this->database)) ? $this->database->searchIdByEmail($user) : 0;
        $this->addErrorMessageToSession();
        return $userId;
    }

    public function getEmailsExcept(int $userId): \Generator
    {
        $emails = [];
        if (isset($this->database)) {
            $emails = $this->database->getEmailsExcept($userId);
        }
        $this->addErrorMessageToSession();
        foreach ($emails as $email) {
            yield $email;
        }
    }

    public function addMoneyToWallet(int $userId, float $amount): void
    {
        if (isset($this->database)) {
            $this->database->addMoneyToWallet($userId, $amount);
        }
        $this->addErrorMessageToSession();
    }

    private function addErrorMessageToSession(): void
    {
        $errorMessage = $this->database->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'database');
        }
    }
}