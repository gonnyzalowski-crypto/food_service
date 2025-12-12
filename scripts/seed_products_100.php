<?php
/**
 * Seed 100 high-value industrial products with images
 */

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? '127.0.0.1',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_NAME'] ?? 'streicher'
);

$pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "Seeding 100 high-value industrial products with images...\n\n";

// Categories with their IDs
$categories = [
    'hydraulic-systems' => ['id' => 1, 'name' => 'Hydraulic Systems'],
    'drilling-equipment' => ['id' => 2, 'name' => 'Drilling Equipment'],
    'pipeline-components' => ['id' => 3, 'name' => 'Pipeline Components'],
    'compressors' => ['id' => 4, 'name' => 'Compressors'],
    'pumping-systems' => ['id' => 5, 'name' => 'Pumping Systems'],
    'safety-equipment' => ['id' => 6, 'name' => 'Safety Equipment'],
    'instrumentation' => ['id' => 7, 'name' => 'Instrumentation'],
    'spare-parts' => ['id' => 8, 'name' => 'Spare Parts'],
];

// Unsplash image URLs for industrial equipment (using specific oil & gas related images)
$imageUrls = [
    'hydraulic' => [
        'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800',
        'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800',
        'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800',
    ],
    'drilling' => [
        'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800',
        'https://images.unsplash.com/photo-1482049016gy-2d3d8a1f7f3f?w=800',
        'https://images.unsplash.com/photo-1513828583688-c52646db42da?w=800',
        'https://images.unsplash.com/photo-1545259741-2ea3ebf61fa3?w=800',
    ],
    'pipeline' => [
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
        'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=800',
        'https://images.unsplash.com/photo-1611273426858-450d8e3c9fce?w=800',
        'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?w=800',
    ],
    'compressor' => [
        'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
        'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=800',
        'https://images.unsplash.com/photo-1590959651373-a3db0f38a961?w=800',
    ],
    'pump' => [
        'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=800',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800',
        'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800',
    ],
    'safety' => [
        'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
        'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800',
        'https://images.unsplash.com/photo-1513828583688-c52646db42da?w=800',
    ],
    'instrument' => [
        'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800',
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800',
    ],
    'spare' => [
        'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=800',
        'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800',
        'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
    ],
];

