<?php

namespace App\Models;

class User
{
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $passwordRepeated;

    public function __construct(
        string $name = null,
        string $email = null,
        string $password = null,
        string $passwordRepeated = null
    )
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->passwordRepeated = $passwordRepeated;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPasswordRepeated(): ?string
    {
        return $this->passwordRepeated;
    }
}