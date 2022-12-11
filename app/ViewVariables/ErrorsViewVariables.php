<?php

namespace App\ViewVariables;

use App\Session;

class ErrorsViewVariables implements ViewVariables
{
    public function getName(): string
    {
        return 'errors';
    }

    public function getValue(): array
    {
        return Session::get('errors') ?? [];
    }
}