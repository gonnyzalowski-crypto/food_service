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

echo "Seeding 15 new industrial products...\n\n";

// Get category IDs
$categories = [];
$stmt = $pdo->query('SELECT id, slug FROM categories');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[$row['slug']] = $row['id'];
}

// 15 New Products
$products = [
    // Drilling Equipment
    [
        'sku' => 'DRL-MUD-2500',
        'category' => 'drilling-equipment',
        'name' => 'Triplex Mud Pump 2500 HP',
        'short_desc' => 'Heavy-duty triplex mud pump for deep drilling operations',
        'description' => 'The Triplex Mud Pump 2500 HP is a critical component in drilling operations, designed to circulate drilling fluid (mud) under high pressure.',
        'long_description' => "The Triplex Mud Pump 2500 HP is a critical component in drilling operations, designed to circulate drilling fluid (mud) under high pressure. This robust pump features three reciprocating pistons that provide consistent, high-pressure output essential for deep well drilling.\n\n**Technical Specifications:**\n- Maximum Horsepower: 2,500 HP\n- Maximum Pressure: 7,500 PSI\n- Maximum Flow Rate: 1,200 GPM\n- Stroke Length: 12 inches\n- Liner Sizes: 5\" to 7.5\"\n- Crankshaft Speed: 120 RPM max\n- Weight: 85,000 lbs",
        'unit_price' => 320000.00,
        'features' => ['2500 HP', '7500 PSI Max', 'Triplex Design', 'API 7K Certified'],
        'specs' => ['Power' => '2500 HP', 'Max Pressure' => '7500 PSI', 'Flow Rate' => '1200 GPM', 'Weight' => '85,000 lbs'],
        'weight_kg' => 38500,
    ],
    [
        'sku' => 'DRL-TOP-750',
        'category' => 'drilling-equipment',
        'name' => 'Top Drive System 750-Ton',
        'short_desc' => 'Advanced top drive drilling system for efficient operations',
        'description' => 'The Top Drive System 750-Ton represents the pinnacle of drilling technology, replacing traditional rotary table and kelly systems.',
        'long_description' => "The Top Drive System 750-Ton represents the pinnacle of drilling technology, replacing traditional rotary table and kelly systems. This advanced unit provides superior drilling efficiency, enhanced safety, and reduced trip times.\n\n**Technical Specifications:**\n- Maximum Torque: 75,000 ft-lbs\n- Continuous Torque: 50,000 ft-lbs\n- Maximum Speed: 220 RPM\n- Hoisting Capacity: 750 tons",
        'unit_price' => 850000.00,
        'features' => ['750 Ton Capacity', '75,000 ft-lbs Torque', 'AC VFD Motor', 'Integrated Pipe Handler'],
        'specs' => ['Capacity' => '750 Tons', 'Max Torque' => '75,000 ft-lbs', 'Max Speed' => '220 RPM', 'Weight' => '45,000 lbs'],
        'weight_kg' => 20400,
    ],
    [
        'sku' => 'DRL-ROT-3000',
        'category' => 'drilling-equipment',
        'name' => 'Rotary Table 49.5" 3000 HP',
        'short_desc' => 'Heavy-duty rotary table for drilling rig operations',
        'description' => 'The Rotary Table 49.5" 3000 HP is engineered for the most demanding drilling applications.',
        'long_description' => "The Rotary Table 49.5\" 3000 HP is engineered for the most demanding drilling applications. This precision-manufactured unit provides the rotational force necessary to turn the drill string during drilling operations.\n\n**Technical Specifications:**\n- Opening Diameter: 49.5 inches\n- Maximum Torque: 150,000 ft-lbs\n- Maximum Speed: 500 RPM",
        'unit_price' => 185000.00,
        'features' => ['49.5" Opening', '150,000 ft-lbs Torque', '3000 HP Rated', 'Split Housing'],
        'specs' => ['Opening' => '49.5 inches', 'Max Torque' => '150,000 ft-lbs', 'Static Load' => '1,500 tons', 'Weight' => '35,000 lbs'],
        'weight_kg' => 15800,
    ],
    [
        'sku' => 'DRL-DWK-3000',
        'category' => 'drilling-equipment',
        'name' => 'Drawworks 3000 HP',
        'short_desc' => 'High-capacity drawworks for deep drilling rigs',
        'description' => '3000 HP AC gear-driven drawworks designed for hoisting heavy drill strings in deep well applications.',
        'long_description' => "This 3000 HP Drawworks features advanced AC variable frequency drive technology for precise control and regenerative braking. It is the heart of the hoisting system on modern drilling rigs.\n\n**Technical Specifications:**\n- Power Rating: 3,000 HP\n- Line Pull: 150,000 lbs single line\n- Drum Size: 36\" x 60\"\n- Braking System: Regenerative + Disc Brakes",
        'unit_price' => 920000.00,
        'features' => ['3000 HP AC Drive', 'Regenerative Braking', 'Compact Footprint', 'Touchscreen Controls'],
        'specs' => ['Power' => '3000 HP', 'Max Line Pull' => '150,000 lbs', 'Wireline Size' => '1-5/8"', 'Weight' => '110,000 lbs'],
        'weight_kg' => 49900,
    ],
    [
        'sku' => 'DRL-SWV-500',
        'category' => 'drilling-equipment',
        'name' => 'Drilling Swivel 500-Ton',
        'short_desc' => 'High-capacity rotary swivel for fluid circulation',
        'description' => '500-Ton API 8C certified drilling swivel with high-pressure washpipe assembly.',
        'long_description' => "Our 500-Ton Drilling Swivel connects the rotary hose to the drill string, allowing fluid circulation while rotating. Built to API 8C standards for extreme durability.\n\n**Technical Specifications:**\n- Static Load: 500 Tons\n- Dynamic Load: 350 Tons\n- Working Pressure: 7,500 PSI\n- Connection: 6-5/8\" API Reg LH",
        'unit_price' => 65000.00,
        'features' => ['500 Ton Rating', '7500 PSI Washpipe', 'Quick Change Packing', 'Heavy Duty Bearings'],
        'specs' => ['Static Load' => '500 Tons', 'Pressure Rating' => '7,500 PSI', 'Gooseneck Angle' => '15 degrees', 'Weight' => '6,500 lbs'],
        'weight_kg' => 2950,
    ],
    [
        'sku' => 'DRL-KLY-525',
        'category' => 'drilling-equipment',
        'name' => 'Hexagonal Kelly 5.25"',
        'short_desc' => 'Precision-machined hexagonal kelly bar',
        'description' => '5.25 inch hexagonal kelly manufactured from AISI 4145H modified alloy steel.',
        'long_description' => "Premium hexagonal kelly drive section for transferring rotary torque to the drill string. Heat treated for maximum strength and durability.\n\n**Technical Specifications:**\n- Size: 5-1/4\" Hexagonal\n- Length: 40 ft\n- Material: AISI 4145H Mod\n- Standards: API 7-1",
        'unit_price' => 18500.00,
        'features' => ['API 7-1 Certified', 'AISI 4145H Steel', 'Full Length Heat Treat', 'Precision Machined'],
        'specs' => ['Size' => '5.25"', 'Length' => '40 ft', 'Drive Type' => 'Hexagonal', 'Weight' => '3,200 lbs'],
        'weight_kg' => 1450,
    ],
    
    // Compressors
    [
        'sku' => 'CMP-SCR-500',
        'category' => 'compressors',
        'name' => 'Screw Compressor 500 HP',
        'short_desc' => 'Industrial rotary screw compressor for continuous operation',
        'description' => 'The Screw Compressor 500 HP delivers reliable, continuous compressed air for demanding industrial applications.',
        'long_description' => "The Screw Compressor 500 HP delivers reliable, continuous compressed air for demanding industrial applications. Featuring twin helical rotors, this unit provides smooth, pulse-free air delivery with exceptional energy efficiency.\n\n**Technical Specifications:**\n- Motor Power: 500 HP (373 kW)\n- Free Air Delivery: 2,500 CFM\n- Maximum Pressure: 175 PSI (12 bar)",
        'unit_price' => 145000.00,
        'features' => ['500 HP Motor', '2500 CFM', 'VSD Optional', 'PLC Control'],
        'specs' => ['Power' => '500 HP', 'Flow' => '2,500 CFM', 'Max Pressure' => '175 PSI', 'Cooling' => 'Air/Water'],
        'weight_kg' => 5400,
    ],
    
    // Pumping Systems
    [
        'sku' => 'PMP-CNT-1000',
        'category' => 'pumping-systems',
        'name' => 'Centrifugal Pump 1000 GPM',
        'short_desc' => 'High-capacity centrifugal pump for industrial fluid transfer',
        'description' => 'The Centrifugal Pump 1000 GPM is designed for high-volume fluid transfer in demanding industrial environments.',
        'long_description' => "The Centrifugal Pump 1000 GPM is designed for high-volume fluid transfer in demanding industrial environments. This robust pump handles a wide range of fluids including water, hydrocarbons, and process chemicals.\n\n**Technical Specifications:**\n- Flow Rate: 1,000 GPM (227 m³/h)\n- Maximum Head: 500 feet (152 m)\n- Motor Power: 200 HP",
        'unit_price' => 28500.00,
        'features' => ['1000 GPM', '500ft Head', 'Back Pull-out', 'API 610 Compliant'],
        'specs' => ['Flow Rate' => '1,000 GPM', 'Head' => '500 ft', 'Motor' => '200 HP', 'Material' => 'Cast Steel'],
        'weight_kg' => 1600,
    ],
    
    // Pipeline Components
    [
        'sku' => 'PIP-VAL-24',
        'category' => 'pipeline-components',
        'name' => 'Pipeline Ball Valve 24"',
        'short_desc' => 'Large-diameter trunnion-mounted ball valve for pipeline applications',
        'description' => 'The Pipeline Ball Valve 24" is a heavy-duty trunnion-mounted valve designed for critical pipeline isolation applications.',
        'long_description' => "The Pipeline Ball Valve 24\" is a heavy-duty trunnion-mounted valve designed for critical pipeline isolation applications. This valve provides reliable, bubble-tight shutoff in demanding oil and gas transmission systems.\n\n**Technical Specifications:**\n- Nominal Size: 24 inches (DN600)\n- Pressure Class: ANSI 600 (PN100)\n- End Connections: Welded or Flanged",
        'unit_price' => 75000.00,
        'features' => ['24" Trunnion Mounted', 'ANSI 600 Class', 'Double Block & Bleed', 'Fire Safe API 607'],
        'specs' => ['Size' => '24 inches', 'Class' => 'ANSI 600', 'Material' => 'A105N Forged Steel', 'Weight' => '15,000 lbs'],
        'weight_kg' => 6800,
    ],
    [
        'sku' => 'PIP-PIG-20',
        'category' => 'pipeline-components',
        'name' => 'Pig Launcher 20"',
        'short_desc' => 'Pipeline pig launcher/receiver trap 20"',
        'description' => 'ASME U-stamp certified pig launcher for 20" pipelines, complete with quick opening closure.',
        'long_description' => "Engineered pig launcher/receiver station for pipeline maintenance and cleaning operations. Includes major barrel, minor barrel, and quick opening closure for safe operation.\n\n**Technical Specifications:**\n- Size: 20\" x 24\"\n- Design Pressure: 1480 PSI\n- Material: API 5L X60\n- Closure: Bandlock type",
        'unit_price' => 85000.00,
        'features' => ['Quick Opening Closure', 'ASME U-Stamp', 'Safety Interlock', 'Custom Skid'],
        'specs' => ['Pipeline Size' => '20"', 'Barrel Size' => '24"', 'Pressure' => '1480 PSI', 'Length' => '25 ft'],
        'weight_kg' => 5500,
    ],
    [
        'sku' => 'PIP-SRV-06',
        'category' => 'pipeline-components',
        'name' => 'Safety Relief Valve 6"',
        'short_desc' => 'High-capacity pressure relief valve',
        'description' => '6" x 8" Flanged safety relief valve for overpressure protection in process pipelines.',
        'long_description' => "Pilot-operated safety relief valve designed for stable operation and high capacity. Essential for protecting pipeline assets from overpressure events.\n\n**Technical Specifications:**\n- Inlet Size: 6\" ANSI 300\n- Outlet Size: 8\" ANSI 150\n- Set Pressure Range: 50-1000 PSI\n- Orifice Area: 16.00 sq in",
        'unit_price' => 12500.00,
        'features' => ['Pilot Operated', 'High Capacity', 'Field Adjustable', 'API 526 Compliant'],
        'specs' => ['Inlet' => '6"', 'Outlet' => '8"', 'Body Material' => 'WCB Steel', 'Trim' => '316 SS'],
        'weight_kg' => 320,
    ],
    [
        'sku' => 'PIP-HEX-ST',
        'category' => 'pipeline-components',
        'name' => 'Heat Exchanger Shell & Tube',
        'short_desc' => 'Industrial shell and tube heat exchanger for process heating/cooling',
        'description' => 'The Heat Exchanger Shell & Tube is designed for efficient heat transfer in demanding industrial processes.',
        'long_description' => "The Heat Exchanger Shell & Tube is designed for efficient heat transfer in demanding industrial processes. This robust unit handles high pressures and temperatures while providing excellent thermal performance.\n\n**Technical Specifications:**\n- Heat Transfer Area: 500 sq ft (46 m²)\n- Shell Diameter: 24 inches\n- Tube Length: 20 feet",
        'unit_price' => 95000.00,
        'features' => ['TEMA R Class', 'Removable Bundle', 'Floating Head', 'ASME U-Stamp'],
        'specs' => ['Area' => '500 sq ft', 'Shell Design' => '300 PSI', 'Tube Design' => '600 PSI', 'Material' => 'Carbon/SS'],
        'weight_kg' => 4200,
    ],
    
    // Instrumentation
    [
        'sku' => 'INS-GAS-MC',
        'category' => 'instrumentation',
        'name' => 'Gas Detector Multi-Channel',
        'short_desc' => 'Advanced multi-channel gas detection system for industrial safety',
        'description' => 'The Gas Detector Multi-Channel system provides comprehensive gas monitoring for industrial facilities.',
        'long_description' => "The Gas Detector Multi-Channel system provides comprehensive gas monitoring for industrial facilities. This advanced system continuously monitors multiple gas types and locations, providing early warning of hazardous conditions.\n\n**Technical Specifications:**\n- Channels: 16 expandable to 64\n- Detectable Gases: H2S, CO, O2, LEL, SO2, NH3",
        'unit_price' => 18500.00,
        'features' => ['16-64 Channels', 'SIL 2 Certified', 'Touchscreen HMI', 'Data Logging'],
        'specs' => ['Inputs' => '4-20mA / Modbus', 'Relays' => '32 Programmable', 'Power' => '24VDC / 120VAC', 'Rating' => 'NEMA 4X'],
        'weight_kg' => 45,
    ],
    [
        'sku' => 'INS-PRE-10K',
        'category' => 'instrumentation',
        'name' => 'Pressure Transmitter 0-10000 PSI',
        'short_desc' => 'High-accuracy pressure transmitter for extreme pressure applications',
        'description' => 'The Pressure Transmitter 0-10000 PSI delivers precise pressure measurement in the most demanding industrial applications.',
        'long_description' => "The Pressure Transmitter 0-10000 PSI delivers precise pressure measurement in the most demanding industrial applications. This transmitter features advanced sensor technology for exceptional accuracy and long-term stability.\n\n**Technical Specifications:**\n- Range: 0 to 10,000 PSI (0-690 bar)\n- Accuracy: ±0.04% of span\n- Output: 4-20mA with HART",
        'unit_price' => 2450.00,
        'features' => ['10,000 PSI Range', '0.04% Accuracy', 'HART Protocol', 'Explosion Proof'],
        'specs' => ['Range' => '0-10,000 PSI', 'Output' => '4-20mA HART', 'Process Conn' => '1/2" NPT', 'Housing' => '316 SS'],
        'weight_kg' => 2.5,
    ],
    [
        'sku' => 'INS-FLO-US',
        'category' => 'instrumentation',
        'name' => 'Flow Meter Ultrasonic',
        'short_desc' => 'Non-invasive ultrasonic flow meter for accurate flow measurement',
        'description' => 'The Flow Meter Ultrasonic provides accurate, non-invasive flow measurement for a wide range of industrial fluids.',
        'long_description' => "The Flow Meter Ultrasonic provides accurate, non-invasive flow measurement for a wide range of industrial fluids. Using transit-time technology, this meter delivers reliable measurements without pressure drop or moving parts.\n\n**Technical Specifications:**\n- Pipe Sizes: 1\" to 120\" (DN25-DN3000)\n- Flow Velocity: 0.01 to 40 m/s\n- Accuracy: ±0.5% of reading",
        'unit_price' => 8900.00,
        'features' => ['Clamp-on Design', 'No Pressure Drop', 'Bi-directional', 'Data Logging'],
        'specs' => ['Pipe Size' => '1"-120"', 'Accuracy' => '0.5%', 'Temp Range' => '-40 to 150C', 'Output' => 'Modbus/HART'],
        'weight_kg' => 4.5,
    ],
];

