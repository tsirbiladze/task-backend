<?php

namespace App\Models;


class BookProduct extends Product
{
    private float $weight;

    public function __construct(string $sku, string $name, float $price, float $weight)
    {
        parent::__construct($sku, $name, $price);
        $this->type = 'Book';
        $this->weight = $weight;
    }

    public function getAttributes(): array
    {
        return ['weight' => $this->weight];
    }

    public function getWeight(): float
    {
        return $this->weight;
    }
}