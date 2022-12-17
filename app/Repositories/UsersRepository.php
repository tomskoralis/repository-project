<?php

namespace App\Repositories;

use Generator;
use App\Models\{Error, User};

interface UsersRepository
{
    public static function getError(): ?Error;

    public static function fetchUser(int $userId): ?User;

    public static function add(User $user): void;

    public static function update(User $user, int $userId): void;

    public static function delete(int $userId): void;

    public static function searchId(User $user): int;

    public static function fetchEmailsExcept(int $userId = 0): Generator;

    public static function addMoneyToWallet(int $userId, float $amount): void;
}