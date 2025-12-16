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
    $_ENV['DB_NAME'] ?? 'Gordon Food Service'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    echo "DB connection error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Seeding demo data...\n";

// Create demo company
$stmt = $pdo->prepare(
    'INSERT INTO companies (name, vat_id, address, billing_terms)
     VALUES (:name, :vat_id, :address, :billing_terms)
     ON DUPLICATE KEY UPDATE name = VALUES(name)'
);
$stmt->execute([
    'name'          => 'Demo Kunde GmbH',
    'vat_id'        => 'DE123456789',
    'address'       => json_encode([
        'street'  => 'Musterstraße 1',
        'city'    => 'München',
        'zip'     => '80331',
        'country' => 'DE'
    ]),
    'billing_terms' => 'net30',
]);
$companyId = (int)$pdo->lastInsertId() ?: 1;
echo "  - Company: Demo Kunde GmbH (ID: $companyId)\n";

// Create demo warehouse
$stmt = $pdo->prepare(
    'INSERT INTO warehouses (name, code, address)
     VALUES (:name, :code, :address)
     ON DUPLICATE KEY UPDATE name = VALUES(name)'
);
$stmt->execute([
    'name'    => 'Hauptlager Regensburg',
    'code'    => 'WH-REG-01',
    'address' => json_encode([
        'street'  => 'Industriestraße 10',
        'city'    => 'Regensburg',
        'zip'     => '93055',
        'country' => 'DE'
    ]),
]);
$warehouseId = (int)$pdo->lastInsertId() ?: 1;
echo "  - Warehouse: Hauptlager Regensburg (ID: $warehouseId)\n";

// Create demo products
$products = [
    [
        'sku'        => 'PUMP-HYD-001',
        'name'       => 'Hydraulikpumpe HP-500',
        'short_desc' => 'Hochleistungs-Hydraulikpumpe für industrielle Anwendungen. Max. Druck: 350 bar.',
        'unit_price' => 1299.00,
        'moq'        => 1,
        'manufacturer' => 'Gordon Food Service',
        'attributes' => json_encode([
            'max_pressure' => '350 bar',
            'flow_rate'    => '50 l/min',
            'material'     => 'Stahl/Aluminium'
        ]),
    ],
    [
        'sku'        => 'VALVE-CTL-002',
        'name'       => 'Steuerventil SV-200',
        'short_desc' => 'Präzisions-Steuerventil für Öl- und Gasanlagen. DN50, PN40.',
        'unit_price' => 459.50,
        'moq'        => 2,
        'manufacturer' => 'Gordon Food Service',
        'attributes' => json_encode([
            'dn'       => 'DN50',
            'pn'       => 'PN40',
            'material' => 'Edelstahl 316L'
        ]),
    ],
    [
        'sku'        => 'SEAL-KIT-003',
        'name'       => 'Dichtungssatz DS-100',
        'short_desc' => 'Kompletter Dichtungssatz für HP-500 Pumpen. Enthält O-Ringe, Wellendichtungen.',
        'unit_price' => 89.90,
        'moq'        => 5,
        'manufacturer' => 'Gordon Food Service',
        'attributes' => json_encode([
            'compatible_with' => 'PUMP-HYD-001',
            'material'        => 'Viton/NBR'
        ]),
    ],
    [
        'sku'        => 'FILTER-OIL-004',
        'name'       => 'Ölfilter OF-250',
        'short_desc' => 'Hochleistungs-Ölfilter für Hydrauliksysteme. Filterfeinheit: 10 µm.',
        'unit_price' => 124.00,
        'moq'        => 3,
        'manufacturer' => 'Gordon Food Service',
        'attributes' => json_encode([
            'filter_rating' => '10 µm',
            'flow_rate'     => '250 l/min',
            'material'      => 'Edelstahl/Glasfaser'
        ]),
    ],
];

