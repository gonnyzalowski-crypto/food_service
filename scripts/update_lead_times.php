<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? '127.0.0.1',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_NAME'] ?? 'Gordon Food Service'
);

$pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Check if lead_time_days column exists
$stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'lead_time_days'");
if (!$stmt->fetch()) {
    // Add lead_time_days column
    $pdo->exec("ALTER TABLE products ADD COLUMN lead_time_days INT DEFAULT 14");
    echo "Added lead_time_days column.\n";
}

// Update all products with random lead times between 7 and 21 working days
$stmt = $pdo->query("SELECT id FROM products");
$products = $stmt->fetchAll(PDO::FETCH_COLUMN);

$updateStmt = $pdo->prepare("UPDATE products SET lead_time_days = ? WHERE id = ?");

foreach ($products as $productId) {
    $leadTime = rand(7, 21);
    $updateStmt->execute([$leadTime, $productId]);
}

echo "Updated " . count($products) . " products with random lead times (7-21 working days).\n";