$stmt = $pdo->prepare(
    'INSERT INTO products (category_id, sku, name, short_desc, description, long_description, unit_price, features, specifications, weight_kg, warranty_months, lead_time_days, moq, is_active, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
     ON DUPLICATE KEY UPDATE 
        unit_price = VALUES(unit_price), 
        description = VALUES(description),
        long_description = VALUES(long_description),
        short_desc = VALUES(short_desc),
        features = VALUES(features),
        specifications = VALUES(specifications)'
);

foreach ($products as $p) {
    $categoryId = $categories[$p['category']] ?? null;
    
    if (!$categoryId) {
        echo "Category '{$p['category']}' not found. Skipping {$p['name']}\n";
        continue;
    }
    
    try {
        $stmt->execute([
            $categoryId,
            $p['sku'],
            $p['name'],
            $p['short_desc'],
            $p['description'],
            $p['long_description'],
            $p['unit_price'],
            json_encode($p['features']),
            json_encode($p['specs']),
            $p['weight_kg'],
            $p['warranty_months'] ?? 12,
            $p['lead_time_days'] ?? 30,
            $p['moq'] ?? 1
        ]);
        echo "[CREATED] {$p['name']} ({$p['sku']})\n";
    } catch (PDOException $e) {
        echo "[ERROR] Failed to create {$p['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ 15 New products seeded successfully!\n";
