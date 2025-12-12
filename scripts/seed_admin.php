<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? '127.0.0.1',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_NAME'] ?? 'streicher'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    echo "DB connection error: " . $e->getMessage() . "\n";
    exit(1);
}

if ($argc < 3) {
    echo "Usage: php scripts/seed_admin.php admin-email@example.com password\n";
    exit(1);
}

$email = $argv[1];
$passwordPlain = $argv[2];
$fullName = 'Admin User';

$passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (company_id, email, password_hash, full_name, role)
     VALUES (NULL, :email, :password_hash, :full_name, "admin")
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = "admin"'
);

$stmt->execute([
    'email'         => $email,
    'password_hash' => $passwordHash,
    'full_name'     => $fullName,
]);

echo "Admin user seeded/updated: {$email}\n";
