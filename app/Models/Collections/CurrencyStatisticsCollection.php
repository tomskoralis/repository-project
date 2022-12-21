<?php

namespace App\Models\Collections;

use App\Models\CurrencyStatistic;

class CurrencyStatisticsCollection extends Collection
{
    public function add(CurrencyStatistic $item): void
    {
        $this->items [] = $item;
    }
}