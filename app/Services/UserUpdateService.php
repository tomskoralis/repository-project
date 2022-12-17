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

    public function updateNameAndEmail(User $user, int $userId): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }

        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        $nameValid = $validation->isNameValid($user);
        $emailValid = $validation->isEmailValid($user);
        if (
            !$nameValid ||
            !$emailValid ||
            !$validation->isEmailAvailable($user, $userId) ||
            !$validation->isPasswordMatchingHash($user, $userId, 'Edit')
        ) {
            $this->errors = $validation->getErrors();
            return;
        }
        $this->usersRepository::update(
            new User(null, $user->getEmail(), $user->getName()),
            $userId
        );
    }

    public function updatePassword(User $user, string $password, int $userId): void
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return;
        }
        $validation = Container::get(UserValidation::class);
        /** @var UserValidation $validation */
        $passwordValid = $validation->isPasswordValid($user);
        $passwordRepeatedValid = $validation->isPasswordRepeatedValid($user);
        if (
            !$passwordValid ||
            !$passwordRepeatedValid
        ) {
            $this->errors = $validation->getErrors();
            return;
        }

        $currentUser = new User($password);
        $currentValidation = Container::get(UserValidation::class);
        /** @var UserValidation $currentValidation */
        if (!$currentValidation->isPasswordMatchingHash($currentUser, $userId, 'Password')) {
            $this->errors = $currentValidation->getErrors();
            return;
        }
        $this->usersRepository::update($user, $userId);
    }
}