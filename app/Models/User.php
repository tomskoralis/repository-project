<?php

namespace App\Models;

class User
{
    private int $id;
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $passwordRepeated;
    private ?float $wallet;

    public function __construct(
        int    $id,
        string $name = null,
        string $email = null,
        string $password = null,
        string $passwordRepeated = null,
        float  $wallet = null
    )
    {
        $this->id = $id;
        $this->password = $password;
        $this->email = $email;
        $this->name = $name;
        $this->passwordRepeated = $passwordRepeated;
        $this->wallet = $wallet;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getWallet(): ?float
    {
        return $this->wallet;
    }
}