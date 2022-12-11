<?php

namespace App\ViewVariables;

use App\Session;

class MessagesViewVariables implements ViewVariables
{
    public function getName(): string
    {
        return 'messages';
    }

    public function getValue(): array
    {
        return Session::get('flashMessages') ?? [];
    }
}