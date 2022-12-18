<?php

namespace App\Services;

use App\Container;
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

    public function registerAndGetId(
        string $name,
        string $email,
        string $password,
        string $passwordRepeated
    ): int
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return 0;
        }

        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        $nameValid = $validation->isNameValid($name);
        $emailValid = $validation->isEmailValid($email);
        $passwordValid = $validation->isPasswordValid($password);
        $passwordRepeatedValid = $validation->isPasswordRepeatedValid($password, $passwordRepeated);
        if (
            !$nameValid ||
            !$emailValid ||
            !$passwordValid ||
            !$passwordRepeatedValid ||
            !$validation->isEmailAvailable($email)
        ) {
            $this->errors = $validation->getErrors();
            return 0;
        }

        $this->usersRepository::add($name, $email, $password);
        return $this->usersRepository::getId($email);
    }
}