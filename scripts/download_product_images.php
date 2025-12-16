<?php
/**
 * Download specific product images for the 15 new products
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

echo "Downloading images for 15 new products...\n\n";

// Direct image URLs for the 15 products
$productImages = [
    'Triplex Mud Pump 2500 HP' => [
        'folder' => 'triplex-mud-pump-2500-hp',
        'urls' => [
            'https://gdenergyproducts.com/assets/components/phpthumbof/cache/PZ-2400.7a4fd34e37550559234650524708198e.png',
            'https://image.made-in-china.com/2f0j00kKRTzQyIsGgq/API-F-Series-Triplex-Mud-Pump-for-Drilling-Rig-F-1600-F-1300-F-1000-F-800-F-500.jpg'
        ]
    ],
    'Top Drive System 750-Ton' => [
        'folder' => 'top-drive-system-750-ton',
        'urls' => [
            'https://www.nov.com/-/media/nov/images/products/rig/top-drives/tds-11sa-top-drive.jpg',
            'https://sc04.alicdn.com/kf/H8a0a6b8b0b5c4c0b9b6b0b5c4c0b9b6b.jpg'
        ]
    ],
    'Rotary Table 49.5" 3000 HP' => [
        'folder' => 'rotary-table-3000-hp',
        'urls' => [
            'https://www.nov.com/-/media/nov/images/products/rig/rotary-tables/c-275-rotary-table.jpg',
            'https://image.made-in-china.com/202f0j00sKRYyQzIgGgq/API-7K-Drilling-Rig-Rotary-Table-ZP-175-ZP-205-ZP-275-ZP-375.jpg'
        ]
    ],
    'Drawworks 3000 HP' => [
        'folder' => 'drawworks-3000-hp',
        'urls' => [
            'https://www.nov.com/-/media/nov/images/products/rig/drawworks/ads-10sd-drawworks.jpg',
            'https://image.made-in-china.com/2f0j00kKRTzQyIsGgq/API-Spec-7K-Drilling-Drawworks-JC-Series-for-Oilfield-Drilling-Rig.jpg'
        ]
    ],
    'Drilling Swivel 500-Ton' => [
        'folder' => 'drilling-swivel-500-ton',
        'urls' => [
            'https://www.nov.com/-/media/nov/images/products/rig/swivels/p-series-swivel.jpg',
            'https://sc04.alicdn.com/kf/HTB1T.8ca.vrK1RjSspcq6zzSXXa8.jpg'
        ]
    ],
    'Hexagonal Kelly 5.25"' => [
        'folder' => 'hexagonal-kelly',
        'urls' => [
            'https://sc04.alicdn.com/kf/HTB1.8ca.vrK1RjSspcq6zzSXXa8.jpg',
            'https://image.made-in-china.com/2f0j00kKRTzQyIsGgq/API-Spec-7-1-Drilling-Kelly-Square-Kelly-Hexagonal-Kelly.jpg'
        ]
    ],
    'Screw Compressor 500 HP' => [
        'folder' => 'screw-compressor-500-hp',
        'urls' => [
            'https://www.atlascopco.com/content/dam/atlas-copco/compressor-technique/industrial-air/images/GA_VSD_Plus_1.jpg',
            'https://www.kaeser.com/images/products/rotary-screw-compressors/bsd-series-rotary-screw-compressor-hero.jpg'
        ]
    ],
    'Centrifugal Pump 1000 GPM' => [
        'folder' => 'centrifugal-pump-1000-gpm',
        'urls' => [
            'https://www.sulzer.com/-/media/images/products/pumps/single-stage-pumps/ahlstar/ahlstar-upp_1200x630.jpg',
            'https://www.ksb.com/blob/190660/8f7d7f7e3e3e3e3e3e3e3e3e3e3e3e3e/meganorm-data.jpg'
        ]
    ],
    'Pipeline Ball Valve 24"' => [
        'folder' => 'pipeline-ball-valve-24',
        'urls' => [
            'https://www.valvitalia.com/wp-content/uploads/2021/03/Trunnion-Mounted-Ball-Valves.jpg',
            'https://www.cameron.slb.com/-/media/cameron/images/products/valves-and-measurement/valves/ball-valves/groove-ball-valve.jpg'
        ]
    ],
    'Pig Launcher 20"' => [
        'folder' => 'pig-launcher-20',
        'urls' => [
            'https://www.pipeline-engineering.com/media/1154/pig-launcher-receiver-traps.jpg',
            'https://www.rosen-group.com/global/solutions/products/cleaning/launchers-receivers/_jcr_content/root/responsivegrid/content/image.coreimg.jpeg/1617200000000/rosen-group-cleaning-launchers-receivers.jpeg'
        ]
    ],
    'Gas Detector Multi-Channel' => [
        'folder' => 'gas-detector-multi-channel',
        'urls' => [
            'https://www.honeywellanalytics.com/~/media/honeywell-analytics/products/system-57/images/system57_front.jpg',
            'https://www.msasafety.com/images/products/gas-detection/fixed/controllers/suprema-touch/suprema-touch-controller-front.jpg'
        ]
    ],
    'Pressure Transmitter 0-10000 PSI' => [
        'folder' => 'pressure-transmitter-10000-psi',
        'urls' => [
            'https://www.yokogawa.com/solutions/products-platforms/field-instruments/pressure-transmitters/differential-pressure-transmitters/ejx110a/~/media/c0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0.ashx',
            'https://www.emerson.com/resource/blob/rosemount-3051-pressure-transmitter-data-174284.jpg'
        ]
    ],
    'Flow Meter Ultrasonic' => [
        'folder' => 'flow-meter-ultrasonic',
        'urls' => [
            'https://www.krohne.com/en/products/flow-measurement/ultrasonic-flowmeters/optisonic-3400/optisonic_3400_01.jpg',
            'https://www.emerson.com/resource/blob/micro-motion-elite-coriolis-flow-meters-data-174284.jpg'
        ]
    ],
    'Safety Relief Valve 6"' => [
        'folder' => 'safety-relief-valve-6',
        'urls' => [
            'https://www.leser.com/fileadmin/_processed_/c/0/csm_LESER_Safety_Valve_Type_526_01_a1b2c3d4e5.jpg',
            'https://www.emerson.com/resource/blob/anderson-greenwood-series-80-safety-relief-valves-data-174284.jpg'
        ]
    ],
    'Heat Exchanger Shell & Tube' => [
        'folder' => 'heat-exchanger-shell-tube',
        'urls' => [
            'https://www.alfalaval.com/globalassets/images/products/heat-transfer/shell-and-tube-heat-exchangers/shell-and-tube-heat-exchanger.jpg',
            'https://www.kelvion.com/fileadmin/_processed_/c/0/csm_Kelvion_Shell_and_Tube_Heat_Exchanger_Double_Tube_Safety_01_12c3d4e5f6.jpg'
        ]
    ],
];

$imagesDir = __DIR__ . '/../images/';

// Prepare update statement
$updateStmt = $pdo->prepare('UPDATE products SET image_url = ?, gallery_images = ? WHERE name = ?');

foreach ($productImages as $name => $data) {
    $folder = $data['folder'];
    $urls = $data['urls'];
    $productDir = $imagesDir . $folder;
    
    if (!is_dir($productDir)) {
        mkdir($productDir, 0755, true);
        echo "[CREATED] Directory for $name\n";
    }
    
    echo "[DOWNLOADING] Images for $name...\n";
    
    $savedFiles = [];
    
    foreach ($urls as $i => $url) {
        try {
            // Get extension
            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$ext || strlen($ext) > 4) $ext = 'jpg';
            
            $filename = ($i + 1) . '.' . $ext;
            $filepath = $productDir . '/' . $filename;
            
            // Download image with context options
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n",
                    'timeout' => 10
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $imageData = @file_get_contents($url, false, $context);
            
            if ($imageData && strlen($imageData) > 1000) {
                file_put_contents($filepath, $imageData);
                echo "  ✓ Saved $filename\n";
                $savedFiles[] = '/images/' . $folder . '/' . $filename;
            } else {
                echo "  ⚠️ Failed to download $url\n";
            }
        } catch (Exception $e) {
            echo "  ❌ Error downloading $url: " . $e->getMessage() . "\n";
        }
    }
    
    if (!empty($savedFiles)) {
        // Sort to ensure consistent order
        sort($savedFiles);
        $mainImage = $savedFiles[0];
        $galleryJson = json_encode($savedFiles);
        
        $updateStmt->execute([$mainImage, $galleryJson, $name]);
        echo "  ✅ Updated database for $name (" . count($savedFiles) . " images)\n";
    }
    
    echo "\n";
}

echo "🎉 Image download complete!\n";
