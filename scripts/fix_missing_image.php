<?php
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'] ?? 'db', $_ENV['DB_NAME'] ?? 'streicher'),
    $_ENV['DB_USER'] ?? 'streicher', $_ENV['DB_PASS'] ?? 'secret',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Fix product ID 58 (Trunnion Ball Valve) - use existing image
$stmt = $pdo->prepare("UPDATE products SET image_url = ?, gallery_images = ? WHERE id = ?");
$stmt->execute([
    '/images/pipeline-pig-launcher-48/1.jpg',
    json_encode(['/images/pipeline-pig-launcher-48/1.jpg']),
    58
]);

echo "Fixed image for product ID 58\n";
