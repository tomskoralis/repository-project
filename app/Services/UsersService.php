<?php

namespace App\Services;

use App\Session;
use App\Models\User;
use App\Repositories\UsersRepository;

class UsersService
{
    private ?UsersRepository $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->addErrorMessageToSession();
    }

    public function getUser(int $userId): User
    {
        $user = (isset($this->usersRepository))
            ? $this->usersRepository->getUser($userId)
            : new User();
        $this->addErrorMessageToSession();
        return $user;
    }

    public function insertUser(User $user): void
    {
        if (isset($this->usersRepository)) {
            $this->usersRepository->addUser($user);
        }
        $this->addErrorMessageToSession();
    }

    public function updateUser(User $user, int $userId): void
    {
        if (isset($this->usersRepository)) {
            $this->usersRepository->updateUser($user, $userId);
        }
        $this->addErrorMessageToSession();
    }

    public function deleteUser(int $userId): void
    {
        if (isset($this->usersRepository)) {
            $this->usersRepository->deleteUser($userId);
        }
        $this->addErrorMessageToSession();
    }

    public function searchIdByEmail(User $user): int
    {
        $userId = (isset($this->usersRepository)) ? $this->usersRepository->searchIdByEmail($user) : 0;
        $this->addErrorMessageToSession();
        return $userId;
    }

    public function getEmailsExcept(int $userId): \Generator
    {
        $emails = [];
        if (isset($this->usersRepository)) {
            $emails = $this->usersRepository->getEmailsExcept($userId);
        }
        $this->addErrorMessageToSession();
        foreach ($emails as $email) {
            yield $email;
        }
    }

    public function addMoneyToWallet(int $userId, float $amount): void
    {
        if (isset($this->usersRepository)) {
            $this->usersRepository->addMoneyToWallet($userId, $amount);
        }
        $this->addErrorMessageToSession();
    }

    private function addErrorMessageToSession(): void
    {
        $errorMessage = $this->usersRepository->getErrorMessage();
        if ($errorMessage !== null) {
            Session::add($errorMessage, 'errors', 'database');
        }
    }
}