// 100 Products organized by category
$products = [
    // HYDRAULIC SYSTEMS (15 products)
    ['sku' => 'HYD-PWR-5000', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Power Unit 5000 HP', 'price' => 127500, 'image_type' => 'hydraulic',
     'short_desc' => 'High-capacity hydraulic power unit for offshore drilling operations',
     'features' => ['5000 HP motor capacity', 'Variable displacement pumps', 'Integrated cooling system', 'Remote monitoring', 'API 16C certified']],
    
    ['sku' => 'HYD-CYL-800T', 'category' => 'hydraulic-systems', 'name' => 'Heavy-Duty Hydraulic Cylinder 800-Ton', 'price' => 89000, 'image_type' => 'hydraulic',
     'short_desc' => 'Industrial hydraulic cylinder for heavy lifting applications',
     'features' => ['800-ton capacity', 'Chrome-plated rod', 'High-pressure seals', 'Corrosion resistant']],
    
    ['sku' => 'HYD-VALVE-4W', 'category' => 'hydraulic-systems', 'name' => 'Directional Control Valve 4-Way', 'price' => 45000, 'image_type' => 'hydraulic',
     'short_desc' => 'Precision 4-way directional control valve for hydraulic systems',
     'features' => ['4-way operation', 'Solenoid actuated', 'High flow capacity', 'Low pressure drop']],
    
    ['sku' => 'HYD-ACC-500L', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Accumulator 500L', 'price' => 67000, 'image_type' => 'hydraulic',
     'short_desc' => 'Bladder-type hydraulic accumulator for energy storage',
     'features' => ['500L capacity', 'Bladder type', '350 bar rating', 'Quick response']],
    
    ['sku' => 'HYD-COOL-1000', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Oil Cooler 1000kW', 'price' => 52000, 'image_type' => 'hydraulic',
     'short_desc' => 'Industrial oil cooler for hydraulic system temperature control',
     'features' => ['1000kW cooling capacity', 'Air-cooled design', 'Low noise operation', 'Easy maintenance']],
    
    ['sku' => 'HYD-FILT-HP', 'category' => 'hydraulic-systems', 'name' => 'High-Pressure Filter Assembly', 'price' => 28000, 'image_type' => 'hydraulic',
     'short_desc' => 'Inline high-pressure hydraulic filter for system protection',
     'features' => ['500 bar rating', '3-micron filtration', 'Bypass indicator', 'Quick-change element']],
    
    ['sku' => 'HYD-MOTOR-750', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Motor 750cc', 'price' => 38000, 'image_type' => 'hydraulic',
     'short_desc' => 'Axial piston hydraulic motor for high-torque applications',
     'features' => ['750cc displacement', 'Axial piston design', 'High efficiency', 'Reversible rotation']],
    
    ['sku' => 'HYD-PUMP-VAR', 'category' => 'hydraulic-systems', 'name' => 'Variable Displacement Pump 500cc', 'price' => 72000, 'image_type' => 'hydraulic',
     'short_desc' => 'Variable displacement axial piston pump for precise control',
     'features' => ['500cc max displacement', 'Pressure compensated', 'Load sensing', 'High efficiency']],
    
    ['sku' => 'HYD-MANIFOLD', 'category' => 'hydraulic-systems', 'name' => 'Custom Hydraulic Manifold Block', 'price' => 35000, 'image_type' => 'hydraulic',
     'short_desc' => 'Precision-machined hydraulic manifold for complex systems',
     'features' => ['Custom design', 'Aluminum or steel', 'Integrated valves', 'Compact design']],
    
    ['sku' => 'HYD-HOSE-SET', 'category' => 'hydraulic-systems', 'name' => 'High-Pressure Hose Assembly Kit', 'price' => 18500, 'image_type' => 'hydraulic',
     'short_desc' => 'Complete high-pressure hydraulic hose kit with fittings',
     'features' => ['700 bar rating', 'Spiral reinforced', 'Various lengths', 'Quick-connect fittings']],
    
    ['sku' => 'HYD-TANK-2000', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Reservoir Tank 2000L', 'price' => 42000, 'image_type' => 'hydraulic',
     'short_desc' => 'Large capacity hydraulic oil reservoir with accessories',
     'features' => ['2000L capacity', 'Baffled design', 'Level indicators', 'Breather filter']],
    
    ['sku' => 'HYD-SERVO-CTL', 'category' => 'hydraulic-systems', 'name' => 'Servo Hydraulic Control System', 'price' => 95000, 'image_type' => 'hydraulic',
     'short_desc' => 'Precision servo-hydraulic control for automated operations',
     'features' => ['Closed-loop control', 'High accuracy', 'Fast response', 'PLC integration']],
    
    ['sku' => 'HYD-TEST-BENCH', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Test Bench 500 Bar', 'price' => 115000, 'image_type' => 'hydraulic',
     'short_desc' => 'Complete hydraulic component testing station',
     'features' => ['500 bar capacity', 'Flow measurement', 'Data logging', 'Multiple test ports']],
    
    ['sku' => 'HYD-INTENSIF', 'category' => 'hydraulic-systems', 'name' => 'Pressure Intensifier 1:10', 'price' => 55000, 'image_type' => 'hydraulic',
     'short_desc' => 'Hydraulic pressure intensifier for ultra-high pressure',
     'features' => ['1:10 ratio', '3500 bar output', 'Continuous duty', 'Low maintenance']],
    
    ['sku' => 'HYD-CLAMP-SYS', 'category' => 'hydraulic-systems', 'name' => 'Hydraulic Clamping System', 'price' => 48000, 'image_type' => 'hydraulic',
     'short_desc' => 'Automated hydraulic workholding system',
     'features' => ['Quick clamp/release', 'Uniform pressure', 'Safety interlocks', 'Modular design']],

    // DRILLING EQUIPMENT (15 products)
    ['sku' => 'DRL-MUD-2500', 'category' => 'drilling-equipment', 'name' => 'Triplex Mud Pump 2500 HP', 'price' => 285000, 'image_type' => 'drilling',
     'short_desc' => 'Heavy-duty triplex mud pump for deep drilling operations',
     'features' => ['2500 HP rating', 'Triplex design', '7500 PSI max', 'Fluid end liners']],
    
    ['sku' => 'DRL-TDS-750', 'category' => 'drilling-equipment', 'name' => 'Top Drive System 750-Ton', 'price' => 395000, 'image_type' => 'drilling',
     'short_desc' => 'Advanced top drive drilling system for efficient operations',
     'features' => ['750-ton capacity', 'AC motor drive', 'Torque monitoring', 'Pipe handling']],
    
    ['sku' => 'DRL-ROT-3000', 'category' => 'drilling-equipment', 'name' => 'Rotary Table 49.5" 3000 HP', 'price' => 165000, 'image_type' => 'drilling',
     'short_desc' => 'Heavy-duty rotary table for drilling rig operations',
     'features' => ['49.5" opening', '3000 HP capacity', 'Helical gears', 'Oil bath lubrication']],
    
    ['sku' => 'DRL-DRAWWRK', 'category' => 'drilling-equipment', 'name' => 'Drawworks 3000 HP', 'price' => 450000, 'image_type' => 'drilling',
     'short_desc' => 'Main hoisting system for drilling rig operations',
     'features' => ['3000 HP motors', 'Disc brakes', 'Crown-o-matic', 'Auto driller ready']],
    
    ['sku' => 'DRL-SWIVEL-500', 'category' => 'drilling-equipment', 'name' => 'Drilling Swivel 500-Ton', 'price' => 125000, 'image_type' => 'drilling',
     'short_desc' => 'Heavy-duty swivel for rotary drilling operations',
     'features' => ['500-ton capacity', 'Sealed bearings', 'Wash pipe assembly', 'API certified']],
    
    ['sku' => 'DRL-KELLY-HEX', 'category' => 'drilling-equipment', 'name' => 'Hexagonal Kelly 5.25"', 'price' => 85000, 'image_type' => 'drilling',
     'short_desc' => 'Hexagonal kelly for rotary drilling power transmission',
     'features' => ['5.25" hex', '40ft length', 'Upset ends', 'Heat treated']],
    
    ['sku' => 'DRL-SLIP-ROT', 'category' => 'drilling-equipment', 'name' => 'Rotary Slips 500-Ton', 'price' => 45000, 'image_type' => 'drilling',
     'short_desc' => 'Heavy-duty rotary slips for pipe handling',
     'features' => ['500-ton capacity', 'Quick release', 'Replaceable dies', 'Safety lock']],
    
    ['sku' => 'DRL-ELEVATOR', 'category' => 'drilling-equipment', 'name' => 'Drill Pipe Elevator 500-Ton', 'price' => 68000, 'image_type' => 'drilling',
     'short_desc' => 'Center-latch elevator for drill pipe handling',
     'features' => ['500-ton rating', 'Center latch', 'Quick connect', 'Wear resistant']],
    
    ['sku' => 'DRL-TONG-PWR', 'category' => 'drilling-equipment', 'name' => 'Power Tong 150K ft-lb', 'price' => 175000, 'image_type' => 'drilling',
     'short_desc' => 'Hydraulic power tong for pipe makeup/breakout',
     'features' => ['150,000 ft-lb torque', 'Hydraulic drive', 'Torque gauge', 'Backup system']],
    
    ['sku' => 'DRL-IRON-SET', 'category' => 'drilling-equipment', 'name' => 'Iron Roughneck Complete', 'price' => 225000, 'image_type' => 'drilling',
     'short_desc' => 'Automated pipe handling and makeup system',
     'features' => ['Automated makeup', 'Torque control', 'Spinner/torque', 'Remote operation']],
    
    ['sku' => 'DRL-SHAKER-4', 'category' => 'drilling-equipment', 'name' => 'Shale Shaker 4-Panel', 'price' => 95000, 'image_type' => 'drilling',
     'short_desc' => 'Linear motion shale shaker for solids control',
     'features' => ['4-panel design', 'Linear motion', 'Adjustable angle', 'Quick screen change']],
    
    ['sku' => 'DRL-DEGASSER', 'category' => 'drilling-equipment', 'name' => 'Vacuum Degasser 1500 GPM', 'price' => 135000, 'image_type' => 'drilling',
     'short_desc' => 'Vacuum degasser for drilling fluid treatment',
     'features' => ['1500 GPM capacity', 'Vacuum operation', 'Self-priming', 'Gas venting']],
    
    ['sku' => 'DRL-CENTRI-14', 'category' => 'drilling-equipment', 'name' => 'Decanting Centrifuge 14"', 'price' => 185000, 'image_type' => 'drilling',
     'short_desc' => 'High-speed centrifuge for barite recovery',
     'features' => ['14" bowl', '3200 RPM', 'Variable speed', 'Tungsten carbide']],
    
    ['sku' => 'DRL-DESANDER', 'category' => 'drilling-equipment', 'name' => 'Hydrocyclone Desander 12"', 'price' => 55000, 'image_type' => 'drilling',
     'short_desc' => 'Hydrocyclone desander for solids removal',
     'features' => ['12" cones', '500 GPM each', 'Polyurethane lined', 'Manifold included']],
    
    ['sku' => 'DRL-MUD-MIX', 'category' => 'drilling-equipment', 'name' => 'Mud Mixing System Complete', 'price' => 145000, 'image_type' => 'drilling',
     'short_desc' => 'Complete mud mixing and storage system',
     'features' => ['Hopper/gun system', 'Agitators', 'Transfer pumps', 'Tank farm']],

    // PIPELINE COMPONENTS (12 products)
    ['sku' => 'PIP-VALVE-36', 'category' => 'pipeline-components', 'name' => 'Trunnion Ball Valve 36" Class 600', 'price' => 178000, 'image_type' => 'pipeline',
     'short_desc' => 'Large bore trunnion mounted ball valve for pipelines',
     'features' => ['36" bore', 'Class 600', 'Fire-safe design', 'Double block & bleed']],
    
    ['sku' => 'PIP-LAUNCH-48', 'category' => 'pipeline-components', 'name' => 'Pipeline Pig Launcher 48"', 'price' => 145000, 'image_type' => 'pipeline',
     'short_desc' => 'Complete pig launcher assembly for pipeline maintenance',
     'features' => ['48" diameter', 'Quick-open closure', 'Signaler', 'Drain/vent']],
    
    ['sku' => 'PIP-RECV-48', 'category' => 'pipeline-components', 'name' => 'Pipeline Pig Receiver 48"', 'price' => 138000, 'image_type' => 'pipeline',
     'short_desc' => 'Pig receiver trap for pipeline pigging operations',
     'features' => ['48" diameter', 'Quick-open door', 'Drain system', 'Indicator']],
    
    ['sku' => 'PIP-GATE-24', 'category' => 'pipeline-components', 'name' => 'Gate Valve 24" Class 900', 'price' => 95000, 'image_type' => 'pipeline',
     'short_desc' => 'High-pressure gate valve for pipeline isolation',
     'features' => ['24" bore', 'Class 900', 'Through conduit', 'Gear operated']],
    
    ['sku' => 'PIP-CHECK-20', 'category' => 'pipeline-components', 'name' => 'Swing Check Valve 20"', 'price' => 65000, 'image_type' => 'pipeline',
     'short_desc' => 'Swing check valve for backflow prevention',
     'features' => ['20" size', 'Tilting disc', 'Low cracking pressure', 'Flanged ends']],
    
    ['sku' => 'PIP-CLAMP-48', 'category' => 'pipeline-components', 'name' => 'Pipeline Repair Clamp 48"', 'price' => 52000, 'image_type' => 'pipeline',
     'short_desc' => 'Emergency repair clamp for pipeline leaks',
     'features' => ['48" diameter', 'Split design', 'Pressure rated', 'Quick install']],
    
    ['sku' => 'PIP-FLANGE-36', 'category' => 'pipeline-components', 'name' => 'Weld Neck Flange 36" Class 600', 'price' => 28000, 'image_type' => 'pipeline',
     'short_desc' => 'Weld neck flange for high-pressure pipeline connections',
     'features' => ['36" size', 'Class 600', 'RTJ face', 'ASTM A694']],
    
    ['sku' => 'PIP-INSUL-JNT', 'category' => 'pipeline-components', 'name' => 'Insulating Joint 24"', 'price' => 75000, 'image_type' => 'pipeline',
     'short_desc' => 'Monolithic insulating joint for cathodic protection',
     'features' => ['24" size', 'Monolithic design', 'High dielectric', 'Buried service']],
    
    ['sku' => 'PIP-EXPAN-20', 'category' => 'pipeline-components', 'name' => 'Expansion Joint 20"', 'price' => 48000, 'image_type' => 'pipeline',
     'short_desc' => 'Metal bellows expansion joint for thermal movement',
     'features' => ['20" size', 'Bellows design', 'Tie rods', 'Limit stops']],
    
    ['sku' => 'PIP-TEE-36', 'category' => 'pipeline-components', 'name' => 'Barred Tee 36" x 24"', 'price' => 42000, 'image_type' => 'pipeline',
     'short_desc' => 'Barred tee fitting for pipeline branching',
     'features' => ['36" x 24" size', 'Barred design', 'Piggable', 'Weld ends']],
    
    ['sku' => 'PIP-REDUCER-36', 'category' => 'pipeline-components', 'name' => 'Concentric Reducer 36" x 30"', 'price' => 22000, 'image_type' => 'pipeline',
     'short_desc' => 'Concentric reducer for pipeline size transition',
     'features' => ['36" x 30"', 'Seamless', 'Weld ends', 'ASTM A860']],
    
    ['sku' => 'PIP-BEND-36', 'category' => 'pipeline-components', 'name' => 'Induction Bend 36" 3D', 'price' => 38000, 'image_type' => 'pipeline',
     'short_desc' => 'Induction bend for pipeline direction change',
     'features' => ['36" diameter', '3D radius', 'Induction bent', 'Tangent ends']],

    // COMPRESSORS (12 products)
    ['sku' => 'CMP-SCREW-500', 'category' => 'compressors', 'name' => 'Oil-Injected Screw Compressor 500 HP', 'price' => 189000, 'image_type' => 'compressor',
     'short_desc' => 'Rotary screw compressor for continuous duty applications',
     'features' => ['500 HP motor', 'Oil-injected', 'Variable speed', 'Integrated dryer']],
    
    ['sku' => 'CMP-RECIP-2000', 'category' => 'compressors', 'name' => 'Reciprocating Gas Compressor 2000 HP', 'price' => 345000, 'image_type' => 'compressor',
     'short_desc' => 'Heavy-duty reciprocating compressor for gas processing',
     'features' => ['2000 HP rating', '4-stage compression', 'API 618', 'Forced lubrication']],
    
    ['sku' => 'CMP-CENT-5000', 'category' => 'compressors', 'name' => 'Centrifugal Compressor 5000 HP', 'price' => 485000, 'image_type' => 'compressor',
     'short_desc' => 'Multi-stage centrifugal compressor for high volume',
     'features' => ['5000 HP', 'Multi-stage', 'API 617', 'Dry gas seals']],
    
    ['sku' => 'CMP-BOOST-1000', 'category' => 'compressors', 'name' => 'Gas Booster Compressor 1000 HP', 'price' => 225000, 'image_type' => 'compressor',
     'short_desc' => 'Booster compressor for gas lift operations',
     'features' => ['1000 HP', 'Low suction', 'High discharge', 'Skid mounted']],
    
    ['sku' => 'CMP-VRU-500', 'category' => 'compressors', 'name' => 'Vapor Recovery Unit 500 HP', 'price' => 175000, 'image_type' => 'compressor',
     'short_desc' => 'Vapor recovery compressor for emissions control',
     'features' => ['500 HP', 'Low pressure suction', 'Liquid tolerant', 'BTEX recovery']],
    
    ['sku' => 'CMP-AIR-350', 'category' => 'compressors', 'name' => 'Instrument Air Compressor 350 HP', 'price' => 145000, 'image_type' => 'compressor',
     'short_desc' => 'Oil-free air compressor for instrument air supply',
     'features' => ['350 HP', 'Oil-free', 'Class 0', 'Integrated dryer']],
    
    ['sku' => 'CMP-REFRIG', 'category' => 'compressors', 'name' => 'Refrigeration Compressor Package', 'price' => 265000, 'image_type' => 'compressor',
     'short_desc' => 'Complete refrigeration compressor for gas processing',
     'features' => ['Propane service', 'Screw type', 'Oil separator', 'Controls']],
    
    ['sku' => 'CMP-DIAPHRAGM', 'category' => 'compressors', 'name' => 'Diaphragm Compressor 100 HP', 'price' => 125000, 'image_type' => 'compressor',
     'short_desc' => 'Leak-free diaphragm compressor for specialty gases',
     'features' => ['100 HP', 'Zero leakage', 'High purity', 'Metal diaphragm']],
    
    ['sku' => 'CMP-SCROLL-50', 'category' => 'compressors', 'name' => 'Scroll Compressor Package 50 HP', 'price' => 65000, 'image_type' => 'compressor',
     'short_desc' => 'Compact scroll compressor for small applications',
     'features' => ['50 HP', 'Oil-free option', 'Low vibration', 'Quiet operation']],
    
    ['sku' => 'CMP-LOBE-200', 'category' => 'compressors', 'name' => 'Rotary Lobe Blower 200 HP', 'price' => 85000, 'image_type' => 'compressor',
     'short_desc' => 'Positive displacement blower for low pressure',
     'features' => ['200 HP', 'Tri-lobe design', 'Low pressure', 'High volume']],
    
    ['sku' => 'CMP-LIQUID-RING', 'category' => 'compressors', 'name' => 'Liquid Ring Compressor 150 HP', 'price' => 95000, 'image_type' => 'compressor',
     'short_desc' => 'Liquid ring compressor for wet gas service',
     'features' => ['150 HP', 'Wet gas capable', 'Isothermal', 'Simple design']],
    
    ['sku' => 'CMP-PACKAGE-SKID', 'category' => 'compressors', 'name' => 'Compressor Package Skid Complete', 'price' => 385000, 'image_type' => 'compressor',
     'short_desc' => 'Complete compressor package with all auxiliaries',
     'features' => ['Turnkey package', 'Coolers included', 'Controls', 'Scrubbers']],

    // PUMPING SYSTEMS (12 products)
    ['sku' => 'PMP-CENT-1500', 'category' => 'pumping-systems', 'name' => 'API 610 Centrifugal Pump BB3', 'price' => 125000, 'image_type' => 'pump',
     'short_desc' => 'Heavy-duty centrifugal pump for refinery service',
     'features' => ['API 610 BB3', '1500 HP', 'Double suction', 'Mechanical seals']],
    
    ['sku' => 'PMP-PROG-500', 'category' => 'pumping-systems', 'name' => 'Progressive Cavity Pump 500 GPM', 'price' => 78000, 'image_type' => 'pump',
     'short_desc' => 'Progressive cavity pump for viscous fluids',
     'features' => ['500 GPM', 'High viscosity', 'Solids handling', 'Variable speed']],
    
    ['sku' => 'PMP-RECIP-API', 'category' => 'pumping-systems', 'name' => 'API 674 Reciprocating Pump', 'price' => 165000, 'image_type' => 'pump',
     'short_desc' => 'Positive displacement pump for injection service',
     'features' => ['API 674', 'Triplex design', '10,000 PSI', 'Plunger type']],
    
    ['sku' => 'PMP-MULTI-STG', 'category' => 'pumping-systems', 'name' => 'Multi-Stage Pump BB5', 'price' => 195000, 'image_type' => 'pump',
     'short_desc' => 'Multi-stage barrel pump for high pressure',
     'features' => ['BB5 design', '8 stages', 'High head', 'Barrel casing']],
    
    ['sku' => 'PMP-VERT-TURB', 'category' => 'pumping-systems', 'name' => 'Vertical Turbine Pump 2000 HP', 'price' => 285000, 'image_type' => 'pump',
     'short_desc' => 'Deep well vertical turbine pump',
     'features' => ['2000 HP', 'Multi-stage', 'Deep setting', 'Bowl assembly']],
    
    ['sku' => 'PMP-SUBMERS', 'category' => 'pumping-systems', 'name' => 'Submersible Pump System ESP', 'price' => 145000, 'image_type' => 'pump',
     'short_desc' => 'Electric submersible pump for artificial lift',
     'features' => ['ESP system', 'Variable speed', 'Gas handling', 'Abrasion resistant']],
    
    ['sku' => 'PMP-GEAR-API', 'category' => 'pumping-systems', 'name' => 'API 676 Gear Pump', 'price' => 55000, 'image_type' => 'pump',
     'short_desc' => 'Positive displacement gear pump for lube oil',
     'features' => ['API 676', 'External gear', 'High pressure', 'Low pulsation']],
    
    ['sku' => 'PMP-DIAPHRAGM', 'category' => 'pumping-systems', 'name' => 'Air-Operated Diaphragm Pump', 'price' => 28000, 'image_type' => 'pump',
     'short_desc' => 'AODD pump for chemical transfer',
     'features' => ['Air operated', 'Self-priming', 'Dry run safe', 'PTFE diaphragm']],
    
    ['sku' => 'PMP-PERISTALTIC', 'category' => 'pumping-systems', 'name' => 'Peristaltic Hose Pump 100 GPM', 'price' => 45000, 'image_type' => 'pump',
     'short_desc' => 'Peristaltic pump for abrasive slurries',
     'features' => ['100 GPM', 'Abrasive service', 'Seal-less', 'Reversible']],
    
    ['sku' => 'PMP-MAGDRIVE', 'category' => 'pumping-systems', 'name' => 'Magnetic Drive Pump API 685', 'price' => 85000, 'image_type' => 'pump',
     'short_desc' => 'Sealless magnetic drive pump for hazardous fluids',
     'features' => ['API 685', 'Sealless', 'Zero emissions', 'Metallic containment']],
    
    ['sku' => 'PMP-FIRE-WATER', 'category' => 'pumping-systems', 'name' => 'Fire Water Pump Package', 'price' => 225000, 'image_type' => 'pump',
     'short_desc' => 'Complete fire water pump system with diesel driver',
     'features' => ['UL/FM listed', 'Diesel driven', 'Jockey pump', 'Controller']],
    
    ['sku' => 'PMP-CHEMICAL', 'category' => 'pumping-systems', 'name' => 'Chemical Injection Pump Skid', 'price' => 95000, 'image_type' => 'pump',
     'short_desc' => 'Multi-pump chemical injection system',
     'features' => ['Multiple pumps', 'Metering', 'Calibration pot', 'Skid mounted']],

    // SAFETY EQUIPMENT (12 products)
    ['sku' => 'SAF-BOP-15K', 'category' => 'safety-equipment', 'name' => 'Annular BOP 13-5/8" 15K', 'price' => 245000, 'image_type' => 'safety',
     'short_desc' => 'Annular blowout preventer for well control',
     'features' => ['13-5/8" bore', '15,000 PSI', 'Spherical element', 'API 16A']],
    
    ['sku' => 'SAF-RAMS-15K', 'category' => 'safety-equipment', 'name' => 'Ram BOP Stack 13-5/8" 15K Triple', 'price' => 385000, 'image_type' => 'safety',
     'short_desc' => 'Triple ram BOP stack for well control',
     'features' => ['Triple rams', '15,000 PSI', 'Shear/blind', 'API 16A']],
    
    ['sku' => 'SAF-ACCUM-3000', 'category' => 'safety-equipment', 'name' => 'BOP Accumulator Unit 3000 PSI', 'price' => 165000, 'image_type' => 'safety',
     'short_desc' => 'Hydraulic accumulator unit for BOP operation',
     'features' => ['3000 PSI', '80 gallon bottles', 'Redundant pumps', 'API 16D']],
    
    ['sku' => 'SAF-CHOKE-MAN', 'category' => 'safety-equipment', 'name' => 'Choke Manifold 15K', 'price' => 125000, 'image_type' => 'safety',
     'short_desc' => 'Well control choke manifold system',
     'features' => ['15,000 PSI', 'Adjustable chokes', 'Kill line', 'API 16C']],
    
    ['sku' => 'SAF-DIVERTER', 'category' => 'safety-equipment', 'name' => 'Diverter System 30"', 'price' => 195000, 'image_type' => 'safety',
     'short_desc' => 'Surface diverter for shallow gas control',
     'features' => ['30" bore', 'Annular seal', 'Flow lines', 'Quick close']],
    
    ['sku' => 'SAF-PSV-SET', 'category' => 'safety-equipment', 'name' => 'Pressure Safety Valve Set', 'price' => 45000, 'image_type' => 'safety',
     'short_desc' => 'API 526 pressure relief valve assembly',
     'features' => ['API 526', 'Spring loaded', 'Flanged', 'Set pressure']],
    
    ['sku' => 'SAF-RUPTURE', 'category' => 'safety-equipment', 'name' => 'Rupture Disc Assembly', 'price' => 18000, 'image_type' => 'safety',
     'short_desc' => 'Pressure relief rupture disc system',
     'features' => ['Reverse acting', 'Holder included', 'Burst sensor', 'ASME certified']],
    
    ['sku' => 'SAF-FLAME-ARR', 'category' => 'safety-equipment', 'name' => 'Flame Arrester Detonation', 'price' => 35000, 'image_type' => 'safety',
     'short_desc' => 'In-line detonation flame arrester',
     'features' => ['Detonation rated', 'ATEX certified', 'Low pressure drop', 'Cleanable']],
    
    ['sku' => 'SAF-ESD-VALVE', 'category' => 'safety-equipment', 'name' => 'Emergency Shutdown Valve 12"', 'price' => 75000, 'image_type' => 'safety',
     'short_desc' => 'Fail-safe emergency shutdown valve',
     'features' => ['12" size', 'Fail-close', 'SIL 3 rated', 'Fast acting']],
    
    ['sku' => 'SAF-GAS-DET', 'category' => 'safety-equipment', 'name' => 'Gas Detection System Complete', 'price' => 125000, 'image_type' => 'safety',
     'short_desc' => 'Complete gas detection and alarm system',
     'features' => ['Multi-point', 'H2S/LEL', 'Control panel', 'SIL 2']],
    
    ['sku' => 'SAF-FIRE-SYS', 'category' => 'safety-equipment', 'name' => 'Fire Suppression System', 'price' => 185000, 'image_type' => 'safety',
     'short_desc' => 'Deluge fire suppression system complete',
     'features' => ['Deluge valves', 'Detection', 'Foam system', 'UL listed']],
    
    ['sku' => 'SAF-SCBA-SET', 'category' => 'safety-equipment', 'name' => 'SCBA Breathing Apparatus Set', 'price' => 28000, 'image_type' => 'safety',
     'short_desc' => 'Self-contained breathing apparatus kit',
     'features' => ['30 min duration', 'Composite cylinder', 'Full face mask', 'NIOSH approved']],

    // INSTRUMENTATION (12 products)
    ['sku' => 'INS-FLOW-12', 'category' => 'instrumentation', 'name' => 'Ultrasonic Flow Meter 12" Custody', 'price' => 95000, 'image_type' => 'instrument',
     'short_desc' => 'Custody transfer ultrasonic flow meter',
     'features' => ['12" size', 'Multi-path', 'AGA 9', '0.1% accuracy']],
    
    ['sku' => 'INS-CTRL-DCS', 'category' => 'instrumentation', 'name' => 'Distributed Control System Package', 'price' => 275000, 'image_type' => 'instrument',
     'short_desc' => 'Complete DCS system for process control',
     'features' => ['Redundant controllers', 'HMI stations', 'I/O modules', 'Engineering']],
    
    ['sku' => 'INS-LEVEL-RADAR', 'category' => 'instrumentation', 'name' => 'Radar Level Transmitter', 'price' => 15000, 'image_type' => 'instrument',
     'short_desc' => 'Non-contact radar level measurement',
     'features' => ['80 GHz', 'High accuracy', 'SIL 2', 'HART/FF']],
    
    ['sku' => 'INS-PRESS-XMTR', 'category' => 'instrumentation', 'name' => 'Pressure Transmitter Smart', 'price' => 8500, 'image_type' => 'instrument',
     'short_desc' => 'Smart pressure transmitter with diagnostics',
     'features' => ['0.04% accuracy', 'HART 7', 'SIL 2/3', 'Diagnostics']],
    
    ['sku' => 'INS-TEMP-RTD', 'category' => 'instrumentation', 'name' => 'Temperature Assembly RTD', 'price' => 4500, 'image_type' => 'instrument',
     'short_desc' => 'RTD temperature sensor with thermowell',
     'features' => ['Pt100 RTD', 'Thermowell', 'Transmitter', 'SIL rated']],
    
    ['sku' => 'INS-ANALYZER-GC', 'category' => 'instrumentation', 'name' => 'Process Gas Chromatograph', 'price' => 185000, 'image_type' => 'instrument',
     'short_desc' => 'Online gas chromatograph for composition analysis',
     'features' => ['Multi-component', 'Fast cycle', 'Heated enclosure', 'Remote ready']],
    
    ['sku' => 'INS-CONTROL-VLV', 'category' => 'instrumentation', 'name' => 'Control Valve 6" Globe', 'price' => 45000, 'image_type' => 'instrument',
     'short_desc' => 'Globe control valve with smart positioner',
     'features' => ['6" size', 'Smart positioner', 'Low noise trim', 'SIL 3']],
    
    ['sku' => 'INS-SAFETY-PLC', 'category' => 'instrumentation', 'name' => 'Safety PLC System SIL 3', 'price' => 165000, 'image_type' => 'instrument',
     'short_desc' => 'Safety instrumented system controller',
     'features' => ['SIL 3 certified', 'Redundant', 'TUV approved', 'IEC 61511']],
    
    ['sku' => 'INS-METERING', 'category' => 'instrumentation', 'name' => 'Fiscal Metering Skid', 'price' => 385000, 'image_type' => 'instrument',
     'short_desc' => 'Complete fiscal metering system',
     'features' => ['Turbine meters', 'Prover connection', 'Flow computer', 'Custody transfer']],
    
    ['sku' => 'INS-VIBRATION', 'category' => 'instrumentation', 'name' => 'Vibration Monitoring System', 'price' => 125000, 'image_type' => 'instrument',
     'short_desc' => 'Continuous machinery vibration monitoring',
     'features' => ['Multi-channel', 'API 670', 'Trending', 'Alarm management']],
    
    ['sku' => 'INS-SCADA', 'category' => 'instrumentation', 'name' => 'SCADA System Complete', 'price' => 225000, 'image_type' => 'instrument',
     'short_desc' => 'Supervisory control and data acquisition system',
     'features' => ['RTU/PLC integration', 'Historian', 'Reporting', 'Cybersecurity']],
    
    ['sku' => 'INS-CALIBRATOR', 'category' => 'instrumentation', 'name' => 'Multi-Function Calibrator', 'price' => 35000, 'image_type' => 'instrument',
     'short_desc' => 'Portable multi-function calibration tool',
     'features' => ['Pressure/temp', 'Electrical', 'HART comm', 'Documenting']],

    // SPARE PARTS (10 products)
    ['sku' => 'SPR-MUD-KIT', 'category' => 'spare-parts', 'name' => 'Mud Pump Major Overhaul Kit', 'price' => 67500, 'image_type' => 'spare',
     'short_desc' => 'Complete overhaul kit for triplex mud pump',
     'features' => ['Liners', 'Pistons', 'Valves', 'Seats', 'Seals']],
    
    ['sku' => 'SPR-BOP-KIT', 'category' => 'spare-parts', 'name' => 'BOP Stack Annual Service Kit', 'price' => 89000, 'image_type' => 'spare',
     'short_desc' => 'Annual service parts kit for BOP stack',
     'features' => ['Ram packers', 'Seals', 'Bonnets', 'O-rings']],
    
    ['sku' => 'SPR-PUMP-SEAL', 'category' => 'spare-parts', 'name' => 'Mechanical Seal Kit API 682', 'price' => 25000, 'image_type' => 'spare',
     'short_desc' => 'Complete mechanical seal kit for API pumps',
     'features' => ['Dual seals', 'Plan 53B', 'Cartridge type', 'Spare faces']],
    
    ['sku' => 'SPR-VALVE-TRIM', 'category' => 'spare-parts', 'name' => 'Control Valve Trim Set', 'price' => 18000, 'image_type' => 'spare',
     'short_desc' => 'Replacement trim set for control valves',
     'features' => ['Plug/seat', 'Cage', 'Packing', 'Gaskets']],
    
    ['sku' => 'SPR-BEARING-SET', 'category' => 'spare-parts', 'name' => 'Compressor Bearing Set', 'price' => 45000, 'image_type' => 'spare',
     'short_desc' => 'Complete bearing set for reciprocating compressor',
     'features' => ['Main bearings', 'Rod bearings', 'Thrust', 'Seals']],
    
    ['sku' => 'SPR-GASKET-KIT', 'category' => 'spare-parts', 'name' => 'Heat Exchanger Gasket Kit', 'price' => 12000, 'image_type' => 'spare',
     'short_desc' => 'Complete gasket set for plate heat exchanger',
     'features' => ['NBR gaskets', 'Full set', 'Clips', 'Adhesive']],
    
    ['sku' => 'SPR-FILTER-SET', 'category' => 'spare-parts', 'name' => 'Compressor Filter Element Set', 'price' => 8500, 'image_type' => 'spare',
     'short_desc' => 'Annual filter element replacement set',
     'features' => ['Air filters', 'Oil filters', 'Separators', 'O-rings']],
    
    ['sku' => 'SPR-IMPELLER', 'category' => 'spare-parts', 'name' => 'Pump Impeller Replacement', 'price' => 35000, 'image_type' => 'spare',
     'short_desc' => 'Replacement impeller for centrifugal pump',
     'features' => ['Duplex SS', 'Balanced', 'Wear rings', 'Hardware']],
    
    ['sku' => 'SPR-PISTON-SET', 'category' => 'spare-parts', 'name' => 'Compressor Piston Ring Set', 'price' => 22000, 'image_type' => 'spare',
     'short_desc' => 'Piston ring set for reciprocating compressor',
     'features' => ['PTFE rings', 'Rider rings', 'Oil rings', 'Full set']],
    
    ['sku' => 'SPR-COUPLING', 'category' => 'spare-parts', 'name' => 'Flexible Coupling Assembly', 'price' => 28000, 'image_type' => 'spare',
     'short_desc' => 'Disc pack coupling for rotating equipment',
     'features' => ['Disc pack', 'Spacer', 'Hubs', 'Hardware']],
];

