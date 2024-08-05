<?php

namespace App\Database;

use Exception;
use PDO;
use PDOException;

class Database {
    private PDO $conn;
    private static ?Database $instance = null;

    /**
     * @throws Exception
     */
    private function __construct() {
        $host = $_ENV['DB_HOST'] ?? null;
        $db   = $_ENV['DB_DATABASE'] ?? null;
        $user = $_ENV['DB_USERNAME'] ?? null;
        $pass = $_ENV['DB_PASSWORD'] ?? null;
        $port = $_ENV['DB_PORT'] ?? null;

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch(PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    public function query($sql, $params = []): false|\PDOStatement
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}