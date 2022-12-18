<?php

namespace App\Models\Collections;

use App\Models\CurrencyPrice;

class PriceCollection extends Collection
{
    public function add(CurrencyPrice $item): void
    {
        $this->items [] = $item;
    }
}