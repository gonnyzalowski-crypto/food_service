<?php
/**
 * Scrape 15 more product images and enhance descriptions
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

// Products to scrape (15 more from different categories)
$productsToScrape = [
    'Triplex Mud Pump 2500 HP' => [
        'search' => 'triplex mud pump drilling industrial',
        'folder' => 'triplex-mud-pump-2500-hp',
    ],
    'Top Drive System 750-Ton' => [
        'search' => 'top drive drilling system oilfield',
        'folder' => 'top-drive-system-750-ton',
    ],
    'Rotary Table 49.5" 3000 HP' => [
        'search' => 'rotary table drilling rig industrial',
        'folder' => 'rotary-table-3000-hp',
    ],
    'Drawworks 3000 HP' => [
        'search' => 'drawworks drilling rig hoisting system',
        'folder' => 'drawworks-3000-hp',
    ],
    'Drilling Swivel 500-Ton' => [
        'search' => 'drilling swivel oilfield equipment',
        'folder' => 'drilling-swivel-500-ton',
    ],
    'Hexagonal Kelly 5.25"' => [
        'search' => 'hexagonal kelly drilling pipe',
        'folder' => 'hexagonal-kelly',
    ],
    'Screw Compressor 500 HP' => [
        'search' => 'industrial screw compressor oil gas',
        'folder' => 'screw-compressor-500-hp',
    ],
    'Centrifugal Pump 1000 GPM' => [
        'search' => 'centrifugal pump industrial oil gas',
        'folder' => 'centrifugal-pump-1000-gpm',
    ],
    'Pipeline Ball Valve 24"' => [
        'search' => 'pipeline ball valve industrial large',
        'folder' => 'pipeline-ball-valve-24',
    ],
    'Pig Launcher 20"' => [
        'search' => 'pig launcher pipeline cleaning industrial',
        'folder' => 'pig-launcher-20',
    ],
    'Gas Detector Multi-Channel' => [
        'search' => 'industrial gas detector multi channel safety',
        'folder' => 'gas-detector-multi-channel',
    ],
    'Pressure Transmitter 0-10000 PSI' => [
        'search' => 'pressure transmitter industrial high pressure',
        'folder' => 'pressure-transmitter-10000-psi',
    ],
    'Flow Meter Ultrasonic' => [
        'search' => 'ultrasonic flow meter industrial pipeline',
        'folder' => 'flow-meter-ultrasonic',
    ],
    'Safety Relief Valve 6"' => [
        'search' => 'safety relief valve industrial pressure',
        'folder' => 'safety-relief-valve-6',
    ],
    'Heat Exchanger Shell & Tube' => [
        'search' => 'shell tube heat exchanger industrial',
        'folder' => 'heat-exchanger-shell-tube',
    ],
];

$serpApiKey = $_ENV['SERPAPI_KEY'] ?? null;

if (!$serpApiKey) {
    echo "⚠️  No SERPAPI_KEY found. Using placeholder images.\n\n";
}

$imagesDir = __DIR__ . '/../images/';

foreach ($productsToScrape as $productName => $config) {
    $folder = $config['folder'];
    $searchQuery = $config['search'];
    $productDir = $imagesDir . $folder;
    
    // Skip if folder already exists with images
    if (is_dir($productDir) && count(glob($productDir . '/*.*')) >= 3) {
        echo "[SKIP] $productName - already has images\n";
        continue;
    }
    
    if (!is_dir($productDir)) {
        mkdir($productDir, 0755, true);
    }
    
    if ($serpApiKey) {
        echo "[SCRAPING] $productName...\n";
        
        $url = 'https://serpapi.com/search.json?' . http_build_query([
            'engine' => 'google_images',
            'q' => $searchQuery,
            'api_key' => $serpApiKey,
            'num' => 5,
        ]);
        
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            $images = $data['images_results'] ?? [];
            
            $downloaded = 0;
            foreach (array_slice($images, 0, 3) as $i => $img) {
                $imageUrl = $img['original'] ?? $img['thumbnail'] ?? null;
                if (!$imageUrl) continue;
                
                $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = ($i + 1) . '.' . $ext;
                $filepath = $productDir . '/' . $filename;
                
                $imageData = @file_get_contents($imageUrl, false, stream_context_create([
                    'http' => ['timeout' => 10]
                ]));
                
                if ($imageData && strlen($imageData) > 1000) {
                    file_put_contents($filepath, $imageData);
                    $downloaded++;
                    echo "  ✓ Downloaded image $filename\n";
                }
            }
            
            if ($downloaded > 0) {
                echo "  ✅ Downloaded $downloaded images for $productName\n";
            } else {
                echo "  ⚠️ No images downloaded for $productName\n";
            }
        }
        
        // Rate limiting
        sleep(1);
    } else {
        echo "[PLACEHOLDER] $productName - no API key\n";
    }
}

echo "\n\n=== Updating Product Descriptions ===\n\n";

// Enhanced product descriptions with detailed specs
$enhancedDescriptions = [
    'Triplex Mud Pump 2500 HP' => [
        'short_desc' => 'Heavy-duty triplex mud pump for deep drilling operations',
        'long_description' => "The Triplex Mud Pump 2500 HP is a critical component in drilling operations, designed to circulate drilling fluid (mud) under high pressure. This robust pump features three reciprocating pistons that provide consistent, high-pressure output essential for deep well drilling.\n\n**Technical Specifications:**\n- Maximum Horsepower: 2,500 HP\n- Maximum Pressure: 7,500 PSI\n- Maximum Flow Rate: 1,200 GPM\n- Stroke Length: 12 inches\n- Liner Sizes: 5\" to 7.5\"\n- Crankshaft Speed: 120 RPM max\n- Weight: 85,000 lbs\n\n**Key Features:**\n- Forged alloy steel crankshaft with precision bearings\n- Hardened and ground cylinder liners\n- Dual-acting piston design for smooth operation\n- Integrated pressure relief system\n- API 7K certified construction\n- Modular design for easy maintenance\n\n**Applications:**\n- Offshore drilling platforms\n- Onshore deep well drilling\n- High-pressure well control\n- Extended reach drilling operations",
    ],
    'Top Drive System 750-Ton' => [
        'short_desc' => 'Advanced top drive drilling system for efficient operations',
        'long_description' => "The Top Drive System 750-Ton represents the pinnacle of drilling technology, replacing traditional rotary table and kelly systems. This advanced unit provides superior drilling efficiency, enhanced safety, and reduced trip times.\n\n**Technical Specifications:**\n- Maximum Torque: 75,000 ft-lbs\n- Continuous Torque: 50,000 ft-lbs\n- Maximum Speed: 220 RPM\n- Hoisting Capacity: 750 tons\n- Main Motor: AC Variable Frequency Drive\n- Weight: 45,000 lbs\n\n**Key Features:**\n- Integrated pipe handling system\n- Automatic pipe connection/disconnection\n- Real-time torque and RPM monitoring\n- Built-in IBOP (Internal Blowout Preventer)\n- Dual motor configuration for redundancy\n- Advanced vibration dampening system\n\n**Safety Features:**\n- Emergency disconnect capability\n- Automatic stall protection\n- Integrated load monitoring\n- Fire suppression system ready\n\n**Applications:**\n- Offshore drilling rigs\n- Land-based drilling operations\n- Workover operations\n- Directional drilling",
    ],
    'Rotary Table 49.5" 3000 HP' => [
        'short_desc' => 'Heavy-duty rotary table for drilling rig operations',
        'long_description' => "The Rotary Table 49.5\" 3000 HP is engineered for the most demanding drilling applications. This precision-manufactured unit provides the rotational force necessary to turn the drill string during drilling operations.\n\n**Technical Specifications:**\n- Opening Diameter: 49.5 inches\n- Maximum Torque: 150,000 ft-lbs\n- Maximum Speed: 500 RPM\n- Static Load Capacity: 1,500 tons\n- Rotary Load Capacity: 750 tons\n- Drive Type: Chain or Direct\n- Weight: 35,000 lbs\n\n**Key Features:**\n- Precision-machined master bushing seat\n- Heavy-duty roller bearings\n- Integral lubrication system\n- Hardened and ground sprocket teeth\n- Split housing for easy maintenance\n- API 7K certified\n\n**Construction:**\n- Alloy steel housing\n- Forged steel main shaft\n- Bronze thrust bearings\n- Sealed bearing assemblies\n\n**Applications:**\n- Deep well drilling\n- Offshore platforms\n- Land rigs\n- Workover operations",
    ],
    'Screw Compressor 500 HP' => [
        'short_desc' => 'Industrial rotary screw compressor for continuous operation',
        'long_description' => "The Screw Compressor 500 HP delivers reliable, continuous compressed air for demanding industrial applications. Featuring twin helical rotors, this unit provides smooth, pulse-free air delivery with exceptional energy efficiency.\n\n**Technical Specifications:**\n- Motor Power: 500 HP (373 kW)\n- Free Air Delivery: 2,500 CFM\n- Maximum Pressure: 175 PSI (12 bar)\n- Noise Level: 75 dB(A)\n- Cooling: Air or Water cooled options\n- Weight: 12,000 lbs\n\n**Key Features:**\n- Variable speed drive (VSD) option\n- Integrated air/oil separator\n- Microprocessor controller with touchscreen\n- Automatic start/stop capability\n- Built-in aftercooler and moisture separator\n- Energy recovery system ready\n\n**Efficiency Features:**\n- IE4 premium efficiency motor\n- Low pressure drop air filter\n- Optimized rotor profiles\n- Intelligent capacity control\n\n**Applications:**\n- Oil & gas processing\n- Petrochemical plants\n- Manufacturing facilities\n- Mining operations",
    ],
    'Centrifugal Pump 1000 GPM' => [
        'short_desc' => 'High-capacity centrifugal pump for industrial fluid transfer',
        'long_description' => "The Centrifugal Pump 1000 GPM is designed for high-volume fluid transfer in demanding industrial environments. This robust pump handles a wide range of fluids including water, hydrocarbons, and process chemicals.\n\n**Technical Specifications:**\n- Flow Rate: 1,000 GPM (227 m³/h)\n- Maximum Head: 500 feet (152 m)\n- Suction Size: 8 inches\n- Discharge Size: 6 inches\n- Motor Power: 200 HP\n- Speed: 1,750 RPM\n- Weight: 3,500 lbs\n\n**Key Features:**\n- Back pull-out design for easy maintenance\n- Wear-resistant impeller\n- Mechanical seal with API Plan 11\n- Heavy-duty bearing housing\n- Cast steel casing\n- Dynamically balanced rotor\n\n**Materials:**\n- Casing: Cast Steel or Stainless Steel\n- Impeller: Duplex Stainless Steel\n- Shaft: 17-4 PH Stainless Steel\n- Wear Rings: Bronze or Stellite\n\n**Applications:**\n- Pipeline transfer\n- Refinery operations\n- Water injection\n- Process circulation",
    ],
    'Pipeline Ball Valve 24"' => [
        'short_desc' => 'Large-diameter trunnion-mounted ball valve for pipeline applications',
        'long_description' => "The Pipeline Ball Valve 24\" is a heavy-duty trunnion-mounted valve designed for critical pipeline isolation applications. This valve provides reliable, bubble-tight shutoff in demanding oil and gas transmission systems.\n\n**Technical Specifications:**\n- Nominal Size: 24 inches (DN600)\n- Pressure Class: ANSI 600 (PN100)\n- Temperature Range: -46°C to +200°C\n- End Connections: Welded or Flanged\n- Operation: Gear, Electric, or Pneumatic\n- Weight: 15,000 lbs\n\n**Key Features:**\n- Trunnion-mounted ball design\n- Double block and bleed capability\n- Anti-static device\n- Fire-safe design per API 607\n- Fugitive emission certified\n- Emergency sealant injection\n\n**Materials:**\n- Body: Forged Steel A105N\n- Ball: Inconel 625 overlay\n- Seats: PTFE or Metal-to-Metal\n- Stem: 17-4 PH Stainless Steel\n\n**Certifications:**\n- API 6D\n- API 607 Fire Safe\n- ISO 15848-1 Fugitive Emissions\n- NACE MR0175",
    ],
    'Gas Detector Multi-Channel' => [
        'short_desc' => 'Advanced multi-channel gas detection system for industrial safety',
        'long_description' => "The Gas Detector Multi-Channel system provides comprehensive gas monitoring for industrial facilities. This advanced system continuously monitors multiple gas types and locations, providing early warning of hazardous conditions.\n\n**Technical Specifications:**\n- Channels: 16 expandable to 64\n- Detectable Gases: H2S, CO, O2, LEL, SO2, NH3\n- Response Time: <10 seconds (T90)\n- Display: 10\" color touchscreen\n- Communication: 4-20mA, Modbus, Ethernet\n- Power: 24VDC or 120/240VAC\n\n**Key Features:**\n- SIL 2 certified sensors\n- Automatic sensor calibration\n- Event logging with 100,000 records\n- Remote monitoring capability\n- Redundant power supply option\n- Explosion-proof sensor housings\n\n**Alarm Features:**\n- Three-level alarm thresholds\n- Audible and visual alarms\n- Relay outputs for external devices\n- SMS and email notifications\n- Integration with plant DCS/SCADA\n\n**Applications:**\n- Refineries\n- Chemical plants\n- Offshore platforms\n- Gas processing facilities",
    ],
    'Pressure Transmitter 0-10000 PSI' => [
        'short_desc' => 'High-accuracy pressure transmitter for extreme pressure applications',
        'long_description' => "The Pressure Transmitter 0-10000 PSI delivers precise pressure measurement in the most demanding industrial applications. This transmitter features advanced sensor technology for exceptional accuracy and long-term stability.\n\n**Technical Specifications:**\n- Range: 0 to 10,000 PSI (0-690 bar)\n- Accuracy: ±0.04% of span\n- Output: 4-20mA with HART\n- Process Connection: 1/2\" NPT or 1/4\" NPT\n- Wetted Materials: 316L SS or Hastelloy C\n- Temperature Range: -40°C to +85°C\n\n**Key Features:**\n- Piezoresistive silicon sensor\n- Welded stainless steel construction\n- Integral LCD display option\n- HART 7 communication protocol\n- Lightning and surge protection\n- Remote seal options available\n\n**Performance:**\n- Stability: ±0.1% per year\n- Response Time: 100ms\n- Overpressure: 150% of range\n- Vibration Resistance: 10g\n\n**Certifications:**\n- ATEX/IECEx Zone 0\n- SIL 2/3 capable\n- Marine type approved\n- NACE MR0175 compliant",
    ],
    'Flow Meter Ultrasonic' => [
        'short_desc' => 'Non-invasive ultrasonic flow meter for accurate flow measurement',
        'long_description' => "The Flow Meter Ultrasonic provides accurate, non-invasive flow measurement for a wide range of industrial fluids. Using transit-time technology, this meter delivers reliable measurements without pressure drop or moving parts.\n\n**Technical Specifications:**\n- Pipe Sizes: 1\" to 120\" (DN25-DN3000)\n- Flow Velocity: 0.01 to 40 m/s\n- Accuracy: ±0.5% of reading\n- Repeatability: ±0.15%\n- Output: 4-20mA, Pulse, Modbus, HART\n- Power: 24VDC or 85-265VAC\n\n**Key Features:**\n- Clamp-on or inline installation\n- No pressure drop\n- Bi-directional measurement\n- Built-in data logger\n- Multi-path technology option\n- Automatic gain control\n\n**Display & Interface:**\n- Backlit LCD display\n- USB data download\n- Ethernet connectivity\n- Web-based configuration\n- SCADA integration ready\n\n**Applications:**\n- Custody transfer\n- Process monitoring\n- Water management\n- HVAC systems\n- Chemical processing",
    ],
    'Heat Exchanger Shell & Tube' => [
        'short_desc' => 'Industrial shell and tube heat exchanger for process heating/cooling',
        'long_description' => "The Heat Exchanger Shell & Tube is designed for efficient heat transfer in demanding industrial processes. This robust unit handles high pressures and temperatures while providing excellent thermal performance.\n\n**Technical Specifications:**\n- Heat Transfer Area: 500 sq ft (46 m²)\n- Shell Diameter: 24 inches\n- Tube Length: 20 feet\n- Design Pressure: 300 PSI shell / 600 PSI tube\n- Design Temperature: 650°F (343°C)\n- TEMA Class: R (Refinery Service)\n\n**Key Features:**\n- Removable tube bundle\n- Floating head design\n- Expansion joint for thermal growth\n- Impingement plate protection\n- Segmental baffles\n- ASME U-stamp certified\n\n**Materials:**\n- Shell: Carbon Steel SA-516-70\n- Tubes: Stainless Steel 316L\n- Tube Sheet: SA-516-70 with SS overlay\n- Baffles: Carbon Steel\n\n**Applications:**\n- Crude oil preheating\n- Product cooling\n- Reboiler service\n- Condenser applications\n- Process heating",
    ],
];

// Update descriptions in database
$updateStmt = $pdo->prepare('UPDATE products SET short_desc = ?, long_description = ? WHERE name = ?');

foreach ($enhancedDescriptions as $name => $desc) {
    $updateStmt->execute([$desc['short_desc'], $desc['long_description'], $name]);
    echo "[UPDATED] $name - description enhanced\n";
}

echo "\n\n=== Updating Image References ===\n\n";

// Update image references for newly scraped products
$imageUpdateStmt = $pdo->prepare('UPDATE products SET image_url = ?, gallery_images = ? WHERE name = ?');

foreach ($productsToScrape as $productName => $config) {
    $folder = $config['folder'];
    $productDir = $imagesDir . $folder;
    
    if (is_dir($productDir)) {
        $files = glob($productDir . '/*.*');
        $imageUrls = [];
        
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $imageUrls[] = '/images/' . $folder . '/' . basename($file);
            }
        }
        
        if (!empty($imageUrls)) {
            sort($imageUrls);
            $mainImage = $imageUrls[0];
            $galleryJson = json_encode($imageUrls);
            
            $imageUpdateStmt->execute([$mainImage, $galleryJson, $productName]);
            echo "[IMAGES] $productName - " . count($imageUrls) . " images linked\n";
        }
    }
}

echo "\n✅ Script completed!\n";
