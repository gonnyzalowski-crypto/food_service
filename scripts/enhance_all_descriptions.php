<?php
/**
 * Enhance descriptions for ALL products with detailed technical specifications
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

// Get all products
$stmt = $pdo->query('SELECT id, name, sku, short_desc, category_id FROM products ORDER BY id');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " products to enhance.\n\n";

// Category-specific technical details
$categorySpecs = [
    1 => [ // Hydraulic Systems
        'pressure_range' => '3,000 - 10,000 PSI',
        'temp_range' => '-20°C to +80°C',
        'fluid_type' => 'Mineral oil, synthetic fluids',
        'certifications' => 'ISO 4413, API, CE marked',
        'warranty' => '24 months',
    ],
    2 => [ // Drilling Equipment
        'load_capacity' => '500 - 1,500 tons',
        'speed_range' => '0 - 500 RPM',
        'power_range' => '1,000 - 5,000 HP',
        'certifications' => 'API 7K, API 8C, DNV-GL',
        'warranty' => '24 months',
    ],
    3 => [ // Pipeline Components
        'pressure_class' => 'ANSI 150 - 2500',
        'size_range' => '2" - 48" (DN50 - DN1200)',
        'materials' => 'Carbon Steel, Stainless Steel, Duplex',
        'certifications' => 'API 6D, ASME B16.34, NACE MR0175',
        'warranty' => '36 months',
    ],
    4 => [ // Compressors
        'capacity_range' => '100 - 5,000 CFM',
        'pressure_range' => '100 - 6,000 PSI',
        'power_range' => '50 - 2,000 HP',
        'certifications' => 'API 618, API 619, ASME',
        'warranty' => '24 months',
    ],
    5 => [ // Pumping Systems
        'flow_range' => '100 - 10,000 GPM',
        'head_range' => '100 - 2,000 feet',
        'efficiency' => '75% - 90%',
        'certifications' => 'API 610, API 674, ISO 5199',
        'warranty' => '24 months',
    ],
    6 => [ // Safety Equipment
        'protection_class' => 'IP66/IP67',
        'hazardous_area' => 'Zone 0, 1, 2 / Div 1, 2',
        'response_time' => '< 10 seconds',
        'certifications' => 'ATEX, IECEx, SIL 2/3',
        'warranty' => '36 months',
    ],
    7 => [ // Instrumentation
        'accuracy' => '±0.1% to ±0.5%',
        'output' => '4-20mA, HART, Modbus, Foundation Fieldbus',
        'power' => '24VDC loop powered',
        'certifications' => 'ATEX, IECEx, SIL 2 capable',
        'warranty' => '24 months',
    ],
    8 => [ // Spare Parts
        'compatibility' => 'OEM and aftermarket',
        'materials' => 'Premium grade materials',
        'quality' => '100% tested before shipping',
        'certifications' => 'ISO 9001:2015',
        'warranty' => '12 months',
    ],
];

$updateStmt = $pdo->prepare('UPDATE products SET long_description = ? WHERE id = ?');

$updated = 0;

foreach ($products as $product) {
    $catId = $product['category_id'] ?? 1;
    $specs = $categorySpecs[$catId] ?? $categorySpecs[1];
    $name = $product['name'];
    $shortDesc = $product['short_desc'];
    
    // Generate detailed description
    $longDesc = "The **{$name}** is a premium industrial equipment solution designed for demanding oil and gas, petrochemical, and heavy industry applications. Manufactured to the highest quality standards in Germany, this equipment delivers exceptional performance, reliability, and longevity.\n\n";
    
    $longDesc .= "## Product Overview\n\n";
    $longDesc .= "{$shortDesc}\n\n";
    $longDesc .= "Gordon Food Service GmbH has been a trusted supplier of industrial equipment for over 50 years. Our products are engineered to meet the most stringent international standards and are backed by comprehensive technical support.\n\n";
    
    $longDesc .= "## Technical Specifications\n\n";
    foreach ($specs as $key => $value) {
        $label = ucwords(str_replace('_', ' ', $key));
        $longDesc .= "- **{$label}:** {$value}\n";
    }
    
    $longDesc .= "\n## Key Features\n\n";
    $longDesc .= "- Precision-engineered components for maximum reliability\n";
    $longDesc .= "- Robust construction for harsh operating environments\n";
    $longDesc .= "- Easy maintenance with accessible service points\n";
    $longDesc .= "- Comprehensive documentation and technical support\n";
    $longDesc .= "- Global spare parts availability\n";
    $longDesc .= "- Factory testing and certification\n";
    
    $longDesc .= "\n## Quality Assurance\n\n";
    $longDesc .= "All Gordon Food Service products undergo rigorous quality control procedures including:\n";
    $longDesc .= "- Incoming material inspection\n";
    $longDesc .= "- In-process quality checks\n";
    $longDesc .= "- Final assembly testing\n";
    $longDesc .= "- Hydrostatic/pneumatic testing where applicable\n";
    $longDesc .= "- Complete documentation package\n";
    
    $longDesc .= "\n## Delivery & Support\n\n";
    $longDesc .= "- **Lead Time:** 8-12 weeks (standard), expedited available\n";
    $longDesc .= "- **Shipping:** Worldwide delivery with full insurance\n";
    $longDesc .= "- **Installation:** On-site commissioning available\n";
    $longDesc .= "- **Training:** Operator and maintenance training included\n";
    $longDesc .= "- **Support:** 24/7 technical hotline\n";
    
    $longDesc .= "\n## Ordering Information\n\n";
    $longDesc .= "Contact our sales team for:\n";
    $longDesc .= "- Custom specifications and configurations\n";
    $longDesc .= "- Volume pricing and framework agreements\n";
    $longDesc .= "- Technical consultations\n";
    $longDesc .= "- Spare parts packages\n";
    
    $updateStmt->execute([$longDesc, $product['id']]);
    $updated++;
    
    if ($updated % 10 === 0) {
        echo "Updated $updated products...\n";
    }
}

echo "\n✅ Enhanced descriptions for $updated products!\n";
