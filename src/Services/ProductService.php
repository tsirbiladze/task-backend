<?php

namespace App\Services;

use App\Database\Database;
use App\Models\Product;
use App\Factories\ProductFactory;
use PDO;
use App\Exceptions\ProductNotFoundException;

class ProductService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllProducts(): array
    {
        try {
            $sql = "SELECT p.*, d.size, b.weight, f.height, f.width, f.length 
            FROM products p 
            LEFT JOIN dvds d ON p.id = d.product_id 
            LEFT JOIN books b ON p.id = b.product_id 
            LEFT JOIN furniture f ON p.id = f.product_id 
            ORDER BY p.id";
            $stmt = $this->db->query($sql);
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $product = ProductFactory::createProduct($row['type'], $row);
                $productData = [
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'type' => $product->getType(),
                    'attributes' => $product->getAttributes()
                ];

                if ($product->getType() === 'furniture') {
                    $productData['attributes'] = json_encode($productData['attributes']);
                }

                $products[] = $productData;
            }
            return $products;
        } catch (\Exception $e) {
            error_log('Error fetching products: ' . $e->getMessage());
            throw new \RuntimeException('Failed to fetch products', 0, $e);
        }
    }

    /**
     * @throws ProductNotFoundException
     */
    public function getProductBySku(string $sku): Product
    {
        $sql = "SELECT p.*, d.size, b.weight, f.height, f.width, f.length 
                FROM products p 
                LEFT JOIN dvds d ON p.id = d.product_id 
                LEFT JOIN books b ON p.id = b.product_id 
                LEFT JOIN furniture f ON p.id = f.product_id 
                WHERE p.sku = :sku LIMIT 1";
        $stmt = $this->db->query($sql, [':sku' => $sku]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new ProductNotFoundException("Product with SKU '$sku' not found");
        }

        return ProductFactory::createProduct($row['type'], $row);
    }

    /**
     * @throws \Exception
     */
    public function createProduct(array $data): string
    {
        try {
            $this->db->getConnection()->beginTransaction();

            if (!isset($data['type']) || !is_string($data['type']) || trim($data['type']) === '') {
                throw new \InvalidArgumentException("Product type is missing or empty");
            }

            $data['type'] = strtolower(trim($data['type']));

            if (!in_array($data['type'], ['dvd', 'book', 'furniture'])) {
                throw new \InvalidArgumentException("Invalid product type: " . $data['type']);
            }

            $product = ProductFactory::createProduct($data['type'], $data);

            $sql = "INSERT INTO products (sku, name, price, type) VALUES (:sku, :name, :price, :type)";
            $params = [
                ':sku' => $product->getSku(),
                ':name' => $product->getName(),
                ':price' => $product->getPrice(),
                ':type' => $product->getType(),
            ];

            $stmt = $this->db->query($sql, $params);
            $productId = $this->db->getConnection()->lastInsertId();

            $attributes = $product->getAttributes();
            switch ($data['type']) {
                case 'dvd':
                    $sql = "INSERT INTO dvds (product_id, size) VALUES (:product_id, :size)";
                    $this->db->query($sql, [':product_id' => $productId, ':size' => $data['attribute']]);
                    break;
                case 'book':
                    $sql = "INSERT INTO books (product_id, weight) VALUES (:product_id, :weight)";
                    $this->db->query($sql, [':product_id' => $productId, ':weight' => $data['attribute']]);
                    break;
                case 'furniture':
                    $furnitureData = json_decode($data['attribute'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \InvalidArgumentException("Invalid furniture attributes");
                    }
                    $sql = "INSERT INTO furniture (product_id, height, width, length) VALUES (:product_id, :height, :width, :length)";
                    $this->db->query($sql, [
                        ':product_id' => $productId,
                        ':height' => $furnitureData['height'],
                        ':width' => $furnitureData['width'],
                        ':length' => $furnitureData['length']
                    ]);
                    break;
            }

            $this->db->getConnection()->commit();
            return $product->getSku();
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log('Error creating product: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProduct(Product $product): bool
    {
        $attributes = $product->getAttributes();
        $updates = array_merge(
            ['name = :name', 'price = :price', 'type = :type'],
            array_map(fn($key) => "$key = :$key", array_keys($attributes))
        );

        $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE sku = :sku";

        $params = [
            ':sku' => $product->getSku(),
            ':name' => $product->getName(),
            ':price' => $product->getPrice(),
            ':type' => $product->getType(),
        ];

        foreach ($attributes as $key => $value) {
            $params[":$key"] = $value;
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function deleteProduct(string $sku): bool
    {
        $stmt = $this->db->query("DELETE FROM products WHERE sku = :sku", [':sku' => $sku]);
        return $stmt->rowCount() > 0;
    }

    /**
     * @throws \Exception
     */
    public function massDeleteProducts(array $skus): int
    {
        error_log('Attempting to mass delete products. SKUs: ' . implode(', ', $skus));
        try {
            $this->db->getConnection()->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($skus), '?'));
            $sql = "DELETE FROM products WHERE sku IN ($placeholders)";
            $stmt = $this->db->query($sql, $skus);
            $deletedCount = $stmt->rowCount();

            error_log("Deleted $deletedCount products from 'products' table");

            // წავშალოთ შესაბამისი ჩანაწერები სხვა ცხრილებიდანაც
            $this->db->query("DELETE FROM dvds WHERE product_id IN (SELECT id FROM products WHERE sku IN ($placeholders))", $skus);
            $this->db->query("DELETE FROM books WHERE product_id IN (SELECT id FROM products WHERE sku IN ($placeholders))", $skus);
            $this->db->query("DELETE FROM furniture WHERE product_id IN (SELECT id FROM products WHERE sku IN ($placeholders))", $skus);

            $this->db->getConnection()->commit();
            error_log("Mass deletion completed successfully");
            return $deletedCount;
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log('Error mass deleting products: ' . $e->getMessage());
            throw $e;
        }
    }

    public function productExists(string $sku): bool
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM products WHERE sku = :sku", [':sku' => $sku]);
        return (int) $stmt->fetchColumn() > 0;
    }
}