<?php

namespace App\Services;

use App\Models\Error;
use App\Models\Collections\{UsersCollection, ErrorsCollection};
use App\Repositories\UsersRepository;

class UsersListService
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

    public function getUsersList(int $pageSize, int $page): UsersCollection
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return new UsersCollection();
        }

        $allUsers = $this->usersRepository::fetchAllUsers();

        $selectedUsers = new UsersCollection();
        foreach ($allUsers->getAll() as $key => $user) {
            if (
                $key >= ($page - 1) * $pageSize &&
                $key < $page * $pageSize
            ) {
                $selectedUsers->add($user);
            }
        }

        $this->pageCount = ceil($allUsers->getCount() / $pageSize) ?: 1;

        if ($selectedUsers->getCount() === 0) {
            if ($page === 1) {
                $this->errors->add(
                    new Error("No users found!", 'nothingFound')
                );
            } else {
                $this->errors->add(
                    new Error("No users found on page $page!", 'nothingFound')
                );
            }
        }

        return $selectedUsers;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }
}