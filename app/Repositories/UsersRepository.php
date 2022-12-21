<?php

namespace App\Repositories;

use Generator;
use App\Models\{User, Error};
use App\Models\Collections\UsersCollection;

interface UsersRepository
{
    public static function getError(): ?Error;

    public static function fetchUser(int $userId): ?User;

    public static function add($name, $email, $password): void;

    public static function update(User $user): void;

    public static function delete(int $userId): void;

    public static function getId(string $email): int;

    public static function fetchAllEmailsExcept(int $userId = 0): Generator;

    public static function addMoney(int $userId, float $amount): void;

    public static function fetchAllUsers(): UsersCollection;
}