<?php

namespace App\Validation;

use App\Session;
use App\Models\User;
use App\Services\UsersService;

class UserValidation
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function isUserValid(): bool
    {
        $nameValid = false;
        $emailValid = false;
        $passwordValid = false;
        $passwordRepeatedValid = false;
        if ($this->user->getName() !== null) {
            $nameValid = $this->isNameValid();
        }
        if ($this->user->getEmail() !== null) {
            $emailValid = $this->isEmailValid();
        }
        if ($this->user->getPassword() !== null) {
            $passwordValid = $this->isPasswordValid();
        }
        if ($this->user->getPasswordRepeated() !== null) {
            $passwordRepeatedValid = $this->isPasswordRepeatedValid();
        }
        return ($nameValid && $emailValid && $passwordValid && $passwordRepeatedValid);
    }

    public function isEmailTaken(int $userId = 0): bool
    {
        $usersService = new UsersService();
        $emails = $usersService->getEmailsExcept($userId);
        foreach ($emails as $email) {
            if ($email === $this->user->getEmail()) {
                Session::add('This e-mail is already registered!', 'errors', 'email');
                return false;
            }
        }
        return true;
    }

    public function isPasswordMatchingHash(int $userId = 0, $form = ''): bool
    {
        if (password_verify($this->user->getPassword(), (new UsersService())->getUser($userId)->getPassword())) {
            return true;
        }
        Session::add('Incorrect password!', 'errors', 'passwordMatching' . $form);
        return false;
    }

    private function isNameValid(): bool
    {
        if (strlen($this->user->getName()) < 4) {
            Session::add('Username cannot be shorter than 4 characters!', 'errors', 'name');
            return false;
        }
        if (strlen($this->user->getName()) > 100) {
            Session::add('Username cannot be longer than 100 characters!', 'errors', 'name');
            return false;
        }
        if (!ctype_alnum($this->user->getName())) {
            Session::add('Username cannot contain characters that are not letters or numbers!', 'errors', 'name');
            return false;
        }
        return true;
    }

    private function isEmailValid(): bool
    {
        if (!filter_var($this->user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            Session::add('Invalid e-mail address!', 'errors', 'email');
            return false;
        }
        if (strlen($this->user->getEmail()) > 255) {
            Session::add('E-mail cannot be longer than 255 characters!', 'errors', 'email');
            return false;
        }
        return true;
    }

    private function isPasswordValid(): bool
    {
        if (strlen($this->user->getPassword()) < 6) {
            Session::add('Password cannot be shorter than 6 characters!', 'errors', 'password');
            return false;
        }
        if (strlen($this->user->getPassword()) > 255) {
            Session::add('Password cannot be longer than 255 characters!', 'errors', 'password');
            return false;
        }
        if (!ctype_graph($this->user->getPassword())) {
            Session::add('Password cannot contain special characters!', 'errors', 'password');
            return false;
        }
        return true;
    }

    private function isPasswordRepeatedValid(): bool
    {
        if ($this->user->getPassword() !== $this->user->getPasswordRepeated()) {
            Session::add('Passwords do not match!', 'errors', 'passwordRepeated');
            return false;
        }
        return true;
    }
}