<?php

namespace App\Interfaces;

interface ProductInterface
{
    public function getSku(): string;
    public function getName(): string;
    public function getPrice(): float;
    public function getType(): string;
    public function getAttributes(): array;
}