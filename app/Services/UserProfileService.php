<?php

namespace App\Services;

use App\Models\{User, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;

class UserProfileService
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

    public function getUser(int $userId): ?User
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return null;
        }

        $user = $this->usersRepository::fetchUser($userId);
        if ($user === null) {
            $this->errors->add(
                new Error('User not found!', 'nothingFound')
            );
            return null;
        }
        return new User($user->getId(), $user->getName(), $user->getEmail());
    }
}