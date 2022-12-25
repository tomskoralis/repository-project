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
        $uri = $_SERVER['REQUEST_URI'];
        if ($uri && substr($uri, -1) === '/') {
            $uri = substr($uri, 0, -1);
        }
        return [
            'host' => $_SERVER['HTTP_HOST'],
            'path' => $uri,
        ] ?? [];
    }
}