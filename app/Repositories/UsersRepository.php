<?php

namespace App\Repositories;

use App\Models\User;

interface UsersRepository
{
    public function getErrorMessage(): ?string;

    public function fetchUser(int $userId): User;

    public function addUser(User $user): void;

    public function updateUser(User $user, int $userId): void;

    public function deleteUser(int $userId): void;

    public function searchIdByEmail(User $user): int;

    public function fetchEmailsExcept(int $userId = 0): \Generator;

    public function addMoneyToWallet(int $userId, float $amount): void;
}