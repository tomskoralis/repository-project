<?php

namespace App\Repositories;

use App\Models\User;

interface UsersRepository
{
    public function getErrorMessage(): ?string;

    public function getUser(int $userId): User;

    public function insertUser(User $user): void;

    public function updateUser(User $user, int $userId): void;

    public function deleteUser(int $userId): void;

    public function searchIdByEmail(User $user): int;

    public function getEmailsExcept(int $userId = 0): \Generator;
}