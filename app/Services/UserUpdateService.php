<?php

namespace App\Services;

use App\Container;
use App\Models\User;
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;
use App\Validation\UserValidation;

class UserUpdateService
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

    public function updateNameAndEmail(User $user): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }

        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        $nameValid = $validation->isNameValid($user->getName());
        $emailValid = $validation->isEmailValid($user->getEmail());
        if (
            !$nameValid ||
            !$emailValid ||
            !$validation->isEmailAvailable($user->getEmail(), $user->getId()) ||
            !$validation->isPasswordCorrect($user, 'Edit')
        ) {
            $this->errors = $validation->getErrors();
            return;
        }
        $this->usersRepository::update(
            new User(
                $user->getId(),
                $user->getName(),
                $user->getEmail()
            )
        );
    }

    public function updatePassword(User $user, string $password): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }
        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        $passwordValid = $validation->isPasswordValid($user->getPassword());
        $passwordRepeatedValid = $validation->isPasswordRepeatedValid(
            $user->getPassword(),
            $user->getPasswordRepeated()
        );
        if (
            !$passwordValid ||
            !$passwordRepeatedValid ||
            !$validation->isPasswordCorrect(
                new User(
                    $user->getId(),
                    null,
                    null,
                    $password
                ),
                'Password'
            )
        ) {
            $this->errors = $validation->getErrors();
            return;
        }
        $this->usersRepository::update($user);
    }
}