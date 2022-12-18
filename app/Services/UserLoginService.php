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

    public function loginAndGetId(string $email, string $password): int
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return 0;
        }

        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        if (
            !$validation->isEmailValid($email) ||
            !$validation->isPasswordValid($password)
        ) {
            $this->errors = $validation->getErrors();
            return 0;
        }

        $userId = $this->usersRepository::getId($email);
        if ($userId === 0) {
            $this->errors->add(
                new Error('Incorrect password!', 'password')
            );
            return 0;
        }

        if (!$validation->isPasswordCorrect(
            new User($userId, null, $email, $password)
        )) {
            $this->errors = $validation->getErrors();
            return 0;
        }
        return $userId;
    }
}