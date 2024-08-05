<?php

namespace App\Factories;

use App\Models\DVDProduct;
use App\Models\BookProduct;
use App\Models\FurnitureProduct;
use App\Models\Product;

class ProductFactory
{
    public static function createProduct(string $type, array $data): Product
    {
        return match ($type) {
            'dvd' => new DVDProduct(
                $data['sku'],
                $data['name'],
                (float)$data['price'],
                isset($data['size']) ? (int)$data['size'] : 0
            ),
            'book' => new BookProduct(
                $data['sku'],
                $data['name'],
                (float)$data['price'],
                isset($data['weight']) ? (float)$data['weight'] : 0.0
            ),
            'furniture' => new FurnitureProduct(
                $data['sku'],
                $data['name'],
                (float)$data['price'],
                isset($data['height']) ? (float)$data['height'] : 0.0,
                isset($data['width']) ? (float)$data['width'] : 0.0,
                isset($data['length']) ? (float)$data['length'] : 0.0
            ),
            default => throw new \InvalidArgumentException("Invalid product type: $type"),
        };
    }
}