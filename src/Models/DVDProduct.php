<?php

namespace App\Models;

class DVDProduct extends Product
{
    private int $size;

    public function __construct(string $sku, string $name, float $price, int $size)
    {
        parent::__construct($sku, $name, $price);
        $this->type = 'dvd';
        $this->size = $size;
    }

    public function getAttributes(): array
    {
        return ['size' => $this->size];
    }
}   