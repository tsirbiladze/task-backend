<?php

namespace App\Models;

class FurnitureProduct extends Product
{
    private float $height;
    private float $width;
    private float $length;

    public function __construct(string $sku, string $name, float $price, float $height, float $width, float $length)
    {
        parent::__construct($sku, $name, $price);
        $this->type = 'Furniture';
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
    }

    public function getAttributes(): array
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
            'length' => $this->length
        ];
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getLength(): float
    {
        return $this->length;
    }
}