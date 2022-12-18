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

    public function isNameValid(string $name): bool
    {
        if (strlen($name) < 4) {
            $this->errors->add(
                new Error('Username cannot be shorter than 4 characters!', 'name')
            );
            return false;
        }
        if (strlen($name) > 100) {
            $this->errors->add(
                new Error('Username cannot be longer than 100 characters!', 'name')
            );
            return false;
        }
        if (!ctype_alnum($name)) {
            $this->errors->add(
                new Error('Username cannot contain special characters!', 'name')
            );
            return false;
        }
        return true;
    }

    public function isEmailValid(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors->add(
                new Error('Invalid e-mail address!', 'email')
            );
            return false;
        }
        if (strlen($email) > 255) {
            $this->errors->add(
                new Error('E-mail cannot be longer than 255 characters!', 'email')
            );
            return false;
        }
        return true;
    }

    public function isPasswordValid(string $password): bool
    {
        if (strlen($password) < 6) {
            $this->errors->add(
                new Error('Password cannot be shorter than 6 characters!', 'password')
            );
            return false;
        }
        if (strlen($password) > 255) {
            $this->errors->add(
                new Error('Password cannot be longer than 255 characters!', 'password')
            );
            return false;
        }
        if (!ctype_graph($password)) {
            $this->errors->add(
                new Error('Password cannot contain unknown characters!', 'password')
            );
            return false;
        }
        return true;
    }

    public function isPasswordRepeatedValid(string $password, string $passwordRepeated): bool
    {
        if ($password !== $passwordRepeated) {
            $this->errors->add(
                new Error('Passwords do not match!', 'passwordRepeated')
            );
            return false;
        }
        return true;
    }

    public function isEmailAvailable(string $newEmail, int $userId = 0): bool
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return false;
        }

        $emails = $this->usersRepository::fetchAllEmailsExcept($userId);
        foreach ($emails as $email) {
            if ($email === $newEmail) {
                $this->errors->add(
                    new Error('This e-mail is already registered!', 'email')
                );
                return false;
            }
        }
        return true;
    }

    public function isPasswordCorrect(User $user, $form = ''): bool
    {
        $error = $this->usersRepository::getError();
        if ($error !== null) {
            $this->errors->add($error);
            return false;
        }

        if ($this->usersRepository::getError() === null) {
            if (password_verify(
                $user->getPassword(),
                $this->usersRepository::fetchUser($user->getId())->getPassword()
            )) {
                return true;
            }
            $this->errors->add(
                new Error('Incorrect password!', 'passwordMatching' . $form)
            );
        }
        return false;
    }
}