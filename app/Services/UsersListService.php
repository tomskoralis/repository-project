<?php

namespace App\Services;

use App\Models\Collections\{UsersCollection, ErrorsCollection};
use App\Repositories\UsersRepository;

class UsersListService
{
    private UsersRepository $usersRepository;
    private ErrorsCollection $errors;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->errors = new ErrorsCollection();
    }

    public function getErrors(): ErrorsCollection
    {
        return $this->errors;
    }

    //TODO: do the paging in the repository instead
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

        return $selectedUsers;
    }
}