<?php

namespace App\Services;

use App\Models\User;
use App\Models\Collections\{UsersCollection, ErrorsCollection};
use App\Repositories\UsersRepository;

class UsersSearchService
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

    //TODO: do the searching and paging in the repository instead
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

        return $selectedUsers;
    }
}