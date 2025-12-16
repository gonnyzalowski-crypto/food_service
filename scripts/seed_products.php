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

echo "Seeding 20 high-value industrial products...\n\n";

// Get category IDs
$categories = [];
$stmt = $pdo->query('SELECT id, slug FROM categories');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[$row['slug']] = $row['id'];
}

// If no categories, create them
if (empty($categories)) {
    echo "Creating categories first...\n";
    $categoryData = [
        ['Hydraulic Systems', 'hydraulic-systems', 'Industrial hydraulic pumps, valves, cylinders, and complete systems'],
        ['Drilling Equipment', 'drilling-equipment', 'Rotary drilling rigs, mud pumps, and drilling accessories'],
        ['Pipeline Components', 'pipeline-components', 'Valves, fittings, flanges, and pipeline accessories'],
        ['Compressors', 'compressors', 'Industrial gas and air compressors for oil & gas applications'],
        ['Pumping Systems', 'pumping-systems', 'Centrifugal, reciprocating, and specialty pumps'],
        ['Safety Equipment', 'safety-equipment', 'Blowout preventers, safety valves, and emergency systems'],
        ['Instrumentation', 'instrumentation', 'Pressure gauges, flow meters, and control systems'],
        ['Spare Parts', 'spare-parts', 'Replacement parts, seals, gaskets, and maintenance kits'],
    ];
    
    $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)');
    $order = 1;
    foreach ($categoryData as $cat) {
        $stmt->execute([$cat[0], $cat[1], $cat[2], $order++]);
        $categories[$cat[1]] = $pdo->lastInsertId();
    }
}

