<?php
/**
 * Script to update product images with relevant industrial equipment photos
 * Run this inside Docker: docker exec -it Gordon Food Service-web php /var/www/scripts/update_product_images.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'db',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_NAME'] ?? 'Gordon Food Service'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB connection error: " . $e->getMessage() . "\n");
}

echo "Connected to database.\n";

// Mapping of product SKU patterns to specific image search terms and URLs
$productImageMap = [
    // Hydraulic Systems
    'HYD-PWR' => [
        'search' => 'hydraulic power unit industrial',
        'images' => [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
        ]
    ],
    'HYD-CYL' => [
        'search' => 'hydraulic cylinder industrial',
        'images' => [
            'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800&q=80',
            'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80',
        ]
    ],
    'HYD-VALVE' => [
        'search' => 'hydraulic valve industrial',
        'images' => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
        ]
    ],
    'HYD-ACC' => [
        'search' => 'hydraulic accumulator',
        'images' => [
            'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=800&q=80',
        ]
    ],
    'HYD-COOL' => [
        'search' => 'industrial oil cooler',
        'images' => [
            'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80',
        ]
    ],
    'HYD-FILT' => [
        'search' => 'hydraulic filter industrial',
        'images' => [
            'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800&q=80',
        ]
    ],
    'HYD-MOTOR' => [
        'search' => 'hydraulic motor industrial',
        'images' => [
            'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
        ]
    ],
    'HYD-PUMP' => [
        'search' => 'hydraulic pump industrial',
        'images' => [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
        ]
    ],
    'HYD-MANIFOLD' => [
        'search' => 'hydraulic manifold block',
        'images' => [
            'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80',
        ]
    ],
    'HYD-HOSE' => [
        'search' => 'hydraulic hose assembly',
        'images' => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
        ]
    ],
    'HYD-TANK' => [
        'search' => 'hydraulic reservoir tank',
        'images' => [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
        ]
    ],
    'HYD-SERVO' => [
        'search' => 'servo hydraulic system',
        'images' => [
            'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
        ]
    ],
    
    // Drilling Equipment
    'DRL-' => [
        'search' => 'drilling rig equipment',
        'images' => [
            'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800&q=80',
            'https://images.unsplash.com/photo-1562077981-4d7eafd44932?w=800&q=80',
            'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800&q=80',
        ]
    ],
    'DRL-BOP' => [
        'search' => 'blowout preventer oil gas',
        'images' => [
            'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800&q=80',
        ]
    ],
    'DRL-TDS' => [
        'search' => 'top drive drilling system',
        'images' => [
            'https://images.unsplash.com/photo-1562077981-4d7eafd44932?w=800&q=80',
        ]
    ],
    'DRL-MUD' => [
        'search' => 'mud pump drilling',
        'images' => [
            'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800&q=80',
        ]
    ],
    'DRL-ROT' => [
        'search' => 'rotary table drilling',
        'images' => [
            'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800&q=80',
        ]
    ],
    
    // Pipeline Components
    'PIPE-' => [
        'search' => 'pipeline valve industrial',
        'images' => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
        ]
    ],
    'PIPE-VALVE' => [
        'search' => 'industrial pipeline valve',
        'images' => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
        ]
    ],
    'PIPE-FLANGE' => [
        'search' => 'pipe flange industrial',
        'images' => [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
        ]
    ],
    
    // Compressors
    'COMP-' => [
        'search' => 'industrial compressor',
        'images' => [
            'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80',
            'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
        ]
    ],
    
    // Pumping Systems
    'PUMP-' => [
        'search' => 'industrial pump system',
        'images' => [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80',
        ]
    ],
    
    // Safety Equipment
    'SAFE-' => [
        'search' => 'industrial safety equipment',
        'images' => [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
        ]
    ],
    
    // Instrumentation
    'INST-' => [
        'search' => 'industrial instrumentation gauge',
        'images' => [
            'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80',
        ]
    ],
    
    // Spare Parts
    'SPARE-' => [
        'search' => 'industrial spare parts',
        'images' => [
            'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800&q=80',
        ]
    ],
];

// High-quality industrial equipment images from Unsplash
$industrialImages = [
    // Hydraulic/Mechanical
    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80&sig=hyd1',
    'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80&sig=hyd2',
    'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800&q=80&sig=hyd3',
    'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800&q=80&sig=hyd4',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80&sig=pipe1',
    
    // Drilling/Oil & Gas
    'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800&q=80&sig=drill1',
    'https://images.unsplash.com/photo-1562077981-4d7eafd44932?w=800&q=80&sig=drill2',
    'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800&q=80&sig=drill3',
    
    // Industrial/Factory
    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=800&q=80&sig=ind1',
    'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=800&q=80&sig=ind2',
    
    // Engineering/Technical
    'https://images.unsplash.com/photo-1537462715879-360eeb61a0ad?w=800&q=80&sig=eng1',
    'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800&q=80&sig=eng2',
];

// Get all products
$stmt = $pdo->query('SELECT id, sku, name, category_id FROM products ORDER BY id');
$products = $stmt->fetchAll();

echo "Found " . count($products) . " products to update.\n\n";

$updateStmt = $pdo->prepare('UPDATE products SET image_url = ? WHERE id = ?');

$updated = 0;
foreach ($products as $index => $product) {
    $sku = $product['sku'];
    $imageUrl = null;
    
    // Find matching image based on SKU pattern
    foreach ($productImageMap as $pattern => $config) {
        if (strpos($sku, $pattern) === 0 || strpos($sku, $pattern) !== false) {
            $images = $config['images'];
            // Use product ID to consistently select an image
            $imageIndex = $product['id'] % count($images);
            $imageUrl = $images[$imageIndex];
            // Add unique signature to prevent caching issues
            $imageUrl .= '&pid=' . $product['id'];
            break;
        }
    }
    
    // Fallback to general industrial images
    if (!$imageUrl) {
        $imageIndex = $product['id'] % count($industrialImages);
        $imageUrl = $industrialImages[$imageIndex];
        $imageUrl .= '&pid=' . $product['id'];
    }
    
    $updateStmt->execute([$imageUrl, $product['id']]);
    $updated++;
    
    echo "[$updated/" . count($products) . "] Updated {$sku}: " . substr($imageUrl, 0, 60) . "...\n";
}

echo "\n✅ Successfully updated $updated product images.\n";
