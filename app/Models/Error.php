<?php

namespace App\Models;

class Error
{
    private string $message;
    private string $type;
    private ?string $subtype;

    public function __construct(
        string  $message,
        string  $type,
        ?string $subtype = null
    )
    {
        $this->message = $message;
        $this->type = $type;
        $this->subtype = $subtype;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubtype(): ?string
    {
        return $this->subtype;
    }
}