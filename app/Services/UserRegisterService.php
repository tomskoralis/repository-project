<?php

namespace App\Services;

use App\Container;
use App\Models\User;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;
use App\Validation\UserValidation;

class UserRegisterService
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

    public function registerAndGetId(User $user): int
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return 0;
        }

        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        $nameValid = $validation->isNameValid($user);
        $emailValid = $validation->isEmailValid($user);
        $passwordValid = $validation->isPasswordValid($user);
        $passwordRepeatedValid = $validation->isPasswordRepeatedValid($user);
        if (
            !$nameValid ||
            !$emailValid ||
            !$passwordValid ||
            !$passwordRepeatedValid ||
            !$validation->isEmailAvailable($user)
        ) {
            $this->errors = $validation->getErrors();
            return 0;
        }

        $this->usersRepository::add($user);
        return $this->usersRepository::searchId($user);
    }
}