// Clear existing products (handle foreign keys)
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('DELETE FROM inventory');
$pdo->exec('DELETE FROM order_items');
$pdo->exec('DELETE FROM products');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "Cleared existing products.\n\n";

// Insert products
$stmt = $pdo->prepare('
    INSERT INTO products (sku, category_id, name, short_desc, description, long_description, unit_price, features, specifications, image_url, weight_kg, warranty_months, moq, manufacturer, is_featured, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');

$count = 0;
foreach ($products as $i => $p) {
    $categoryId = $categories[$p['category']]['id'] ?? null;
    
    // Get image URL
    $imageType = $p['image_type'];
    $imageIndex = $i % count($imageUrls[$imageType]);
    $imageUrl = $imageUrls[$imageType][$imageIndex];
    
    // Generate long description
    $longDesc = "The {$p['name']} is a premium industrial equipment solution designed for demanding oil and gas applications. " .
                "Manufactured to the highest quality standards, this equipment delivers exceptional performance and reliability. " .
                "\n\nKey Benefits:\n" .
                "- Engineered for harsh operating environments\n" .
                "- Compliant with international standards\n" .
                "- Backed by comprehensive warranty and support\n" .
                "- Available with custom configurations\n\n" .
                "Contact our technical team for detailed specifications and customization options.";
    
    // Generate specifications
    $specs = [
        'Manufacturer' => 'Streicher GmbH',
        'Country of Origin' => 'Germany',
        'Certification' => 'ISO 9001:2015, API',
        'Lead Time' => '8-12 weeks',
        'Installation' => 'Available',
    ];
    
    $isFeatured = ($i < 12) ? 1 : 0; // First 12 are featured
    
    $stmt->execute([
        $p['sku'],
        $categoryId,
        $p['name'],
        $p['short_desc'],
        $p['short_desc'],
        $longDesc,
        $p['price'],
        json_encode($p['features']),
        json_encode($specs),
        $imageUrl,
        rand(50, 5000),
        24,
        1,
        'Streicher',
        $isFeatured,
        1
    ]);
    
    $count++;
    echo "  [{$count}/100] {$p['sku']} - {$p['name']} - \$" . number_format($p['price']) . "\n";
}

echo "\n✅ Successfully seeded {$count} high-value industrial products!\n";
echo "\nPrice range: \$8,500 - \$485,000\n";
echo "Categories: " . count($categories) . "\n";
echo "\nView products at: http://localhost:8000/catalog\n";
