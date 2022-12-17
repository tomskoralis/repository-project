<?php

namespace App\Models\Collections;

use App\Models\Price;

class PriceCollection extends Collection
{
    public function add(Price $item): void
    {
        $this->items [] = $item;
    }
}