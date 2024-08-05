<?php

namespace App\Controllers;

use App\Exceptions\ProductNotFoundException;
use App\Services\ProductService;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;

class ProductController
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @throws ProductNotFoundException
     */
    public function handleRequest(ServerRequest $request): JsonResponse
    {
        $requestMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();
        $uriSegments = explode('/', trim($uri, '/'));

        if ($uriSegments[0] !== 'products') {
            return new JsonResponse(['error' => 'Not Found'], 404);
        }

        $productId = $uriSegments[1] ?? null;

        return match ($requestMethod) {
            'GET' => $this->handleGetRequest($uri, $productId),
            'POST' => $this->handlePostRequest($request),
            'DELETE' => $this->handleDeleteRequest($request, $productId),
            default => new JsonResponse(['error' => 'Method Not Allowed'], 405),
        };
    }

    /**
     * @throws ProductNotFoundException
     */
    private function handleGetRequest($uri, $productId): JsonResponse
    {
        if ($productId && str_contains($uri, 'check-sku')) {
            $isUnique = !$this->productService->productExists($productId);
            return new JsonResponse(['isUnique' => $isUnique]);
        } elseif ($productId) {
            $product = $this->productService->getProductBySku($productId);
            return new JsonResponse($product);
        } else {
            $products = $this->productService->getAllProducts();
            return new JsonResponse($products);
        }
    }

    private function handlePostRequest(ServerRequest $request): JsonResponse
    {
        $data = json_decode($request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON data'], 400);
        } else {
            try {
                $sku = $this->productService->createProduct($data);
                return new JsonResponse(['sku' => $sku, 'message' => 'Product created successfully'], 201);
            } catch (\PDOException $e) {
                if ($e->getCode() == '23000') {
                    return new JsonResponse(['error' => 'A product with this SKU already exists'], 409);
                } else {
                    error_log('Database error: ' . $e->getMessage());
                    return new JsonResponse(['error' => 'Database error occurred'], 500);
                }
            } catch (\InvalidArgumentException $e) {
                return new JsonResponse(['error' => $e->getMessage()], 400);
            } catch (\Exception $e) {
                error_log('Unexpected error: ' . $e->getMessage());
                return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
            }
        }
    }

    private function handleDeleteRequest(ServerRequest $request, $productId): JsonResponse
    {
        $body = json_decode($request->getBody()->getContents(), true);
        if ($productId) {
            return new JsonResponse(['deleted' => $this->productService->deleteProduct($productId)]);
        } elseif (isset($body['skus']) && is_array($body['skus'])) {
            $deletedCount = $this->productService->massDeleteProducts($body['skus']);
            return new JsonResponse(['deletedCount' => $deletedCount]);
        } else {
            return new JsonResponse(['error' => 'Invalid request for deletion'], 400);
        }
    }
}
