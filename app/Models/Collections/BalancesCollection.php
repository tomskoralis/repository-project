<?php

namespace App\Models\Collections;

use App\Models\Balance;

class BalancesCollection extends Collection
{
    public function add(Balance $item): void
    {
        $this->items [] = $item;
    }
}