$stmtProduct = $pdo->prepare(
    'INSERT INTO products (sku, name, short_desc, unit_price, moq, manufacturer, attributes)
     VALUES (:sku, :name, :short_desc, :unit_price, :moq, :manufacturer, :attributes)
     ON DUPLICATE KEY UPDATE name = VALUES(name), unit_price = VALUES(unit_price)'
);

$stmtInventory = $pdo->prepare(
    'INSERT INTO inventory (product_id, warehouse_id, quantity, batch_number)
     VALUES (:product_id, :warehouse_id, :quantity, :batch_number)
     ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)'
);

foreach ($products as $p) {
    $stmtProduct->execute($p);
    $productId = (int)$pdo->lastInsertId();
    
    // If product already existed, get its ID
    if ($productId === 0) {
        $stmt = $pdo->prepare('SELECT id FROM products WHERE sku = :sku');
        $stmt->execute(['sku' => $p['sku']]);
        $productId = (int)$stmt->fetchColumn();
    }
    
    // Add inventory
    $stmtInventory->execute([
        'product_id'   => $productId,
        'warehouse_id' => $warehouseId,
        'quantity'     => rand(10, 100),
        'batch_number' => 'BATCH-' . date('Ym') . '-' . rand(100, 999),
    ]);
    
    echo "  - Product: {$p['sku']} - {$p['name']}\n";
}

// Create a demo order
$stmt = $pdo->prepare(
    'INSERT INTO orders (company_id, order_number, status, total, currency, billing_address, shipping_address)
     VALUES (:company_id, :order_number, :status, :total, :currency, :billing_address, :shipping_address)
     ON DUPLICATE KEY UPDATE status = VALUES(status)'
);
$stmt->execute([
    'company_id'      => $companyId,
    'order_number'    => 'ST-DEMO-001',
    'status'          => 'shipped',
    'total'           => 1758.50,
    'currency'        => 'EUR',
    'billing_address' => json_encode(['street' => 'Musterstraße 1', 'city' => 'München', 'zip' => '80331']),
    'shipping_address'=> json_encode(['street' => 'Musterstraße 1', 'city' => 'München', 'zip' => '80331']),
]);
$orderId = (int)$pdo->lastInsertId() ?: 1;
echo "  - Demo order: ST-DEMO-001 (ID: $orderId)\n";

// Create a demo shipment for tracking testing
$stmt = $pdo->prepare(
    'INSERT INTO shipments (order_id, carrier, tracking_number, status, events)
     VALUES (:order_id, :carrier, :tracking_number, :status, :events)
     ON DUPLICATE KEY UPDATE status = VALUES(status)'
);
$stmt->execute([
    'order_id'        => $orderId,
    'carrier'         => 'DHL',
    'tracking_number' => 'JJD000390012345678',
    'status'          => 'in_transit',
    'events'          => json_encode([
        [
            'ts'           => date('Y-m-d H:i', strtotime('-2 days')),
            'location'     => 'Regensburg, DE',
            'status_code'  => 'PICKED_UP',
            'status_label' => 'Sendung abgeholt',
        ],
        [
            'ts'           => date('Y-m-d H:i', strtotime('-1 day')),
            'location'     => 'Nürnberg, DE',
            'status_code'  => 'IN_TRANSIT',
            'status_label' => 'Im Transit',
        ],
        [
            'ts'           => date('Y-m-d H:i'),
            'location'     => 'München, DE',
            'status_code'  => 'OUT_FOR_DELIVERY',
            'status_label' => 'In Zustellung',
        ],
    ]),
]);
echo "  - Demo shipment: JJD000390012345678 (DHL)\n";

echo "\nDemo data seeded successfully!\n";
echo "\nYou can now test:\n";
echo "  - Admin login: http://localhost:8000/admin/login\n";
echo "    Email: admin@Gordon Food Service.de / Password: admin123\n";
echo "  - Product page: http://localhost:8000/product?sku=PUMP-HYD-001\n";
echo "  - Tracking: http://localhost:8000/track?tracking=JJD000390012345678\n";
echo "  - Mailpit (emails): http://localhost:8025\n";
echo "  - phpMyAdmin: http://localhost:8080\n";
