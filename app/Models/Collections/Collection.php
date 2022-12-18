<?php

namespace App\Models\Collections;

use Generator;

class Collection
{
    protected array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getAll(): Generator
    {
        foreach ($this->items as $item) {
            yield $item;
        }
    }

    public function getCount(): int
    {
        return count($this->items);
    }
}