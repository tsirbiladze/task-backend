<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\ProductController;
use App\Services\ProductService;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\Response\JsonResponse;

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
ini_set('display_errors', 0);
error_reporting(E_ALL);

header_remove('X-Powered-By');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(http_response_code(200));
}

$productService = new ProductService($db);
$productController = new ProductController($productService);
$request = ServerRequestFactory::fromGlobals();

try {
    $response = $productController->handleRequest($request);
} catch (\Throwable $e) {
    error_log('Caught exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    $response = new JsonResponse(['error' => 'Internal Server Error'], 500);
}

(new SapiEmitter())->emit($response);