<?php
namespace App\Exceptions;

use Exception;

class ProductNotFoundException extends Exception
{
    public function __construct($message = "Product not found", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}