// 20 High-value industrial products ($50,000 - $400,000)
$products = [
    // Hydraulic Systems ($50K - $150K)
    [
        'sku' => 'HYD-PWR-5000',
        'category' => 'hydraulic-systems',
        'name' => 'Gordon Food Service HPU-5000 Hydraulic Power Unit',
        'short_desc' => 'High-performance 500 HP hydraulic power unit with variable displacement pumps and integrated cooling system.',
        'description' => 'The Gordon Food Service HPU-5000 is our flagship hydraulic power unit designed for demanding offshore and onshore drilling operations. This fully integrated system delivers exceptional performance with its twin variable displacement axial piston pumps, capable of generating up to 5,000 PSI working pressure with flow rates up to 200 GPM.',
        'long_description' => "The HPU-5000 represents the pinnacle of hydraulic power technology, engineered specifically for the most demanding industrial applications in the oil and gas sector.\n\n**Key Features:**\n- Twin Rexroth A4VSO variable displacement pumps\n- 500 HP electric motor with soft-start capability\n- Integrated 1,000-gallon reservoir with level monitoring\n- Advanced filtration system (3-micron absolute)\n- PLC-based control system with touchscreen HMI\n- Remote monitoring via Modbus TCP/IP\n- Explosion-proof design (ATEX Zone 1 certified)\n\n**Applications:**\n- Offshore drilling platforms\n- Land-based drilling rigs\n- Heavy-lift cranes\n- BOP control systems\n- Pipeline construction equipment\n\n**Certifications:**\n- API 16D compliant\n- DNV-GL Type Approved\n- ATEX/IECEx certified\n- CE marked",
        'unit_price' => 127500.00,
        'features' => ['500 HP motor', '5000 PSI max pressure', '200 GPM flow rate', 'ATEX Zone 1', 'Remote monitoring'],
        'specs' => [
            'Motor Power' => '500 HP / 373 kW',
            'Max Pressure' => '5,000 PSI / 345 bar',
            'Flow Rate' => '200 GPM / 757 LPM',
            'Reservoir' => '1,000 gallons / 3,785 liters',
            'Dimensions' => '3.5m x 2.2m x 2.4m',
            'Weight' => '8,500 kg',
            'Voltage' => '480V / 60Hz or 400V / 50Hz',
        ],
        'weight_kg' => 8500,
        'warranty_months' => 24,
        'lead_time_days' => 90,
        'moq' => 1,
    ],
    [
        'sku' => 'HYD-CYL-800T',
        'category' => 'hydraulic-systems',
        'name' => 'Heavy-Duty Hydraulic Cylinder 800-Ton',
        'short_desc' => '800-ton capacity double-acting hydraulic cylinder for heavy lifting and pressing applications.',
        'description' => 'Engineered for extreme loads, this 800-ton hydraulic cylinder features chrome-plated piston rod, high-strength alloy steel construction, and precision-machined sealing surfaces for leak-free operation.',
        'long_description' => "Our 800-ton hydraulic cylinder is designed for the most demanding heavy industrial applications where reliability and precision are paramount.\n\n**Construction:**\n- Forged high-strength alloy steel barrel\n- Chrome-plated piston rod (0.025mm tolerance)\n- Polyurethane and bronze wear rings\n- High-pressure seals rated for 10,000 PSI\n\n**Applications:**\n- Shipyard lifting systems\n- Bridge construction\n- Heavy press operations\n- Offshore platform installation",
        'unit_price' => 89000.00,
        'features' => ['800-ton capacity', 'Double-acting', 'Chrome-plated rod', '10,000 PSI rated', 'Custom stroke available'],
        'specs' => [
            'Capacity' => '800 tons / 7,120 kN',
            'Bore Diameter' => '500 mm',
            'Rod Diameter' => '350 mm',
            'Stroke' => '1,500 mm (custom available)',
            'Max Pressure' => '10,000 PSI / 700 bar',
            'Weight' => '4,200 kg',
        ],
        'weight_kg' => 4200,
        'warranty_months' => 24,
        'lead_time_days' => 60,
        'moq' => 1,
    ],
    
    // Drilling Equipment ($100K - $400K)
    [
        'sku' => 'DRL-MUD-2500',
        'category' => 'drilling-equipment',
        'name' => 'Triplex Mud Pump 2500 HP',
        'short_desc' => 'High-pressure triplex mud pump for deep well drilling operations with 7,500 PSI capability.',
        'description' => 'The Gordon Food Service TMP-2500 triplex mud pump delivers exceptional performance for deep drilling operations. Featuring a robust power end with forged steel crankshaft and precision fluid end components.',
        'long_description' => "The TMP-2500 is engineered for continuous operation in the most challenging drilling environments, from arctic conditions to desert heat.\n\n**Power End Features:**\n- Forged alloy steel crankshaft\n- Precision ground crosshead guides\n- Forced lubrication system\n- Heavy-duty roller bearings\n\n**Fluid End Features:**\n- Bi-metal liners (chrome carbide)\n- Tungsten carbide valve seats\n- Quick-change module design\n- Multiple liner sizes available\n\n**Control System:**\n- VFD motor control\n- Real-time pressure monitoring\n- Stroke counter with data logging\n- Remote diagnostic capability",
        'unit_price' => 285000.00,
        'features' => ['2500 HP', '7500 PSI max', 'Triplex design', 'VFD control', 'Arctic rated'],
        'specs' => [
            'Power Rating' => '2,500 HP / 1,864 kW',
            'Max Pressure' => '7,500 PSI / 517 bar',
            'Max Flow Rate' => '1,200 GPM',
            'Stroke Length' => '12 inches / 305 mm',
            'Liner Sizes' => '5" to 7.5"',
            'Weight' => '45,000 kg',
        ],
        'weight_kg' => 45000,
        'warranty_months' => 24,
        'lead_time_days' => 120,
        'moq' => 1,
    ],
    [
        'sku' => 'DRL-TDS-750',
        'category' => 'drilling-equipment',
        'name' => 'Top Drive System 750-Ton',
        'short_desc' => 'Advanced 750-ton top drive system with integrated pipe handling and torque monitoring.',
        'description' => 'State-of-the-art top drive system featuring AC variable frequency drive, integrated IBOP, and advanced torque/drag monitoring for optimized drilling performance.',
        'long_description' => "The TDS-750 represents the latest advancement in top drive technology, combining power, precision, and reliability for modern drilling operations.\n\n**Drive System:**\n- Dual AC motors (750 HP each)\n- Helical gear reduction\n- Continuous duty rating\n- Regenerative braking\n\n**Pipe Handling:**\n- Integrated elevator links\n- Automated pipe spinner\n- Torque wrench system\n- Hands-free connection\n\n**Monitoring & Control:**\n- Real-time torque measurement\n- Weight-on-bit calculation\n- Stick-slip detection\n- Directional drilling interface",
        'unit_price' => 395000.00,
        'features' => ['750-ton capacity', 'Dual AC motors', 'Integrated IBOP', 'Torque monitoring', 'Pipe handling'],
        'specs' => [
            'Capacity' => '750 tons / 6,675 kN',
            'Continuous Torque' => '65,000 ft-lbs',
            'Max Speed' => '250 RPM',
            'Motor Power' => '2 x 750 HP',
            'Weight' => '35,000 kg',
            'Height' => '12.5 m',
        ],
        'weight_kg' => 35000,
        'warranty_months' => 24,
        'lead_time_days' => 180,
        'moq' => 1,
    ],
    [
        'sku' => 'DRL-ROT-3000',
        'category' => 'drilling-equipment',
        'name' => 'Rotary Table 49.5" - 3000 HP',
        'short_desc' => 'Heavy-duty 49.5-inch rotary table rated for 3000 HP with integrated master bushing.',
        'description' => 'Premium rotary table designed for high-torque drilling applications. Features precision-machined table surface, heavy-duty bearings, and integrated lubrication system.',
        'long_description' => "Our 49.5-inch rotary table is built for the most demanding drilling operations, delivering reliable performance in continuous heavy-duty service.\n\n**Construction:**\n- Cast steel housing\n- Precision-ground table surface\n- Tapered roller bearings\n- Hardened ring gear\n\n**Features:**\n- Quick-change master bushing\n- Integrated rotary lock\n- Automatic lubrication\n- Temperature monitoring",
        'unit_price' => 165000.00,
        'features' => ['49.5" opening', '3000 HP rated', 'Quick-change bushing', 'Auto lubrication', 'Heavy-duty bearings'],
        'specs' => [
            'Table Opening' => '49.5 inches / 1,257 mm',
            'Static Load' => '1,000 tons',
            'Max Torque' => '100,000 ft-lbs',
            'Speed Range' => '0-250 RPM',
            'Weight' => '18,000 kg',
        ],
        'weight_kg' => 18000,
        'warranty_months' => 24,
        'lead_time_days' => 90,
        'moq' => 1,
    ],
    
    // Pipeline Components ($50K - $200K)
    [
        'sku' => 'PIP-VALVE-36',
        'category' => 'pipeline-components',
        'name' => 'Trunnion Ball Valve 36" Class 600',
        'short_desc' => '36-inch trunnion-mounted ball valve, ANSI Class 600, with gear operator and fire-safe design.',
        'description' => 'Full-bore trunnion ball valve designed for high-pressure gas transmission pipelines. Features fire-safe design per API 607 and fugitive emission certification.',
        'long_description' => "This 36-inch trunnion ball valve is engineered for critical pipeline applications where reliability and safety are paramount.\n\n**Design Features:**\n- Full-bore design (no flow restriction)\n- Trunnion-mounted ball\n- Double block and bleed capability\n- Anti-static device\n- Fire-safe certified (API 607)\n\n**Materials:**\n- Body: ASTM A352 LCC\n- Ball: ASTM A182 F316 + ENP\n- Seats: PEEK with metal backup\n- Stem: 17-4 PH stainless steel\n\n**Operator:**\n- Gear operator standard\n- Actuator-ready mounting\n- Position indicator\n- Locking device",
        'unit_price' => 178000.00,
        'features' => ['36" full bore', 'Class 600', 'Fire-safe API 607', 'DBB capability', 'Fugitive emission certified'],
        'specs' => [
            'Size' => '36 inches / 900 mm',
            'Pressure Class' => 'ANSI 600 / PN100',
            'Design Temp' => '-46°C to +200°C',
            'Body Material' => 'ASTM A352 LCC',
            'End Connection' => 'RTJ Flanged',
            'Weight' => '12,500 kg',
        ],
        'weight_kg' => 12500,
        'warranty_months' => 36,
        'lead_time_days' => 120,
        'moq' => 1,
    ],
    [
        'sku' => 'PIP-LAUNCH-48',
        'category' => 'pipeline-components',
        'name' => 'Pipeline Pig Launcher 48"',
        'short_desc' => '48-inch pig launcher with quick-opening closure and full instrumentation package.',
        'description' => 'Complete pig launching system for 48-inch pipelines, featuring quick-opening closure, kicker line, and integrated pressure/temperature instrumentation.',
        'long_description' => "Our 48-inch pig launcher is designed for efficient pipeline maintenance operations with maximum safety features.\n\n**Closure System:**\n- Quick-opening door (davit arm)\n- Pressure-locking mechanism\n- Safety interlock system\n- Visual position indicator\n\n**Barrel Features:**\n- Oversized barrel (1.5D length)\n- Reducer and transition piece\n- Kicker line connection\n- Drain and vent connections\n\n**Instrumentation:**\n- Pressure transmitter\n- Temperature indicator\n- Pig passage indicator\n- Safety relief valve",
        'unit_price' => 145000.00,
        'features' => ['48" diameter', 'Quick-opening closure', 'Safety interlock', 'Full instrumentation', 'ASME certified'],
        'specs' => [
            'Pipeline Size' => '48 inches',
            'Barrel Length' => '72 inches (1.5D)',
            'Design Pressure' => '1,480 PSI / 102 bar',
            'Design Temp' => '-29°C to +120°C',
            'Material' => 'ASTM A516 Gr.70',
            'Weight' => '8,200 kg',
        ],
        'weight_kg' => 8200,
        'warranty_months' => 24,
        'lead_time_days' => 90,
        'moq' => 1,
    ],
    
    // Compressors ($150K - $350K)
    [
        'sku' => 'CMP-SCREW-500',
        'category' => 'compressors',
        'name' => 'Oil-Injected Screw Compressor 500 HP',
        'short_desc' => '500 HP rotary screw compressor package with integrated dryer and filtration system.',
        'description' => 'Complete compressor package featuring twin-screw rotors, variable speed drive, and comprehensive air treatment system for instrument-grade air quality.',
        'long_description' => "The CMP-SCREW-500 delivers reliable compressed air for demanding industrial applications with exceptional energy efficiency.\n\n**Compressor Element:**\n- Asymmetric rotor profile\n- Premium synthetic lubricant\n- Minimum 100,000-hour bearing life\n- Low noise operation (<75 dBA)\n\n**Drive System:**\n- 500 HP premium efficiency motor\n- Variable frequency drive\n- Soft-start capability\n- Power factor correction\n\n**Air Treatment:**\n- Refrigerated air dryer\n- Coalescing filters (0.01 micron)\n- Activated carbon filter\n- Automatic condensate drain",
        'unit_price' => 189000.00,
        'features' => ['500 HP VFD', 'Integrated dryer', 'Low noise', 'Energy efficient', 'Remote monitoring'],
        'specs' => [
            'Motor Power' => '500 HP / 373 kW',
            'Free Air Delivery' => '2,500 CFM / 4,250 m³/h',
            'Discharge Pressure' => '150 PSI / 10.3 bar',
            'Noise Level' => '< 75 dBA',
            'Dimensions' => '4.5m x 2.2m x 2.5m',
            'Weight' => '9,500 kg',
        ],
        'weight_kg' => 9500,
        'warranty_months' => 24,
        'lead_time_days' => 60,
        'moq' => 1,
    ],
    [
        'sku' => 'CMP-RECIP-2000',
        'category' => 'compressors',
        'name' => 'Reciprocating Gas Compressor 2000 HP',
        'short_desc' => '2000 HP reciprocating compressor for natural gas boosting and processing applications.',
        'description' => 'Heavy-duty reciprocating compressor designed for continuous operation in gas processing plants and pipeline compression stations.',
        'long_description' => "The CMP-RECIP-2000 is engineered for the demanding requirements of natural gas compression with exceptional reliability and efficiency.\n\n**Frame Design:**\n- Cast iron frame with integral oil reservoir\n- Forged steel crankshaft\n- Precision crosshead guides\n- Force-feed lubrication\n\n**Cylinder Features:**\n- Multiple stage compression\n- PTFE piston rings (non-lube option)\n- Automatic valve unloaders\n- Pulsation dampeners\n\n**Control System:**\n- PLC-based control\n- Capacity control (0-100%)\n- Vibration monitoring\n- Remote SCADA interface",
        'unit_price' => 345000.00,
        'features' => ['2000 HP', 'Multi-stage', 'API 618 compliant', 'SCADA ready', 'Low emissions'],
        'specs' => [
            'Power Rating' => '2,000 HP / 1,492 kW',
            'Suction Pressure' => '50-500 PSI',
            'Discharge Pressure' => 'Up to 3,000 PSI',
            'Capacity' => '50 MMSCFD',
            'Stages' => '2-4 (application dependent)',
            'Weight' => '65,000 kg',
        ],
        'weight_kg' => 65000,
        'warranty_months' => 24,
        'lead_time_days' => 150,
        'moq' => 1,
    ],
    
    // Pumping Systems ($80K - $250K)
    [
        'sku' => 'PMP-CENT-1500',
        'category' => 'pumping-systems',
        'name' => 'API 610 Centrifugal Pump BB3',
        'short_desc' => 'Between-bearings, axially split centrifugal pump for refinery and petrochemical service.',
        'description' => 'Heavy-duty API 610 BB3 pump designed for high-temperature hydrocarbon service with exceptional reliability and maintainability.',
        'long_description' => "Our API 610 BB3 pump is the industry standard for critical refinery and petrochemical applications.\n\n**Hydraulic Design:**\n- Double-suction impeller\n- Axially split casing\n- Back-to-back mechanical seals\n- Optimized efficiency (>85%)\n\n**Construction:**\n- Carbon steel or stainless casing\n- Forged impeller\n- Heavy-duty bearings\n- API Plan 53B seal support\n\n**Features:**\n- Centerline mounting\n- Thermal growth compensation\n- Easy maintenance access\n- Condition monitoring ready",
        'unit_price' => 125000.00,
        'features' => ['API 610 11th Ed', 'BB3 configuration', 'High temperature', 'Double mechanical seal', 'Condition monitoring'],
        'specs' => [
            'Flow Rate' => 'Up to 8,000 GPM',
            'Head' => 'Up to 800 feet',
            'Temperature' => '-40°C to +400°C',
            'Pressure' => 'Up to 600 PSI',
            'Driver' => 'Up to 1,500 HP',
            'Weight' => '5,500 kg',
        ],
        'weight_kg' => 5500,
        'warranty_months' => 24,
        'lead_time_days' => 90,
        'moq' => 1,
    ],
    [
        'sku' => 'PMP-PROG-500',
        'category' => 'pumping-systems',
        'name' => 'Progressive Cavity Pump System',
        'short_desc' => 'Complete progressive cavity pump system for high-viscosity and abrasive fluid handling.',
        'description' => 'Engineered pump system for challenging applications including crude oil, drilling mud, and slurries with high solids content.',
        'long_description' => "The PMP-PROG-500 system is designed for the most demanding pumping applications where conventional pumps fail.\n\n**Pump Features:**\n- Single-screw progressive cavity design\n- Hardened rotor (chrome plating)\n- Elastomer stator (multiple compounds)\n- Adjustable packing gland\n\n**Drive Options:**\n- Direct drive\n- Belt drive\n- VFD control\n- Hydraulic drive\n\n**Applications:**\n- Heavy crude oil\n- Drilling mud\n- Produced water\n- Food processing",
        'unit_price' => 78000.00,
        'features' => ['High viscosity', 'Abrasive service', 'Self-priming', 'Reversible', 'Low shear'],
        'specs' => [
            'Flow Rate' => 'Up to 2,000 GPM',
            'Pressure' => 'Up to 600 PSI',
            'Viscosity' => 'Up to 1,000,000 cP',
            'Solids' => 'Up to 60%',
            'Temperature' => '-20°C to +150°C',
            'Weight' => '2,800 kg',
        ],
        'weight_kg' => 2800,
        'warranty_months' => 18,
        'lead_time_days' => 45,
        'moq' => 1,
    ],
    
    // Safety Equipment ($100K - $300K)
    [
        'sku' => 'SAF-BOP-15K',
        'category' => 'safety-equipment',
        'name' => 'Annular BOP 13-5/8" 15K',
        'short_desc' => '13-5/8 inch annular blowout preventer rated for 15,000 PSI working pressure.',
        'description' => 'Premium annular BOP with spherical packing element for superior sealing on all tubular sizes and open hole.',
        'long_description' => "The SAF-BOP-15K provides critical well control capability for high-pressure drilling operations.\n\n**Design Features:**\n- Spherical packing element\n- Forged alloy steel body\n- Quick-change element design\n- Hydraulic operating system\n\n**Sealing Capability:**\n- Full range of pipe sizes\n- Open hole (shear/seal)\n- Wireline and coiled tubing\n- Kelly and drill pipe\n\n**Certifications:**\n- API 16A certified\n- DNV-GL approved\n- ABS type approved\n- NORSOK compliant",
        'unit_price' => 245000.00,
        'features' => ['15,000 PSI', '13-5/8" bore', 'API 16A', 'Quick-change element', 'Full pipe range'],
        'specs' => [
            'Bore Size' => '13-5/8 inches',
            'Working Pressure' => '15,000 PSI',
            'Test Pressure' => '22,500 PSI',
            'Pipe Range' => '2-3/8" to 13-3/8"',
            'Operating Pressure' => '1,500-3,000 PSI',
            'Weight' => '15,000 kg',
        ],
        'weight_kg' => 15000,
        'warranty_months' => 24,
        'lead_time_days' => 120,
        'moq' => 1,
    ],
    [
        'sku' => 'SAF-RAMS-15K',
        'category' => 'safety-equipment',
        'name' => 'Ram BOP Stack 13-5/8" 15K Triple',
        'short_desc' => 'Triple ram BOP stack with blind/shear, pipe, and variable bore rams.',
        'description' => 'Complete ram BOP stack assembly for critical well control applications, featuring quick-change bonnets and integrated choke/kill lines.',
        'long_description' => "Our triple ram BOP stack provides comprehensive well control capability for demanding drilling operations.\n\n**Ram Configuration:**\n- Upper: Blind/Shear rams\n- Middle: Variable bore rams\n- Lower: Pipe rams\n\n**Features:**\n- Quick-change bonnets\n- Integrated choke/kill outlets\n- Hydraulic locking system\n- Position indicators\n\n**Construction:**\n- Forged alloy steel body\n- Inlay-clad sealing surfaces\n- Premium elastomers\n- Corrosion-resistant coating",
        'unit_price' => 385000.00,
        'features' => ['15,000 PSI', 'Triple ram', 'Blind/shear capability', 'Quick-change', 'API 16A'],
        'specs' => [
            'Bore Size' => '13-5/8 inches',
            'Working Pressure' => '15,000 PSI',
            'Ram Types' => 'BSR, VBR, Pipe',
            'Choke/Kill' => '4-1/16" 15K',
            'Height' => '3.8 m',
            'Weight' => '42,000 kg',
        ],
        'weight_kg' => 42000,
        'warranty_months' => 24,
        'lead_time_days' => 150,
        'moq' => 1,
    ],
    
    // Instrumentation ($50K - $150K)
    [
        'sku' => 'INS-FLOW-12',
        'category' => 'instrumentation',
        'name' => 'Ultrasonic Flow Meter 12" Custody Transfer',
        'short_desc' => '12-inch multi-path ultrasonic flow meter for fiscal metering applications.',
        'description' => 'High-accuracy custody transfer flow meter with 4-path ultrasonic measurement and integrated flow computer.',
        'long_description' => "The INS-FLOW-12 delivers exceptional accuracy for fiscal metering and custody transfer applications.\n\n**Measurement Technology:**\n- 4-path ultrasonic design\n- Chordal integration\n- Self-diagnostic capability\n- Bi-directional measurement\n\n**Accuracy:**\n- ±0.1% of reading (calibrated)\n- ±0.15% of reading (uncalibrated)\n- Repeatability: ±0.02%\n- Turndown: 100:1\n\n**Flow Computer:**\n- Integrated electronics\n- AGA/API calculations\n- Data logging\n- Multiple communication protocols",
        'unit_price' => 95000.00,
        'features' => ['Custody transfer', '4-path ultrasonic', '±0.1% accuracy', 'Bi-directional', 'Integrated computer'],
        'specs' => [
            'Size' => '12 inches / 300 mm',
            'Accuracy' => '±0.1% (calibrated)',
            'Pressure Rating' => 'ANSI 600',
            'Temperature' => '-40°C to +200°C',
            'Output' => '4-20mA, Modbus, HART',
            'Weight' => '850 kg',
        ],
        'weight_kg' => 850,
        'warranty_months' => 36,
        'lead_time_days' => 60,
        'moq' => 1,
    ],
    [
        'sku' => 'INS-CTRL-DCS',
        'category' => 'instrumentation',
        'name' => 'Distributed Control System Package',
        'short_desc' => 'Complete DCS package for process control with redundant controllers and operator stations.',
        'description' => 'Turnkey distributed control system including redundant controllers, I/O modules, operator workstations, and engineering software.',
        'long_description' => "Our DCS package provides comprehensive process control capability for oil & gas and petrochemical facilities.\n\n**Controller Features:**\n- Redundant CPU modules\n- Hot-swappable I/O\n- Deterministic scan times\n- IEC 61131-3 programming\n\n**I/O Capacity:**\n- 500 analog inputs\n- 200 analog outputs\n- 1,000 digital I/O\n- HART communication\n\n**Operator Interface:**\n- Dual redundant servers\n- 4 operator workstations\n- Large-screen displays\n- Alarm management system",
        'unit_price' => 275000.00,
        'features' => ['Redundant controllers', 'Hot-swap I/O', 'Alarm management', 'Historian included', 'Cybersecurity'],
        'specs' => [
            'I/O Points' => '1,700+ points',
            'Controllers' => '2 (redundant)',
            'Workstations' => '4 operator + 1 engineering',
            'Scan Time' => '100 ms (configurable)',
            'Communication' => 'Ethernet, Modbus, HART',
            'Certification' => 'SIL 2 capable',
        ],
        'weight_kg' => 500,
        'warranty_months' => 24,
        'lead_time_days' => 90,
        'moq' => 1,
    ],
    
    // Spare Parts (High-value kits $50K - $100K)
    [
        'sku' => 'SPR-MUD-KIT',
        'category' => 'spare-parts',
        'name' => 'Mud Pump Major Overhaul Kit',
        'short_desc' => 'Complete major overhaul kit for triplex mud pump including fluid end and power end components.',
        'description' => 'Comprehensive spare parts kit for complete mud pump overhaul, including all wear components, seals, bearings, and gaskets.',
        'long_description' => "This major overhaul kit contains all components needed for a complete mud pump rebuild.\n\n**Fluid End Components:**\n- 3 x Bi-metal liners\n- 6 x Valve assemblies\n- 6 x Valve seats\n- 3 x Piston assemblies\n- Complete seal kit\n\n**Power End Components:**\n- Crosshead pins and bushings\n- Wrist pin bearings\n- Connecting rod bearings\n- Main bearings\n- Oil seals and gaskets\n\n**Additional Items:**\n- Pulsation dampener bladders\n- Relief valve kit\n- Lubrication system filters",
        'unit_price' => 67500.00,
        'features' => ['Complete overhaul', 'OEM quality', 'All wear parts', 'Detailed documentation', 'Technical support'],
        'specs' => [
            'Application' => 'Triplex Mud Pump 2500 HP',
            'Components' => '150+ individual parts',
            'Liner Sizes' => '6", 6.5", 7"',
            'Packaging' => 'Wooden crates',
            'Documentation' => 'Assembly manual included',
            'Shelf Life' => '5 years (sealed)',
        ],
        'weight_kg' => 2500,
        'warranty_months' => 12,
        'lead_time_days' => 30,
        'moq' => 1,
    ],
    [
        'sku' => 'SPR-BOP-KIT',
        'category' => 'spare-parts',
        'name' => 'BOP Stack Annual Service Kit',
        'short_desc' => 'Annual service kit for 13-5/8" 15K BOP stack including all elastomers and wear components.',
        'description' => 'Complete annual service kit for BOP stack maintenance, containing all seals, packings, and wear components per API 53 requirements.',
        'long_description' => "This comprehensive service kit ensures your BOP stack maintains peak performance and regulatory compliance.\n\n**Annular BOP Components:**\n- Packing element\n- Top seal assembly\n- Operating piston seals\n- Wear bushing\n\n**Ram BOP Components:**\n- Ram packer assemblies (all types)\n- Bonnet seals\n- Operating piston seals\n- Door seals\n\n**Accessories:**\n- Choke/kill valve seals\n- Hydraulic connector seals\n- Test plug assemblies\n- Lubricants and compounds",
        'unit_price' => 89000.00,
        'features' => ['API 53 compliant', 'All elastomers', 'OEM specification', 'Traceability', 'Certified materials'],
        'specs' => [
            'Application' => '13-5/8" 15K BOP Stack',
            'Components' => '200+ individual parts',
            'Elastomer Grades' => 'Nitrile, Viton, HNBR',
            'Certification' => 'API 16A materials',
            'Packaging' => 'Organized kit boxes',
            'Documentation' => 'Installation guide included',
        ],
        'weight_kg' => 450,
        'warranty_months' => 24,
        'lead_time_days' => 21,
        'moq' => 1,
    ],
    
    // Additional high-value items
    [
        'sku' => 'DRL-IRON-SET',
        'category' => 'drilling-equipment',
        'name' => 'Iron Roughneck Complete System',
        'short_desc' => 'Automated pipe handling system with spinning wrench and torque wrench capabilities.',
        'description' => 'Complete iron roughneck system for automated drill pipe makeup and breakout, featuring advanced torque control and safety interlocks.',
        'long_description' => "The Iron Roughneck system revolutionizes pipe handling with automated, hands-free operation.\n\n**Spinning Wrench:**\n- Hydraulic roller drive\n- Bi-directional operation\n- Adjustable speed control\n- Pipe size range: 3-1/2\" to 8\"\n\n**Torque Wrench:**\n- Jaw-type gripping\n- Torque range: 5,000-100,000 ft-lbs\n- Digital torque display\n- Automatic torque control\n\n**Safety Features:**\n- Hands-free zone sensors\n- Emergency stop system\n- Interlock with driller's console\n- Visual and audible alarms",
        'unit_price' => 225000.00,
        'features' => ['Automated operation', '100,000 ft-lb torque', 'Safety interlocks', 'Digital control', 'All pipe sizes'],
        'specs' => [
            'Pipe Range' => '3-1/2" to 8"',
            'Makeup Torque' => '5,000-100,000 ft-lbs',
            'Breakout Torque' => '120,000 ft-lbs',
            'Spinning Speed' => '0-150 RPM',
            'Hydraulic Pressure' => '3,000 PSI',
            'Weight' => '8,500 kg',
        ],
        'weight_kg' => 8500,
        'warranty_months' => 24,
        'lead_time_days' => 90,
        'moq' => 1,
    ],
    [
        'sku' => 'PIP-CLAMP-48',
        'category' => 'pipeline-components',
        'name' => 'Pipeline Repair Clamp System 48"',
        'short_desc' => 'Emergency pipeline repair clamp system for 48-inch pipelines with full encirclement design.',
        'description' => 'Heavy-duty pipeline repair clamp for emergency repairs and permanent reinforcement of damaged pipeline sections.',
        'long_description' => "Our 48-inch repair clamp system provides rapid response capability for pipeline emergencies.\n\n**Design Features:**\n- Full encirclement design\n- Split-sleeve construction\n- Epoxy injection ports\n- Pressure-containing capability\n\n**Applications:**\n- External corrosion repair\n- Dent reinforcement\n- Leak containment\n- Structural reinforcement\n\n**Installation:**\n- No hot work required\n- Minimal pipeline preparation\n- Bolted assembly\n- Field-installable",
        'unit_price' => 52000.00,
        'features' => ['48" diameter', 'No hot work', 'Pressure containing', 'Quick installation', 'ASME B31.8'],
        'specs' => [
            'Pipeline Size' => '48 inches OD',
            'Length' => '24 inches standard',
            'Pressure Rating' => '1,480 PSI',
            'Material' => 'Carbon steel (ASTM A516 Gr.70)',
            'Bolting' => 'ASTM A193 B7',
            'Weight' => '1,800 kg',
        ],
        'weight_kg' => 1800,
        'warranty_months' => 60,
        'lead_time_days' => 14,
        'moq' => 1,
    ],
];

