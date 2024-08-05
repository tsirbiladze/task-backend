<?php
namespace App\Traits;

trait ValidatorTrait
{
    protected function validateSku(string $sku): bool
    {
        return !empty($sku) && strlen($sku) <= 255;
    }

    protected function validateName(string $name): bool
    {
        return !empty($name) && strlen($name) <= 255;
    }

    protected function validatePrice(float $price): bool
    {
        return $price > 0;
    }
}