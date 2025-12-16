<?php
/**
 * Fix product images - use only the REAL scraped images from local folders
 * Clear placeholder images for products that weren't scraped
 */

require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'] ?? 'db', $_ENV['DB_NAME'] ?? 'Gordon Food Service'),
    $_ENV['DB_USER'] ?? 'Gordon Food Service', $_ENV['DB_PASS'] ?? 'secret',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Connected to database.\n\n";

// Map product names to their scraped image folders
$scrapedProducts = [
    'Hydraulic Power Unit 5000 HP' => 'hydraulic-power-unit-5000-hp',
    'Heavy-Duty Hydraulic Cylinder 800-Ton' => 'heavy-duty-hydraulic-cylinder-800-ton',
    'Directional Control Valve 4-Way' => 'directional-control-valve-4-way',
    'Hydraulic Accumulator 500L' => 'hydraulic-accumulator-500l',
    'Hydraulic Oil Cooler 1000kW' => 'hydraulic-oil-cooler-1000kw',
    'High-Pressure Filter Assembly' => 'high-pressure-filter-assembly',
    'Hydraulic Motor 750cc' => 'hydraulic-motor-750cc',
    'Variable Displacement Pump 500cc' => 'variable-displacement-pump-500cc',
    'Custom Hydraulic Manifold Block' => 'custom-hydraulic-manifold-block',
    'High-Pressure Hose Assembly Kit' => 'high-pressure-hose-assembly-kit',
    'Hydraulic Reservoir Tank 2000L' => 'hydraulic-reservoir-tank-2000l',
    'Servo Hydraulic Control System' => 'servo-hydraulic-control-system',
    'Hydraulic Test Bench 500 Bar' => 'hydraulic-test-bench-500-bar',
    'Pressure Intensifier 1:10' => 'pressure-intensifier-1-10',
    'Hydraulic Clamping System' => 'hydraulic-clamping-system',
];

// Get all products
$stmt = $pdo->query('SELECT id, name FROM products ORDER BY id');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " products.\n\n";

$updateStmt = $pdo->prepare('UPDATE products SET image_url = ?, gallery_images = ? WHERE id = ?');

$updated = 0;
$cleared = 0;

foreach ($products as $product) {
    $name = $product['name'];
    
    if (isset($scrapedProducts[$name])) {
        // This product has real scraped images
        $folder = $scrapedProducts[$name];
        $imagesDir = __DIR__ . '/../images/' . $folder;
        
        if (is_dir($imagesDir)) {
            // Get all image files in the folder
            $files = glob($imagesDir . '/*.*');
            $imageUrls = [];
            
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    // Use relative URL path
                    $imageUrls[] = '/images/' . $folder . '/' . basename($file);
                }
            }
            
            if (!empty($imageUrls)) {
                // Sort to ensure consistent order (1, 2, 3)
                sort($imageUrls);
                
                $mainImage = $imageUrls[0];
                $galleryJson = json_encode($imageUrls);
                
                $updateStmt->execute([$mainImage, $galleryJson, $product['id']]);
                $updated++;
                
                echo "[UPDATED] {$name}\n";
                foreach ($imageUrls as $url) {
                    echo "          - {$url}\n";
                }
            }
        }
    } else {
        // No scraped images - clear the placeholder
        $updateStmt->execute([null, null, $product['id']]);
        $cleared++;
        echo "[CLEARED] {$name}\n";
    }
}

echo "\n✅ Updated $updated products with real scraped images\n";
echo "🗑️  Cleared $cleared products (no scraped images)\n";
