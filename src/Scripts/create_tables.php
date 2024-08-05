<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try {
    $db = Database::getInstance();
    echo "Database instance created.\n";

    // Drop existing tables
    $tables = ['furniture', 'books', 'dvds', 'products'];
    foreach ($tables as $table) {
        $db->query("DROP TABLE IF EXISTS $table");
        echo "Table '$table' dropped (if existed)\n";
    }

    // Create tables
    $createTables = [
        "products" => "
            CREATE TABLE products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sku VARCHAR(255) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                type ENUM('dvd', 'book', 'furniture') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        "dvds" => "
            CREATE TABLE dvds (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                size INT NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        ",
        "books" => "
            CREATE TABLE books (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                weight DECIMAL(5, 2) NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        ",
        "furniture" => "
            CREATE TABLE furniture (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                height DECIMAL(5, 2) NOT NULL,
                width DECIMAL(5, 2) NOT NULL,
                length DECIMAL(5, 2) NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        "
    ];

    foreach ($createTables as $name => $sql) {
        $db->query($sql);
        echo "Table '$name' created successfully\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    
    // Add more detailed connection information
    echo "Attempting to connect to: " . $_ENV['DB_HOST'] . ":" . $_ENV['DB_PORT'] . "\n";
    
    // Check if MySQL is running
    $connection = @fsockopen($_ENV['DB_HOST'], $_ENV['DB_PORT']);
    if (is_resource($connection)) {
        echo "MySQL server is running and accepting connections.\n";
        fclose($connection);
    } else {
        echo "Cannot establish a connection to MySQL server. Make sure it's running.\n";
    }
}