<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

try {
    $db = Database::getInstance();
} catch (\Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    exit("Database connection failed. Please check the error log for more details.");
}
