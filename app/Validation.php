<?php

namespace App;

use App\Models\User;
use App\Services\UsersService;

class Validation
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
                $_SESSION["errors"]["email"] = "This e-mail is already registered!";
                return false;
            }
        }
        return true;
    }

    public function isPasswordMatchingHash(int $userId = 0, $form = ""): bool
    {
        if (password_verify($this->user->getPassword(), (new UsersService())->getUser($userId)->getPassword())) {
            return true;
        }
        $_SESSION["errors"]["passwordMatching" . $form] = "Incorrect password!";
        return false;
    }

    private function isNameValid(): bool
    {
        if (strlen($this->user->getName()) < 4) {
            $_SESSION["errors"]["name"] = "Username cannot be shorter than 4 characters!";
            return false;
        }
        if (strlen($this->user->getName()) > 100) {
            $_SESSION["errors"]["name"] = "Username cannot be longer than 100 characters!";
            return false;
        }
        if (!ctype_alnum($this->user->getName())) {
            $_SESSION["errors"]["name"] = "Username cannot contain characters that are not letters or numbers!";
            return false;
        }
        return true;
    }

    private function isEmailValid(): bool
    {
        if (!filter_var($this->user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $_SESSION["errors"]["email"] = "Invalid e-mail address!";
            return false;
        }
        if (strlen($this->user->getEmail()) > 255) {
            $_SESSION["errors"]["email"] = "E-mail cannot be longer than 255 characters!";
            return false;
        }
        return true;
    }

    private function isPasswordValid(): bool
    {
        if (strlen($this->user->getPassword()) < 6) {
            $_SESSION["errors"]["password"] = "Password cannot be shorter than 6 characters!";
            return false;
        }
        if (strlen($this->user->getPassword()) > 255) {
            $_SESSION["errors"]["password"] = "Password cannot be longer than 255 characters!";
            return false;
        }
        if (!ctype_graph($this->user->getPassword())) {
            $_SESSION["errors"]["password"] = "Password cannot contain special characters!";
            return false;
        }
        return true;
    }

    private function isPasswordRepeatedValid(): bool
    {
        if ($this->user->getPassword() !== $this->user->getPasswordRepeated()) {
            $_SESSION["errors"]["passwordRepeated"] = "Passwords do not match!";
            return false;
        }
        return true;
    }
}