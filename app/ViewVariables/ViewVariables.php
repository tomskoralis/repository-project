<?php

namespace App\ViewVariables;

interface ViewVariables
{
    public function getName(): string;

    public function getValue(): array;
}