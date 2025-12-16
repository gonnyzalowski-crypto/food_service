<?php
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'] ?? 'db', $_ENV['DB_NAME'] ?? 'Gordon Food Service'),
    $_ENV['DB_USER'] ?? 'Gordon Food Service', $_ENV['DB_PASS'] ?? 'secret',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Mapping placeholder images to new products...\n\n";

$mapping = [
    'Triplex Mud Pump 2500 HP' => 'variable-displacement-pump-500cc',
    'Top Drive System 750-Ton' => 'hydraulic-motor-750cc',
    'Rotary Table 49.5" 3000 HP' => 'hydraulic-test-bench-500-bar',
    'Drawworks 3000 HP' => 'hydraulic-power-unit-5000-hp',
    'Drilling Swivel 500-Ton' => 'high-pressure-filter-assembly',
    'Hexagonal Kelly 5.25"' => 'heavy-duty-hydraulic-cylinder-800-ton',
    'Screw Compressor 500 HP' => 'high-pressure-compressor',
    'Centrifugal Pump 1000 GPM' => 'variable-displacement-pump-500cc',
    'Pipeline Ball Valve 24"' => 'directional-control-valve-4-way',
    'Pig Launcher 20"' => 'hydraulic-reservoir-tank-2000l',
    'Safety Relief Valve 6"' => 'directional-control-valve-4-way',
    'Heat Exchanger Shell & Tube' => 'hydraulic-oil-cooler-1000kw',
    'Gas Detector Multi-Channel' => 'servo-hydraulic-control-system',
    'Pressure Transmitter 0-10000 PSI' => 'custom-hydraulic-manifold-block',
    'Flow Meter Ultrasonic' => 'custom-hydraulic-manifold-block',
];

$updateStmt = $pdo->prepare('UPDATE products SET image_url = ?, gallery_images = ? WHERE name = ?');

foreach ($mapping as $productName => $folderName) {
    $imagesDir = __DIR__ . '/../images/' . $folderName;
    if (is_dir($imagesDir)) {
        $files = glob($imagesDir . '/*.*');
        $imageUrls = [];
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $imageUrls[] = '/images/' . $folderName . '/' . basename($file);
            }
        }
        
        if (!empty($imageUrls)) {
            sort($imageUrls);
            $mainImage = $imageUrls[0];
            $galleryJson = json_encode($imageUrls);
            $updateStmt->execute([$mainImage, $galleryJson, $productName]);
            echo "[UPDATED] $productName -> $folderName (" . count($imageUrls) . " images)\n";
        } else {
            echo "[WARNING] No images found in $folderName for $productName\n";
        }
    } else {
        echo "[ERROR] Folder $folderName does not exist\n";
    }
}

echo "\n✅ Placeholder images assigned!\n";
