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

// Create settings table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Insert default settings
$defaults = [
    'company_name' => 'Gordon Food Service GmbH',
    'vat_id' => 'DE123456789',
    'address' => "Industriestraße 45\n93055 Regensburg\nGermany",
    'bank_name' => 'Deutsche Bank AG',
    'account_holder' => 'Gordon Food Service GmbH',
    'iban' => 'DE89 3704 0044 0532 0130 00',
    'bic' => 'COBADEFFXXX',
    'support_email' => 'support@Gordon Food Servicegmbh.com',
    'support_phone' => '+49 991 330-00',
    'sales_email' => 'sales@Gordon Food Servicegmbh.com',
    'shipping_email' => 'shipping@Gordon Food Servicegmbh.com',
    'vat_rate' => '19',
    'currency' => 'USD',
    'free_shipping_threshold' => '5000',
    'notify_new_order' => '1',
    'notify_payment' => '1',
    'notify_low_stock' => '1',
];

$stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($defaults as $key => $value) {
    $stmt->execute([$key, $value]);
}

echo "Settings table created and populated with defaults.\n";
