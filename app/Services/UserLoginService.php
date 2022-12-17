<?php

namespace App\Services;

use App\Container;
use App\Models\{User, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;
use App\Validation\UserValidation;

class UserLoginService
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

    public function loginAndGetId(User $user): int
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return 0;
        }

        $userId = $this->usersRepository::searchId($user);
        if ($userId === 0) {
            $this->errors->add(
                new Error('Incorrect password!', 'password')
            );
            return 0;
        }
        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        if (
            !$validation->isEmailValid($user) ||
            !$validation->isPasswordValid($user) ||
            !$validation->isPasswordMatchingHash($user, $userId)
        ) {
            $this->errors = $validation->getErrors();
            return 0;
        }
        return $userId;
    }
}