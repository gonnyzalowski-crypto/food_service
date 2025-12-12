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

$newEmail = 'gonnyzalowski@gmail.com';
$newPassword = 'Americana12';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('UPDATE users SET email = ?, password_hash = ? WHERE role = ?');
$stmt->execute([$newEmail, $hash, 'admin']);

echo "Admin credentials updated!\n";
echo "Email: $newEmail\n";
echo "Password: $newPassword\n";
