<?php

namespace App\Validation;

use App\Models\{User, Error};
use App\Models\Collections\ErrorsCollection;
use App\Repositories\UsersRepository;

class UserValidation
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

    public function isNameValid(User $user): bool
    {
        if (strlen($user->getName()) < 4) {
            $this->errors->add(
                new Error('Username cannot be shorter than 4 characters!', 'name')
            );
            return false;
        }
        if (strlen($user->getName()) > 100) {
            $this->errors->add(
                new Error('Username cannot be longer than 100 characters!', 'name')
            );
            return false;
        }
        if (!ctype_alnum($user->getName())) {
            $this->errors->add(
                new Error('Username cannot contain special characters!', 'name')
            );
            return false;
        }
        return true;
    }

    public function isEmailValid(User $user): bool
    {
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $this->errors->add(
                new Error('Invalid e-mail address!', 'email')
            );
            return false;
        }
        if (strlen($user->getEmail()) > 255) {
            $this->errors->add(
                new Error('E-mail cannot be longer than 255 characters!', 'email')
            );
            return false;
        }
        return true;
    }

    public function isPasswordValid(User $user): bool
    {
        if (strlen($user->getPassword()) < 6) {
            $this->errors->add(
                new Error('Password cannot be shorter than 6 characters!', 'password')
            );
            return false;
        }
        if (strlen($user->getPassword()) > 255) {
            $this->errors->add(
                new Error('Password cannot be longer than 255 characters!', 'password')
            );
            return false;
        }
        if (!ctype_graph($user->getPassword())) {
            $this->errors->add(
                new Error('Password cannot contain unknown characters!', 'password')
            );
            return false;
        }
        return true;
    }

    public function isPasswordRepeatedValid(User $user): bool
    {
        if ($user->getPassword() !== $user->getPasswordRepeated()) {
            $this->errors->add(
                new Error('Passwords do not match!', 'passwordRepeated')
            );
            return false;
        }
        return true;
    }

    public function isEmailAvailable(User $user, int $userId = 0): bool
    {
        $error = $this->usersRepository->getError();
        if ($error !== null) {
            $this->errors->add($error);
            return false;
        }

        $emails = $this->usersRepository->fetchEmailsExcept($userId);
        foreach ($emails as $email) {
            if ($email === $user->getEmail()) {
                $this->errors->add(
                    new Error('This e-mail is already registered!', 'email')
                );
                return false;
            }
        }
        return true;
    }

    public function isPasswordMatchingHash(User $newUser, int $userId = 0, $form = ''): bool
    {
        $error = $this->usersRepository->getError();
        if ($error !== null) {
            $this->errors->add($error);
            return false;
        }

        $currentUser = $this->usersRepository->fetchUser($userId);
        if ($this->usersRepository->getError() === null) {
            if (password_verify($newUser->getPassword(), $currentUser->getPassword())) {
                return true;
            }
            $this->errors->add(
                new Error('Incorrect password!', 'passwordMatching' . $form)
            );
        }
        return false;
    }
}