<?php

namespace App\Models\Collections;

use App\Models\User;

class UsersCollection extends Collection
{
    public function add(User $item): void
    {
        $this->items [] = $item;
    }
}