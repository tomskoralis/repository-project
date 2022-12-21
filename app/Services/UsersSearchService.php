<?php

namespace App\Services;

use App\Models\{User, Error};
use App\Models\Collections\{UsersCollection, ErrorsCollection};
use App\Repositories\UsersRepository;

class UsersSearchService
{
    private UsersRepository $usersRepository;
    private ErrorsCollection $errors;
    private int $pageCount;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    public function getUsersList(string $searchQuery, int $pageSize, int $page): UsersCollection
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return new UsersCollection();
        }

        $foundUsers = new UsersCollection();
        foreach ($this->usersRepository::fetchAllUsers()->getAll() as $user) {
            /** @var User $user */
            if (stripos($user->getName(), $searchQuery) !== false) {
                $foundUsers->add($user);
            }
        }

        $selectedUsers = new UsersCollection();
        foreach ($foundUsers->getAll() as $key => $user) {
            if (
                $key >= ($page - 1) * $pageSize &&
                $key < $page * $pageSize
            ) {
                $selectedUsers->add($user);
            }
        }

        $this->pageCount = ceil($foundUsers->getCount() / $pageSize) ?: 1;

        if ($foundUsers->getCount() === 0 && $page === 1) {
            $this->errors->add(
                new Error("No users found when searching for '$searchQuery'!", 'nothingFound')
            );
        }

        if ($selectedUsers->getCount() === 0 && $page > 1) {
            $this->errors->add(
                new Error("No users found on page $page!", 'nothingFound')
            );
        }
        return $selectedUsers;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }
}