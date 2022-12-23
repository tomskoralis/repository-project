<?php

namespace App\ViewVariables;

class UriPathViewVariables implements ViewVariables
{
    public function getName(): string
    {
        return 'getUrl';
    }

    public function getValue(): array
    {
        return [
            'host' => $_SERVER['HTTP_HOST'],
            'path' => $_SERVER['REQUEST_URI'],
        ] ?? [];
    }
}