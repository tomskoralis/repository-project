<?php

namespace App\Models;

class User
{
    private ?string $password;
    private ?string $email;
    private ?string $name;
    private ?string $passwordRepeated;
    private ?float $wallet;

    public function __construct(
        string $password = null,
        string $email = null,
        string $name = null,
        string $passwordRepeated = null,
        float  $wallet = null
    )
    {
        $this->password = $password;
        $this->email = $email;
        $this->name = $name;
        $this->passwordRepeated = $passwordRepeated;
        $this->wallet = $wallet;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPasswordRepeated(): ?string
    {
        return $this->passwordRepeated;
    }

    public function getWallet(): ?float
    {
        return $this->wallet;
    }
}