<?php

namespace App;

class Template
{
    private string $path;
    private array $parameters;

    public function __construct(string $path, array $parameters = [])
    {
        $this->path = $path;
        $this->parameters = $parameters;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}