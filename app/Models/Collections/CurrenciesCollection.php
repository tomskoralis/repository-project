<?php

namespace App\Models\Collections;

use App\Models\Currency;

class CurrenciesCollection extends Collection
{
    public function add(Currency $item): void
    {
        $this->items [] = $item;
    }
}