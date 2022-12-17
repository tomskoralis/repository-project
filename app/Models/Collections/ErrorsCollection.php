<?php

namespace App\Models\Collections;

use App\Models\Error;

class ErrorsCollection extends Collection
{
    public function add(Error $item): void
    {
        $this->items [] = $item;
    }
}