// Insert products
$stmtProduct = $pdo->prepare(
    'INSERT INTO products (sku, category_id, name, short_desc, description, long_description, unit_price, 
     features, specifications, weight_kg, warranty_months, lead_time_days, moq, manufacturer, is_featured, is_active)
     VALUES (:sku, :category_id, :name, :short_desc, :description, :long_description, :unit_price,
     :features, :specifications, :weight_kg, :warranty_months, :lead_time_days, :moq, :manufacturer, :is_featured, :is_active)
     ON DUPLICATE KEY UPDATE 
     name = VALUES(name), 
     unit_price = VALUES(unit_price),
     short_desc = VALUES(short_desc),
     description = VALUES(description),
     long_description = VALUES(long_description)'
);

$count = 0;
foreach ($products as $p) {
    $categoryId = $categories[$p['category']] ?? null;
    
    $stmtProduct->execute([
        'sku' => $p['sku'],
        'category_id' => $categoryId,
        'name' => $p['name'],
        'short_desc' => $p['short_desc'],
        'description' => $p['description'],
        'long_description' => $p['long_description'],
        'unit_price' => $p['unit_price'],
        'features' => json_encode($p['features']),
        'specifications' => json_encode($p['specs']),
        'weight_kg' => $p['weight_kg'],
        'warranty_months' => $p['warranty_months'],
        'lead_time_days' => $p['lead_time_days'],
        'moq' => $p['moq'],
        'manufacturer' => 'Gordon Food Service',
        'is_featured' => $count < 6 ? 1 : 0,  // First 6 are featured
        'is_active' => 1,
    ]);
    
    $count++;
    $priceFormatted = number_format($p['unit_price'], 2);
    echo "  [$count/20] {$p['sku']} - {$p['name']} - \${$priceFormatted}\n";
}

echo "\n✅ Successfully seeded {$count} high-value industrial products!\n";
echo "\nPrice range: \$52,000 - \$395,000\n";
echo "Categories: " . count($categories) . "\n";
echo "\nView products at: http://localhost:8000/catalog\n";
