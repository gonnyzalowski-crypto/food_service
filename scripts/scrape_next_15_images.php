<?php
/**
 * Scrape images for the next 15 products that don't have images
 * Uses Unsplash for high-quality industrial images
 */

require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'] ?? 'db', $_ENV['DB_NAME'] ?? 'streicher'),
    $_ENV['DB_USER'] ?? 'streicher', $_ENV['DB_PASS'] ?? 'secret',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Connected to database.\n\n";

// Get products without images
$stmt = $pdo->query("SELECT id, name, sku FROM products WHERE image_url IS NULL OR image_url = '' LIMIT 15");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " products without images.\n\n";

// Industrial equipment image URLs from Unsplash (free to use)
$industrialImages = [
    // Drilling equipment
    'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800', // Oil rig
    'https://images.unsplash.com/photo-1562077981-4d7eafd44932?w=800', // Industrial
    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=800', // Factory
    'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=800', // Machinery
    'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800', // Industrial pipes
    'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800', // Factory equipment
    'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=800', // Industrial
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800', // Pipes
    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800', // Industrial
    'https://images.unsplash.com/photo-1581093458791-9d42e3c7e117?w=800', // Machinery
    'https://images.unsplash.com/photo-1581093450021-4a7360e9a6b5?w=800', // Equipment
    'https://images.unsplash.com/photo-1581093588401-fbb62a02f120?w=800', // Industrial
    'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800', // Factory
    'https://images.unsplash.com/photo-1581093806997-124204d9fa9d?w=800', // Machinery
    'https://images.unsplash.com/photo-1581094271901-8022df4466f9?w=800', // Industrial
];

$imagesDir = __DIR__ . '/../images/';
$updateStmt = $pdo->prepare('UPDATE products SET image_url = ?, gallery_images = ? WHERE id = ?');

foreach ($products as $index => $product) {
    $name = $product['name'];
    $id = $product['id'];
    
    // Create folder name from product name
    $folder = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $folder = trim($folder, '-');
    $productDir = $imagesDir . $folder;
    
    if (!is_dir($productDir)) {
        mkdir($productDir, 0755, true);
    }
    
    echo "[PROCESSING] $name (ID: $id)\n";
    
    // Get image URL (cycle through available images)
    $imageUrl = $industrialImages[$index % count($industrialImages)];
    
    // Download image
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
            'timeout' => 15
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $imageData = @file_get_contents($imageUrl, false, $context);
    
    if ($imageData && strlen($imageData) > 1000) {
        $filename = '1.jpg';
        $filepath = $productDir . '/' . $filename;
        file_put_contents($filepath, $imageData);
        
        $localUrl = '/images/' . $folder . '/' . $filename;
        $galleryJson = json_encode([$localUrl]);
        
        $updateStmt->execute([$localUrl, $galleryJson, $id]);
        echo "  ✅ Downloaded and saved image\n";
        echo "  📁 $localUrl\n";
    } else {
        echo "  ⚠️ Failed to download image\n";
    }
    
    // Small delay to be nice to servers
    usleep(500000); // 0.5 second
}

echo "\n🎉 Image scraping complete!\n";
