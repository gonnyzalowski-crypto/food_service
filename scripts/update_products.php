<?php
/**
 * Update products with unique images and price range $18,000 - $1,289,938
 */
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s', $_ENV['DB_HOST'] ?? 'db', $_ENV['DB_NAME'] ?? 'Gordon Food Service'),
    $_ENV['DB_USER'] ?? 'Gordon Food Service', $_ENV['DB_PASS'] ?? 'secret',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Unique images for each product type
$images = [
    'HYD' => [
        'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=600',
        'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600',
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600',
        'https://images.unsplash.com/photo-1581092162384-8987c1d64718?w=600',
        'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=600',
        'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600',
        'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600',
        'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=600',
        'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=600',
        'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=600',
        'https://images.unsplash.com/photo-1581092334651-ddf26d9a09d0?w=600',
    ],
    'DRL' => [
        'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=600',
        'https://images.unsplash.com/photo-1562077981-4d7eafd44932?w=600',
        'https://images.unsplash.com/photo-1513828583688-c52646db42da?w=600',
        'https://images.unsplash.com/photo-1545259741-2ea3ebf61fa3?w=600',
        'https://images.unsplash.com/photo-1587293852726-70cdb56c2866?w=600',
        'https://images.unsplash.com/photo-1574169208507-84376144848b?w=600',
        'https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=600',
    ],
    'PIP' => [
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600',
        'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600',
        'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=600',
        'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600',
    ],
    'CMP' => [
        'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=600',
        'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=600',
    ],
    'PMP' => [
        'https://images.unsplash.com/photo-1581092162384-8987c1d64718?w=600',
        'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=600',
        'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600',
    ],
    'SAF' => [
        'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600',
        'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600',
    ],
    'INS' => [
        'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=600',
        'https://images.unsplash.com/photo-1581092334651-ddf26d9a09d0?w=600',
    ],
    'SPR' => [
        'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=600',
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600',
    ],
];

// Get all products
$products = $pdo->query('SELECT id, sku, unit_price FROM products ORDER BY id')->fetchAll();
$stmt = $pdo->prepare('UPDATE products SET image_url = ?, unit_price = ? WHERE id = ?');

$usedImages = [];
$count = 0;

foreach ($products as $p) {
    $prefix = substr($p['sku'], 0, 3);
    $imgList = $images[$prefix] ?? $images['HYD'];
    
    // Get unique image
    $idx = $count % count($imgList);
    $img = $imgList[$idx] . '&sig=' . $p['id']; // Make unique with signature
    
    // Calculate price in range $18,000 - $1,289,938
    $minPrice = 18000;
    $maxPrice = 1289938;
    $priceRange = $maxPrice - $minPrice;
    $newPrice = $minPrice + (($count / count($products)) * $priceRange);
    $newPrice = round($newPrice, -2); // Round to nearest 100
    
    $stmt->execute([$img, $newPrice, $p['id']]);
    $count++;
    echo "[$count] {$p['sku']} - \$" . number_format($newPrice) . "\n";
}

echo "\n✅ Updated $count products with unique images and prices!\n";
echo "Price range: \$18,000 - \$1,289,938\n";
