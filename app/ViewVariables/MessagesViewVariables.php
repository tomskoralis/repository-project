<?php

namespace App\ViewVariables;

class MessagesViewVariables implements ViewVariables
{
    public function getName(): string
    {
        return "messages";
    }

    public function getValue(): array
    {
        return $_SESSION["flashMessages"] ?? [];
    }
}