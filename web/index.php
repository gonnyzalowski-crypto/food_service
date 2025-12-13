<?php
declare(strict_types=1);

// Serve static files directly when using PHP built-in server
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Static file mappings
$staticMappings = [
    '/styles.css' => __DIR__ . '/assets/styles.css',
    '/logo.png' => __DIR__ . '/assets/logo.png',
    '/favicon.png' => __DIR__ . '/assets/favicon.png',
    '/product-placeholder.svg' => __DIR__ . '/assets/product-placeholder.svg',
    '/privacy-policy.pdf' => __DIR__ . '/assets/privacy-policy.pdf',
];

// Check for direct static file mapping
if (isset($staticMappings[$requestPath])) {
    $filePath = $staticMappings[$requestPath];
    if (file_exists($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        exit;
    }
}

// Serve files from /images/ path
if (preg_match('#^/images/(.+)$#', $requestPath, $matches)) {
    $imagePath = $matches[1];
    // Try web/images first, then root images folder
    $possiblePaths = [
        __DIR__ . '/images/' . $imagePath,
        dirname(__DIR__) . '/images/' . $imagePath,
    ];
    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $mimeTypes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
            ];
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=31536000');
            readfile($filePath);
            exit;
        }
    }
}

// Serve files from /assets/ path
if (preg_match('#^/assets/(.+)$#', $requestPath, $matches)) {
    $assetPath = __DIR__ . '/assets/' . $matches[1];
    if (file_exists($assetPath) && is_file($assetPath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($assetPath);
        exit;
    }
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/translations.php';

use Dotenv\Dotenv;

// Secure session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['de', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'de';

// Load env
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Health check endpoint - respond before DB connection for Railway healthcheck
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestPath === '/health' || $requestPath === '/healthz') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'timestamp' => date('c')]);
    exit;
}

// Database setup endpoint - initialize all tables
if ($requestPath === '/setup-database') {
    $setupDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQL_HOST'] ?? $_ENV['MYSQLHOST'] ?? '127.0.0.1';
    $setupDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQL_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $setupDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQL_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? 'streicher';
    $setupDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQL_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $setupDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQL_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $setupPdo = new PDO(
            "mysql:host=$setupDbHost;port=$setupDbPort;dbname=$setupDbName;charset=utf8mb4",
            $setupDbUser, $setupDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $tables = [];
        
        // Categories table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            image_url VARCHAR(500),
            parent_id INT DEFAULT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'categories';
        
        // Products table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sku VARCHAR(100) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255),
            description TEXT,
            specifications TEXT,
            category_id INT,
            unit_price DECIMAL(12,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'EUR',
            stock_quantity INT DEFAULT 0,
            lead_time_days INT DEFAULT 14,
            weight_kg DECIMAL(10,2),
            dimensions VARCHAR(100),
            image_url VARCHAR(500),
            is_active TINYINT(1) DEFAULT 1,
            is_featured TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )");
        $tables[] = 'products';

        // Ensure is_featured column exists for older databases
        try {
            $setupPdo->exec("ALTER TABLE products ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
        }
        
        // Users table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_id INT,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(255),
            phone VARCHAR(50),
            role ENUM('customer', 'admin') DEFAULT 'customer',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'users';
        
        // Orders table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT,
            status VARCHAR(50) DEFAULT 'pending',
            subtotal DECIMAL(12,2),
            tax_amount DECIMAL(12,2),
            shipping_amount DECIMAL(12,2),
            total_amount DECIMAL(12,2),
            currency VARCHAR(3) DEFAULT 'EUR',
            billing_name VARCHAR(255),
            billing_company VARCHAR(255),
            billing_email VARCHAR(255),
            billing_phone VARCHAR(50),
            billing_address TEXT,
            billing_city VARCHAR(100),
            billing_postal VARCHAR(20),
            billing_country VARCHAR(100),
            shipping_name VARCHAR(255),
            shipping_company VARCHAR(255),
            shipping_address TEXT,
            shipping_city VARCHAR(100),
            shipping_postal VARCHAR(20),
            shipping_country VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'orders';
        
        // Order items table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT,
            sku VARCHAR(100),
            name VARCHAR(255),
            quantity INT NOT NULL,
            unit_price DECIMAL(12,2),
            total_price DECIMAL(12,2),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )");
        $tables[] = 'order_items';
        
        // Shipments table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS shipments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            tracking_number VARCHAR(100),
            carrier VARCHAR(100),
            status VARCHAR(50) DEFAULT 'pending',
            shipped_at TIMESTAMP NULL,
            delivered_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )");
        $tables[] = 'shipments';
        
        // Tracking history table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS tracking_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            shipment_id INT NOT NULL,
            status VARCHAR(100),
            location VARCHAR(255),
            description TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
        )");
        $tables[] = 'tracking_history';
        
        // Payment uploads table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS payment_uploads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            filename VARCHAR(255),
            original_filename VARCHAR(255),
            file_path VARCHAR(500),
            file_size INT,
            mime_type VARCHAR(100),
            notes TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            reviewed_at TIMESTAMP NULL,
            reviewed_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )");
        $tables[] = 'payment_uploads';
        
        // Settings table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'settings';
        
        // Support tickets table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_number VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255),
            company VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(50),
            subject VARCHAR(255),
            message TEXT,
            status VARCHAR(50) DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'support_tickets';
        
        // Login attempts table (rate limiting)
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255),
            success TINYINT(1) DEFAULT 0,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, attempted_at)
        )");
        $tables[] = 'login_attempts';
        
        // Email logs table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            to_email VARCHAR(255),
            subject VARCHAR(255),
            status VARCHAR(50),
            error_message TEXT,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'email_logs';
        
        // Tracking communications table
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS tracking_communications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            sender_type ENUM('customer', 'admin') NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )");
        $tables[] = 'tracking_communications';
        
        // Create default admin user if not exists
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
            $setupPdo->exec("INSERT INTO users (email, password_hash, full_name, role) VALUES ('admin@streichergmbh.com', '$adminHash', 'Administrator', 'admin')");
        }
        
        // Insert default categories if empty
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO categories (name, slug, description) VALUES 
                ('Pipelines & Plants', 'pipelines-plants', 'Pipeline construction and plant equipment'),
                ('Mechanical Engineering', 'mechanical-engineering', 'Mechanical engineering equipment'),
                ('Drilling Technology', 'drilling-technology', 'Drilling rigs and equipment'),
                ('Hydraulic Systems', 'hydraulic-systems', 'Hydraulic power units and components'),
                ('Instrumentation', 'instrumentation', 'Measurement and control instruments')
            ");
        }
        
        // Seed products from image folders if empty
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM products");
        if ($stmt->fetchColumn() == 0) {
            $products = [
                ['Centrifugal Pump 1000 GPM', 'centrifugal-pump-1000-gpm', 1, 18500, 'High-capacity centrifugal pump for industrial fluid transfer, 1000 GPM flow rate'],
                ['Custom Hydraulic Manifold Block', 'custom-hydraulic-manifold-block', 4, 4200, 'Precision-machined hydraulic manifold block for custom applications'],
                ['Decanting Centrifuge 14"', 'decanting-centrifuge-14', 1, 85000, '14-inch decanting centrifuge for solids control in drilling operations'],
                ['Directional Control Valve 4-Way', 'directional-control-valve-4-way', 4, 2800, '4-way directional control valve for hydraulic systems'],
                ['Drawworks 3000 HP', 'drawworks-3000-hp', 3, 450000, 'Heavy-duty 3000 HP drawworks for drilling rig hoisting operations'],
                ['Drill Pipe Elevator 500-Ton', 'drill-pipe-elevator-500-ton', 3, 28000, '500-ton capacity drill pipe elevator for handling operations'],
                ['Drilling Swivel 500-Ton', 'drilling-swivel-500-ton', 3, 125000, '500-ton drilling swivel for rotary drilling operations'],
                ['Flow Meter Ultrasonic', 'flow-meter-ultrasonic', 5, 8500, 'Non-invasive ultrasonic flow meter for accurate flow measurement'],
                ['Gas Detector Multi-Channel', 'gas-detector-multi-channel', 5, 12000, 'Advanced multi-channel gas detection system for industrial safety'],
                ['Gate Valve 24" Class 900', 'gate-valve-24-class-900', 1, 35000, '24-inch gate valve Class 900 for high-pressure pipeline applications'],
                ['Heat Exchanger Shell & Tube', 'heat-exchanger-shell-tube', 2, 45000, 'Industrial shell and tube heat exchanger for process heating/cooling'],
                ['Heavy Duty Hydraulic Cylinder 800-Ton', 'heavy-duty-hydraulic-cylinder-800-ton', 4, 68000, '800-ton heavy-duty hydraulic cylinder for extreme applications'],
                ['Hexagonal Kelly 5.25"', 'hexagonal-kelly', 3, 42000, '5.25-inch hexagonal kelly for drilling string rotation'],
                ['High Pressure Compressor', 'high-pressure-compressor', 2, 95000, 'Industrial high-pressure compressor for oil and gas applications'],
                ['High Pressure Filter Assembly', 'high-pressure-filter-assembly', 4, 3500, 'High-pressure hydraulic filter assembly for system protection'],
                ['High Pressure Hose Assembly Kit', 'high-pressure-hose-assembly-kit', 4, 1800, 'Complete high-pressure hose assembly kit with fittings'],
                ['Hydraulic Accumulator 500L', 'hydraulic-accumulator-500l', 4, 8500, '500-liter hydraulic bladder accumulator for energy storage'],
                ['Hydraulic Clamping System', 'hydraulic-clamping-system', 4, 15000, 'Precision hydraulic clamping system for industrial applications'],
                ['Hydraulic Motor 750cc', 'hydraulic-motor-750cc', 4, 4800, '750cc displacement hydraulic motor for heavy-duty applications'],
                ['Hydraulic Oil Cooler 1000kW', 'hydraulic-oil-cooler-1000kw', 4, 22000, '1000kW capacity hydraulic oil cooler for thermal management'],
                ['Hydraulic Power Unit', 'hydraulic-power-unit', 4, 35000, 'Complete hydraulic power unit for industrial applications'],
                ['Hydraulic Power Unit 5000 HP', 'hydraulic-power-unit-5000-hp', 4, 285000, '5000 HP hydraulic power unit for heavy industrial operations'],
                ['Hydraulic Reservoir Tank 2000L', 'hydraulic-reservoir-tank-2000l', 4, 6500, '2000-liter hydraulic oil reservoir tank with accessories'],
                ['Hydraulic Test Bench 500 Bar', 'hydraulic-test-bench-500-bar', 4, 48000, '500 bar hydraulic test bench for component testing'],
                ['Hydrocyclone Desander 12"', 'hydrocyclone-desander-12', 3, 18000, '12-inch hydrocyclone desander for drilling fluid processing'],
                ['Iron Roughneck Complete', 'iron-roughneck-complete', 3, 380000, 'Complete iron roughneck system for automated pipe handling'],
                ['Mud Mixing System Complete', 'mud-mixing-system-complete', 3, 125000, 'Complete mud mixing system for drilling fluid preparation'],
                ['Pig Launcher 20"', 'pig-launcher-20', 1, 55000, '20-inch pig launcher for pipeline cleaning and inspection'],
                ['Pipeline Ball Valve 24"', 'pipeline-ball-valve-24', 1, 42000, '24-inch trunnion-mounted ball valve for pipeline applications'],
                ['Pipeline Pig Launcher 48"', 'pipeline-pig-launcher-48', 1, 185000, '48-inch pig launcher for large diameter pipelines'],
                ['Pipeline Pig Receiver 48"', 'pipeline-pig-receiver-48', 1, 175000, '48-inch pig receiver for large diameter pipelines'],
                ['Pipeline Repair Clamp 48"', 'pipeline-repair-clamp-48', 1, 28000, '48-inch pipeline repair clamp for emergency repairs'],
                ['Power Tong 150K ft-lb', 'power-tong-150k-ft-lb', 3, 165000, '150,000 ft-lb power tong for casing and tubing operations'],
                ['Pressure Intensifier 1:10', 'pressure-intensifier-1-10', 4, 12500, '1:10 ratio hydraulic pressure intensifier'],
                ['Pressure Transmitter 0-10000 PSI', 'pressure-transmitter-10000-psi', 5, 2800, 'High-accuracy pressure transmitter for extreme pressure applications'],
                ['Rotary Slips 500-Ton', 'rotary-slips-500-ton', 3, 45000, '500-ton rotary slips for drill string handling'],
                ['Rotary Table 49.5" 3000 HP', 'rotary-table-3000-hp', 3, 320000, '49.5-inch rotary table rated for 3000 HP drilling operations'],
                ['Safety Relief Valve 6"', 'safety-relief-valve-6', 1, 8500, '6-inch safety relief valve for pressure protection'],
                ['Screw Compressor 500 HP', 'screw-compressor-500-hp', 2, 125000, '500 HP rotary screw compressor for continuous operation'],
                ['Servo Hydraulic Control System', 'servo-hydraulic-control-system', 4, 85000, 'Advanced servo-hydraulic control system for precision applications'],
                ['Shale Shaker 4-Panel', 'shale-shaker-4-panel', 3, 95000, '4-panel shale shaker for drilling fluid solids control'],
                ['Swing Check Valve 20"', 'swing-check-valve-20', 1, 18500, '20-inch swing check valve for pipeline applications'],
                ['Top Drive System 750-Ton', 'top-drive-system-750-ton', 3, 850000, '750-ton top drive system for efficient drilling operations'],
                ['Triplex Mud Pump 2500 HP', 'triplex-mud-pump-2500-hp', 3, 425000, '2500 HP triplex mud pump for deep drilling operations'],
                ['Vacuum Degasser 1500 GPM', 'vacuum-degasser-1500-gpm', 3, 75000, '1500 GPM vacuum degasser for drilling fluid processing'],
                ['Variable Displacement Pump 500cc', 'variable-displacement-pump-500cc', 4, 6800, '500cc variable displacement hydraulic piston pump'],
            ];
            
            $insertStmt = $setupPdo->prepare("INSERT INTO products (sku, name, slug, description, category_id, unit_price, image_url, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
            
            foreach ($products as $i => $p) {
                $sku = 'STR-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT);
                $slug = $p[1];
                $imageUrl = '/images/' . $slug . '/1.jpg';
                // Check for different extensions
                $possibleExts = ['jpg', 'jpeg', 'png', 'webp'];
                foreach ($possibleExts as $ext) {
                    $testPath = __DIR__ . '/images/' . $slug;
                    if (is_dir($testPath)) {
                        $files = glob($testPath . '/*.*');
                        if (!empty($files)) {
                            $imageUrl = '/images/' . $slug . '/' . basename($files[0]);
                            break;
                        }
                    }
                }
                $isFeatured = $i < 6 ? 1 : 0; // First 6 products are featured
                $insertStmt->execute([$sku, $p[0], $slug, $p[4], $p[2], $p[3], $imageUrl, $isFeatured]);
            }
        }
        
        header('Content-Type: text/html');
        echo '<h1>Database Setup Complete</h1>';
        echo '<p>Created tables: ' . implode(', ', $tables) . '</p>';
        echo '<p>Seeded ' . count($products ?? []) . ' products</p>';
        echo '<p>Default admin: admin@streichergmbh.com / admin123</p>';
        echo '<p><strong>Change the admin password immediately!</strong></p>';
        echo '<p><a href="/">Go to Homepage</a> | <a href="/admin">Go to Admin</a></p>';
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Database Setup Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Support both local and Railway MySQL environment variables
$dbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQL_HOST'] ?? $_ENV['MYSQLHOST'] ?? '127.0.0.1';
$dbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQL_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
$dbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQL_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? 'streicher';
$dbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQL_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQL_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $dbHost,
    $dbPort,
    $dbName
);

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Return error page but don't crash - allow healthcheck to pass
    http_response_code(503);
    echo '<h1>Database Connection Error</h1><p>Please check database configuration.</p>';
    echo '<pre>Host: ' . htmlspecialchars($dbHost) . '</pre>';
    echo '<pre>Database: ' . htmlspecialchars($dbName) . '</pre>';
    exit;
}

// ============ SERVE STATIC FILES ============
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Serve uploads from /uploads/ directory (at project root, not in /web/)
if (preg_match('#^/uploads/(.+)$#', $path, $matches)) {
    $uploadPath = __DIR__ . '/../uploads/' . $matches[1];
    
    if (file_exists($uploadPath) && is_file($uploadPath)) {
        $ext = strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
        ];
        
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($uploadPath));
        header('Cache-Control: public, max-age=86400');
        readfile($uploadPath);
        exit;
    }
}

// Serve images from /images/ directory (located at project root, not in /web/)
if (preg_match('#^/images/(.+)$#', $path, $matches)) {
    // In Docker: /var/www/html/web/../images/ = /var/www/html/images/
    $imagePath = realpath(__DIR__ . '/../images/' . $matches[1]) ?: (__DIR__ . '/../images/' . $matches[1]);
    
    if (file_exists($imagePath) && is_file($imagePath)) {
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($imagePath));
        header('Cache-Control: public, max-age=86400');
        readfile($imagePath);
        exit;
    }
}

// ============ HELPER FUNCTIONS ============

function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function render_template(string $template, array $params = []): void {
    global $pdo, $lang;
    $params['lang'] = $lang;
    extract($params);
    ob_start();
    require __DIR__ . '/templates/' . $template;
    $content = ob_get_clean();
    require __DIR__ . '/templates/layout.php';
    exit;
}

function render_admin_template(string $template, array $params = []): void {
    global $pdo;
    extract($params);
    ob_start();
    require __DIR__ . '/templates/admin/' . $template;
    $content = ob_get_clean();
    require __DIR__ . '/templates/admin/layout.php';
    exit;
}

function require_admin(): void {
    if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: /admin/login');
        exit;
    }
}

function format_price(float $amount, string $currency = 'EUR'): string {
    if ($currency === 'USD') {
        return '$' . number_format($amount, 2);
    }
    return '€' . number_format($amount, 2);
}

function get_exchange_rate(): float {
    global $pdo;
    // Try to get cached rate from database
    $stmt = $pdo->query("SELECT setting_value, updated_at FROM settings WHERE setting_key = 'eur_usd_rate'");
    $row = $stmt->fetch();
    
    if ($row) {
        $lastUpdate = strtotime($row['updated_at']);
        // If rate is less than 24 hours old, use cached
        if (time() - $lastUpdate < 86400) {
            return (float)$row['setting_value'];
        }
    }
    
    // Fetch new rate from API (fallback to default if fails)
    $rate = 1.08; // Default EUR/USD rate
    try {
        $url = 'https://api.exchangerate-api.com/v4/latest/EUR';
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['rates']['USD'])) {
                $rate = (float)$data['rates']['USD'];
            }
        }
    } catch (Exception $e) {
        // Use default rate
    }
    
    // Cache the rate
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('eur_usd_rate', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$rate]);
    
    return $rate;
}

function convert_price(float $eurAmount, string $toCurrency): float {
    if ($toCurrency === 'EUR') {
        return $eurAmount;
    }
    $rate = get_exchange_rate();
    return $eurAmount * $rate;
}

function get_cart_count(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}

function get_cart_total(): float {
    global $pdo;
    $cart = $_SESSION['cart'] ?? [];
    $total = 0.0;
    foreach ($cart as $item) {
        $stmt = $pdo->prepare('SELECT unit_price FROM products WHERE sku = ? LIMIT 1');
        $stmt->execute([$item['sku']]);
        $price = (float)($stmt->fetchColumn() ?: 0);
        $total += $price * $item['qty'];
    }
    return $total;
}

function generate_order_number(): string {
    return 'ST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function generate_tracking_number(): string {
    return 'STR' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 10));
}

function get_status_label(string $status): string {
    $labels = [
        'pending' => 'Pending',
        'awaiting_payment' => 'Awaiting Payment',
        'payment_uploaded' => 'Payment Uploaded',
        'payment_confirmed' => 'Payment Confirmed',
        'payment_declined' => 'Payment Declined',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'in_transit' => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];
    return $labels[$status] ?? ucfirst($status);
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">';
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ============ API ROUTES ============

// GET /api/products
if ($path === '/api/products' && $method === 'GET') {
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    
    $sql = 'SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1';
    $params = [];
    
    if ($category) {
        $sql .= ' AND c.slug = ?';
        $params[] = $category;
    }
    if ($search) {
        $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_desc LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= ' ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 50';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response($stmt->fetchAll());
}

// POST /api/cart - Add to cart
if ($path === '/api/cart' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $sku = $data['sku'] ?? null;
    $qty = max(1, (int)($data['qty'] ?? 1));
    
    if (!$sku) {
        json_response(['error' => 'SKU required'], 400);
    }
    
    // Verify product exists
    $stmt = $pdo->prepare('SELECT sku, name, unit_price FROM products WHERE sku = ? AND is_active = 1');
    $stmt->execute([$sku]);
    $product = $stmt->fetch();
    
    if (!$product) {
        json_response(['error' => 'Product not found'], 404);
    }
    
    $cart = $_SESSION['cart'] ?? [];
    
    // Check if already in cart
    $found = false;
    foreach ($cart as &$item) {
        if ($item['sku'] === $sku) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }
    unset($item);
    
    if (!$found) {
        $cart[] = [
            'sku' => $sku,
            'name' => $product['name'],
            'price' => (float)$product['unit_price'],
            'qty' => $qty,
        ];
    }
    
    $_SESSION['cart'] = $cart;
    
    json_response([
        'ok' => true,
        'cart_count' => array_sum(array_column($cart, 'qty')),
        'message' => 'Added to cart',
    ]);
}

// GET /api/cart
if ($path === '/api/cart' && $method === 'GET') {
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    json_response([
        'cart' => $cart,
        'count' => array_sum(array_column($cart, 'qty')),
        'total' => $total,
    ]);
}

// DELETE /api/cart - Remove item
if ($path === '/api/cart' && $method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $sku = $data['sku'] ?? null;
    
    if ($sku) {
        $cart = $_SESSION['cart'] ?? [];
        $cart = array_filter($cart, fn($item) => $item['sku'] !== $sku);
        $_SESSION['cart'] = array_values($cart);
    }
    
    json_response(['ok' => true, 'cart_count' => get_cart_count()]);
}

// PUT /api/cart - Update quantity
if ($path === '/api/cart' && $method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $sku = $data['sku'] ?? null;
    $qty = max(0, (int)($data['qty'] ?? 0));
    
    $cart = $_SESSION['cart'] ?? [];
    
    if ($qty === 0) {
        $cart = array_filter($cart, fn($item) => $item['sku'] !== $sku);
    } else {
        foreach ($cart as &$item) {
            if ($item['sku'] === $sku) {
                $item['qty'] = $qty;
                break;
            }
        }
        unset($item);
    }
    
    $_SESSION['cart'] = array_values($cart);
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['qty'];
    }
    
    json_response([
        'ok' => true,
        'cart_count' => get_cart_count(),
        'total' => $total,
    ]);
}

// POST /api/checkout - Create order
if ($path === '/api/checkout' && $method === 'POST') {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        json_response(['error' => 'Cart is empty'], 400);
    }
    
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    $pdo->beginTransaction();
    try {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        
        $orderNumber = generate_order_number();
        
        $stmt = $pdo->prepare(
            'INSERT INTO orders (user_id, order_number, status, total_amount, currency, billing_address, shipping_address, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'] ?? null,
            $orderNumber,
            'awaiting_payment',
            $total,
            'USD',
            json_encode($data['billing'] ?? []),
            json_encode($data['shipping'] ?? []),
            $data['notes'] ?? null,
        ]);
        $orderId = (int)$pdo->lastInsertId();
        
        $stmtItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
             VALUES (?, ?, ?, ?, ?)'
        );
        
        foreach ($cart as $item) {
            // Get product ID
            $stmt = $pdo->prepare('SELECT id FROM products WHERE sku = ?');
            $stmt->execute([$item['sku']]);
            $productId = (int)$stmt->fetchColumn();
            
            $stmtItem->execute([
                $orderId,
                $productId,
                $item['qty'],
                $item['price'],
                $item['price'] * $item['qty'],
            ]);
        }
        
        $pdo->commit();
        $_SESSION['cart'] = [];
        $_SESSION['last_order_id'] = $orderId;
        $_SESSION['last_order_number'] = $orderNumber;
        
        json_response([
            'ok' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total' => $total,
            'redirect' => '/order/' . $orderId . '/payment',
        ]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        json_response(['error' => 'Checkout failed: ' . $e->getMessage()], 500);
    }
}

// POST /api/orders/{id}/upload-payment
if (preg_match('#^/api/orders/(\d+)/upload-payment$#', $path, $m) && $method === 'POST') {
    $orderId = (int)$m[1];
    
    // Verify order exists
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        json_response(['error' => 'Order not found'], 404);
    }
    
    if (empty($_FILES['receipt'])) {
        json_response(['error' => 'No file uploaded'], 400);
    }
    
    $file = $_FILES['receipt'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    
    // Validate MIME type
    if (!in_array($file['type'], $allowedTypes)) {
        json_response(['error' => 'Invalid file type. Allowed: JPG, PNG, GIF, PDF'], 400);
    }
    
    // Validate file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        json_response(['error' => 'Invalid file extension'], 400);
    }
    
    // Validate actual file content using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $actualMime = $finfo->file($file['tmp_name']);
    if (!in_array($actualMime, $allowedTypes)) {
        json_response(['error' => 'File content does not match allowed types'], 400);
    }
    
    if ($file['size'] > 10 * 1024 * 1024) {
        json_response(['error' => 'File too large. Max 10MB'], 400);
    }
    
    // Create uploads directory
    $uploadDir = __DIR__ . '/../uploads/payments/' . $orderId;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate secure random filename to prevent predictable naming
    $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        json_response(['error' => 'Failed to save file'], 500);
    }
    
    // Save to database
    $stmt = $pdo->prepare(
        'INSERT INTO payment_uploads (order_id, filename, original_filename, file_path, file_size, mime_type, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $orderId,
        $filename,
        $file['name'],
        'uploads/payments/' . $orderId . '/' . $filename,
        $file['size'],
        $file['type'],
        $_POST['notes'] ?? null,
    ]);
    
    // Update order status
    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
        ->execute(['payment_uploaded', $orderId]);
    
    json_response([
        'ok' => true,
        'message' => 'Payment receipt uploaded successfully',
        'filename' => $filename,
    ]);
}

// GET /api/orders/{id}
if (preg_match('#^/api/orders/(\d+)$#', $path, $m) && $method === 'GET') {
    $orderId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        json_response(['error' => 'Order not found'], 404);
    }
    
    // Get items
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $order['items'] = $stmt->fetchAll();
    
    // Get shipments
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $order['shipments'] = $stmt->fetchAll();
    
    json_response($order);
}

// POST /api/track
if ($path === '/api/track' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $trackingNumber = $data['tracking_number'] ?? $_POST['tracking_number'] ?? null;
    
    if (!$trackingNumber) {
        json_response(['error' => 'Tracking number required'], 400);
    }
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE tracking_number = ? LIMIT 1');
    $stmt->execute([$trackingNumber]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        json_response(['error' => 'Shipment not found'], 404);
    }
    
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    
    json_response([
        'tracking_number' => $shipment['tracking_number'],
        'carrier' => $shipment['carrier'],
        'status' => $shipment['status'],
        'origin' => $shipment['origin_city'] ?? 'Regensburg, DE',
        'destination' => $shipment['destination_city'] ?? null,
        'shipped_at' => $shipment['shipped_at'] ?? null,
        'estimated_delivery' => $shipment['estimated_delivery'] ?? null,
        'events' => $events,
    ]);
}

// POST /api/tracking/{tracking_number}/message - Customer sends message/document
if (preg_match('#^/api/tracking/([A-Za-z0-9]+)/message$#', $path, $m) && $method === 'POST') {
    $trackingNumber = $m[1];
    
    // Verify shipment exists
    $stmt = $pdo->prepare('SELECT id FROM shipments WHERE tracking_number = ?');
    $stmt->execute([$trackingNumber]);
    if (!$stmt->fetch()) {
        json_response(['error' => 'Shipment not found'], 404);
    }
    
    $message = $_POST['message'] ?? '';
    $documentPath = null;
    $documentName = null;
    $documentType = null;
    $messageType = 'message';
    
    // Handle file upload
    if (!empty($_FILES['document']['name'])) {
        $uploadDir = __DIR__ . '/uploads/tracking/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $safeName = $trackingNumber . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $safeName;
        
        if (move_uploaded_file($_FILES['document']['tmp_name'], $destPath)) {
            $documentPath = '/uploads/tracking/' . $safeName;
            $documentName = $_FILES['document']['name'];
            $documentType = $_FILES['document']['type'];
            $messageType = 'document';
        }
    }
    
    if (empty($message) && empty($documentPath)) {
        json_response(['error' => 'Message or document required'], 400);
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO tracking_communications (tracking_number, sender_type, message_type, message, document_name, document_path, document_type) 
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $trackingNumber,
        'customer',
        $messageType,
        $message ?: null,
        $documentName,
        $documentPath,
        $documentType,
    ]);
    
    json_response(['ok' => true, 'message' => 'Message sent successfully']);
}

// ============ ADMIN API ROUTES ============

// POST /admin/orders/{id}/confirm-payment
if (preg_match('#^/admin/orders/(\d+)/confirm-payment$#', $path, $m) && $method === 'POST') {
    require_admin();
    $orderId = (int)$m[1];
    
    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
        ->execute(['payment_confirmed', $orderId]);
    
    // Update payment upload status (simplified - avoid missing columns)
    try {
        $pdo->prepare('UPDATE payment_uploads SET status = ? WHERE order_id = ? AND status = ?')
            ->execute(['approved', $orderId, 'pending']);
    } catch (PDOException $e) {
        // Ignore if payment_uploads table doesn't exist
    }
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        json_response(['ok' => true, 'message' => 'Payment confirmed']);
    }
    header('Location: /admin/orders/' . $orderId);
    exit;
}

// POST /admin/orders/{id}/decline-payment
if (preg_match('#^/admin/orders/(\d+)/decline-payment$#', $path, $m) && $method === 'POST') {
    require_admin();
    $orderId = (int)$m[1];
    $reason = $_POST['decline_reason'] ?? 'other';
    $notes = $_POST['decline_notes'] ?? '';
    
    $reasonLabels = [
        'invalid_receipt' => 'Invalid or unreadable receipt',
        'amount_mismatch' => 'Payment amount doesn\'t match order total',
        'payment_not_received' => 'Payment not received in bank account',
        'duplicate_submission' => 'Duplicate submission',
        'fraudulent' => 'Suspected fraudulent transaction',
        'other' => 'Other reason',
    ];
    
    $declineMessage = $reasonLabels[$reason] ?? $reason;
    if (!empty($notes)) {
        $declineMessage .= ': ' . $notes;
    }
    
    // Update order status to payment_declined
    $pdo->prepare('UPDATE orders SET status = ?, payment_declined_at = NOW(), payment_declined_by = ?, decline_reason = ? WHERE id = ?')
        ->execute(['payment_declined', $_SESSION['user_id'], $declineMessage, $orderId]);
    
    // Update payment upload status
    $pdo->prepare('UPDATE payment_uploads SET status = ?, reviewed_by = ?, reviewed_at = NOW(), decline_reason = ? WHERE order_id = ? AND status = ?')
        ->execute(['declined', $_SESSION['user_id'], $declineMessage, $orderId, 'pending']);
    
    header('Location: /admin/orders/' . $orderId);
    exit;
}

// POST /admin/orders/{id}/revert-to-awaiting
if (preg_match('#^/admin/orders/(\d+)/revert-to-awaiting$#', $path, $m) && $method === 'POST') {
    require_admin();
    $orderId = (int)$m[1];
    
    // Revert order status to awaiting_payment so customer can re-upload
    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
        ->execute(['awaiting_payment', $orderId]);
    
    header('Location: /admin/orders/' . $orderId);
    exit;
}

// POST /admin/orders/{id}/ship
if (preg_match('#^/admin/orders/(\d+)/ship$#', $path, $m) && $method === 'POST') {
    require_admin();
    $orderId = (int)$m[1];
    $data = $_POST;
    
    $trackingNumber = !empty($data['tracking_number']) ? $data['tracking_number'] : generate_tracking_number();
    $carrier = 'Streicher Logistics';
    $shippingMethod = $data['shipping_method'] ?? 'air_freight';
    $packageType = $data['package_type'] ?? 'crate';
    
    // Shipping method descriptions
    $methodDescriptions = [
        'air_freight' => 'Air Freight - International Express',
        'sea_freight' => 'Sea Freight - Heavy Cargo',
        'local_van' => 'Local Van Delivery',
        'motorcycle' => 'Motorcycle Courier Express',
    ];
    
    // Create shipment (minimal columns for production DB)
    $stmt = $pdo->prepare(
        'INSERT INTO shipments (order_id, carrier, tracking_number, status)
         VALUES (?, ?, ?, ?)'
    );
    
    $stmt->execute([
        $orderId,
        $carrier,
        $trackingNumber,
        'shipped',
    ]);
    
    // Update order (simplified - avoid shipped_at column)
    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
        ->execute(['shipped', $orderId]);
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        json_response(['ok' => true, 'tracking_number' => $trackingNumber]);
    }
    header('Location: /admin/orders/' . $orderId);
    exit;
}

// POST /admin/shipments/{id}/update-tracking
if (preg_match('#^/admin/shipments/(\d+)/update-tracking$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    $data = $_POST;
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        json_response(['error' => 'Shipment not found'], 404);
    }
    
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    
    // Status descriptions mapping
    $statusDescriptions = [
        'picked_up' => 'Shipment picked up from warehouse',
        'in_transit' => 'Shipment in transit',
        'arrived_hub' => 'Arrived at sorting hub',
        'departed_hub' => 'Departed from sorting hub',
        'customs_hold' => 'Shipment held at customs for inspection',
        'customs_cleared' => 'Customs clearance completed - shipment released',
        'arrived_destination' => 'Arrived at destination country',
        'out_for_delivery' => 'Out for delivery',
        'delivery_attempted' => 'Delivery attempted - recipient not available',
        'delivered' => 'Shipment delivered successfully',
    ];
    
    $statusCode = $data['status'] ?? 'in_transit';
    $description = !empty($data['description']) ? $data['description'] : ($statusDescriptions[$statusCode] ?? 'Status update');
    
    // Add new event
    $newEvent = [
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => strtoupper(str_replace('_', ' ', $statusCode)),
        'description' => $description,
        'location' => $data['location'] ?? '',
        'facility' => $data['facility'] ?? '',
    ];
    
    array_unshift($events, $newEvent);
    
    // Update shipment
    $newStatus = strtolower($data['status'] ?? $shipment['status']);
    
    $stmt = $pdo->prepare('UPDATE shipments SET status = ?, events = ? WHERE id = ?');
    $stmt->execute([$newStatus, json_encode($events), $shipmentId]);
    
    // If delivered, update order
    if ($newStatus === 'delivered') {
        $pdo->prepare('UPDATE orders SET status = ?, delivered_at = NOW() WHERE id = ?')
            ->execute(['delivered', $shipment['order_id']]);
        
        $pdo->prepare('UPDATE shipments SET actual_delivery = NOW() WHERE id = ?')
            ->execute([$shipmentId]);
    }
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        json_response(['ok' => true]);
    }
    header('Location: /admin/orders/' . $shipment['order_id']);
    exit;
}

// POST /admin/shipments/{id}/customs-hold - Set customs hold status
if (preg_match('#^/admin/shipments/(\d+)/customs-hold$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    $data = $_POST;
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        json_response(['error' => 'Shipment not found'], 404);
    }
    
    $customsMemo = $data['customs_memo'] ?? '';
    $dutyAmount = !empty($data['duty_amount']) ? (float)$data['duty_amount'] : null;
    $dutyCurrency = $data['duty_currency'] ?? 'EUR';
    
    // Update shipment with customs hold
    $stmt = $pdo->prepare(
        'UPDATE shipments SET status = ?, customs_status = ?, customs_memo = ?, customs_duty_amount = ?, customs_duty_currency = ? WHERE id = ?'
    );
    $stmt->execute(['customs_hold', 'held', $customsMemo, $dutyAmount, $dutyCurrency, $shipmentId]);
    
    // Add tracking event
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    array_unshift($events, [
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'CUSTOMS_HOLD',
        'description' => 'Shipment held by customs - clearance required',
        'location' => $data['location'] ?? 'Customs Facility',
    ]);
    $pdo->prepare('UPDATE shipments SET events = ? WHERE id = ?')
        ->execute([json_encode($events), $shipmentId]);
    
    // Add system message to communications
    $pdo->prepare(
        'INSERT INTO tracking_communications (tracking_number, sender_type, message_type, message) VALUES (?, ?, ?, ?)'
    )->execute([
        $shipment['tracking_number'],
        'system',
        'status_update',
        "Your shipment has been held by customs authorities.\n\nReason: " . $customsMemo . 
        ($dutyAmount ? "\n\nDuty Amount: " . number_format($dutyAmount, 2) . " " . $dutyCurrency : ''),
    ]);
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        json_response(['ok' => true]);
    }
    header('Location: /admin/orders/' . $shipment['order_id']);
    exit;
}

// POST /admin/shipments/{id}/clear-customs - Clear customs hold
if (preg_match('#^/admin/shipments/(\d+)/clear-customs$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        json_response(['error' => 'Shipment not found'], 404);
    }
    
    // Update shipment
    $stmt = $pdo->prepare('UPDATE shipments SET status = ?, customs_status = ? WHERE id = ?');
    $stmt->execute(['in_transit', 'cleared', $shipmentId]);
    
    // Add tracking event
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    array_unshift($events, [
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'CUSTOMS_CLEARED',
        'description' => 'Customs clearance completed - shipment released',
        'location' => $_POST['location'] ?? 'Customs Facility',
    ]);
    $pdo->prepare('UPDATE shipments SET events = ? WHERE id = ?')
        ->execute([json_encode($events), $shipmentId]);
    
    // Add system message
    $pdo->prepare(
        'INSERT INTO tracking_communications (tracking_number, sender_type, message_type, message) VALUES (?, ?, ?, ?)'
    )->execute([
        $shipment['tracking_number'],
        'system',
        'status_update',
        'Good news! Your shipment has cleared customs and is now back in transit.',
    ]);
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        json_response(['ok' => true]);
    }
    header('Location: /admin/orders/' . $shipment['order_id']);
    exit;
}

// POST /admin/shipments/{id}/send-message - Admin sends message/document via shipment ID
if (preg_match('#^/admin/shipments/(\d+)/send-message$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    
    // Get shipment to find tracking number and order ID
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        header('Location: /admin/orders');
        exit;
    }
    
    $trackingNumber = $shipment['tracking_number'];
    $message = $_POST['message'] ?? '';
    $documentPath = null;
    $documentName = null;
    $documentType = null;
    $messageType = 'message';
    
    // Handle file upload
    if (!empty($_FILES['document']['name'])) {
        $uploadDir = __DIR__ . '/uploads/tracking/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $safeName = $trackingNumber . '_admin_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $safeName;
        
        if (move_uploaded_file($_FILES['document']['tmp_name'], $destPath)) {
            $documentPath = 'uploads/tracking/' . $safeName;
            $documentName = $_FILES['document']['name'];
            $documentType = $_FILES['document']['type'];
            $messageType = !empty($message) ? 'message' : 'document';
        }
    }
    
    if (empty($message) && empty($documentPath)) {
        header('Location: /admin/orders/' . $shipment['order_id'] . '?error=empty_message');
        exit;
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO tracking_communications (tracking_number, sender_type, sender_name, message_type, message, document_name, document_path, document_type) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $trackingNumber,
        'admin',
        $_SESSION['user_name'] ?? 'Streicher Logistics',
        $messageType,
        $message ?: null,
        $documentName,
        $documentPath,
        $documentType,
    ]);
    
    header('Location: /admin/orders/' . $shipment['order_id'] . '#communications-' . $shipmentId);
    exit;
}

// POST /admin/tracking/{tracking_number}/message - Admin sends message/document
if (preg_match('#^/admin/tracking/([A-Za-z0-9]+)/message$#', $path, $m) && $method === 'POST') {
    require_admin();
    $trackingNumber = $m[1];
    
    $message = $_POST['message'] ?? '';
    $documentPath = null;
    $documentName = null;
    $documentType = null;
    $messageType = 'message';
    
    // Handle file upload
    if (!empty($_FILES['document']['name'])) {
        $uploadDir = __DIR__ . '/uploads/tracking/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $safeName = $trackingNumber . '_admin_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $safeName;
        
        if (move_uploaded_file($_FILES['document']['tmp_name'], $destPath)) {
            $documentPath = '/uploads/tracking/' . $safeName;
            $documentName = $_FILES['document']['name'];
            $documentType = $_FILES['document']['type'];
            $messageType = 'document';
        }
    }
    
    if (empty($message) && empty($documentPath)) {
        json_response(['error' => 'Message or document required'], 400);
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO tracking_communications (tracking_number, sender_type, sender_name, message_type, message, document_name, document_path, document_type) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $trackingNumber,
        'admin',
        $_SESSION['user_name'] ?? 'Streicher Logistics',
        $messageType,
        $message ?: null,
        $documentName,
        $documentPath,
        $documentType,
    ]);
    
    json_response(['ok' => true, 'message' => 'Message sent successfully']);
}

// ============ HTML ROUTES ============

// GET / - Homepage with featured products
if ($path === '/' && $method === 'GET') {
    $stmt = $pdo->query(
        'SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.is_active = 1 
         ORDER BY p.is_featured DESC, p.created_at DESC 
         LIMIT 12'
    );
    $products = $stmt->fetchAll();
    
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY sort_order');
    $categories = $stmt->fetchAll();
    
    render_template('home.php', [
        'title' => 'Streicher GmbH - Industrial Parts & Equipment',
        'products' => $products,
        'categories' => $categories,
        'isHomePage' => true,
    ]);
}

// GET /catalog - Product catalog
if ($path === '/catalog' && $method === 'GET') {
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    
    // Handle currency switching
    if (isset($_GET['currency']) && in_array($_GET['currency'], ['EUR', 'USD'])) {
        $_SESSION['display_currency'] = $_GET['currency'];
    }
    
    $sql = 'SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1';
    $params = [];
    
    if ($category) {
        $sql .= ' AND c.slug = ?';
        $params[] = $category;
    }
    if ($search) {
        $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_desc LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= ' ORDER BY p.is_featured DESC, p.unit_price DESC';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY sort_order');
    $categories = $stmt->fetchAll();
    
    $currentCategory = null;
    if ($category) {
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$category]);
        $currentCategory = $stmt->fetch();
    }
    
    render_template('catalog.php', [
        'title' => ($currentCategory ? $currentCategory['name'] . ' - ' : '') . 'Product Catalog - Streicher',
        'products' => $products,
        'categories' => $categories,
        'currentCategory' => $currentCategory,
        'search' => $search,
    ]);
}

// GET /product - Product detail
if ($path === '/product' && $method === 'GET') {
    $sku = $_GET['sku'] ?? null;
    
    if (!$sku) {
        header('Location: /catalog');
        exit;
    }
    
    $stmt = $pdo->prepare(
        'SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.sku = ?'
    );
    $stmt->execute([$sku]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        render_template('404.php', ['title' => 'Product Not Found']);
    }
    
    // Get related products
    $stmt = $pdo->prepare(
        'SELECT * FROM products 
         WHERE category_id = ? AND sku != ? AND is_active = 1 
         ORDER BY RAND() LIMIT 4'
    );
    $stmt->execute([$product['category_id'], $sku]);
    $relatedProducts = $stmt->fetchAll();
    
    render_template('product_detail.php', [
        'title' => $product['name'] . ' - Streicher',
        'product' => $product,
        'relatedProducts' => $relatedProducts,
    ]);
}

// GET /cart - Shopping cart
if ($path === '/cart' && $method === 'GET') {
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    
    render_template('cart.php', [
        'title' => 'Shopping Cart - Streicher',
        'cart' => $cart,
        'total' => $total,
    ]);
}

// GET /checkout - Checkout page
if ($path === '/checkout' && $method === 'GET') {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header('Location: /cart');
        exit;
    }
    
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    
    // Load settings from database for bank details
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $settingsRows = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsRows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    render_template('checkout.php', [
        'title' => 'Checkout - Streicher',
        'cart' => $cart,
        'total' => $total,
        'settings' => $settings,
    ]);
}

// POST /checkout - Process checkout (form submit)
if ($path === '/checkout' && $method === 'POST') {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header('Location: /cart');
        exit;
    }
    
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    
    $orderNumber = generate_order_number();
    
    $stmt = $pdo->prepare(
        'INSERT INTO orders (user_id, order_number, status, total_amount, currency, billing_address, shipping_address, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    
    $billingAddress = [
        'company' => $_POST['company'] ?? '',
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'zip' => $_POST['zip'] ?? '',
        'country' => $_POST['country'] ?? 'Germany',
    ];
    
    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        $orderNumber,
        'awaiting_payment',
        $total,
        'EUR',
        json_encode($billingAddress),
        json_encode($billingAddress),
        $_POST['notes'] ?? null,
    ]);
    
    $orderId = (int)$pdo->lastInsertId();
    
    $stmtItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
         VALUES (?, ?, ?, ?, ?)'
    );
    
    foreach ($cart as $item) {
        $stmt = $pdo->prepare('SELECT id FROM products WHERE sku = ?');
        $stmt->execute([$item['sku']]);
        $productId = (int)$stmt->fetchColumn();
        
        $stmtItem->execute([
            $orderId,
            $productId,
            $item['qty'],
            $item['price'],
            $item['price'] * $item['qty'],
        ]);
    }
    
    $_SESSION['cart'] = [];
    
    header('Location: /order/' . $orderId . '/payment');
    exit;
}

// GET /order/{id}/payment - Payment upload page
if (preg_match('#^/order/(\d+)/payment$#', $path, $m) && $method === 'GET') {
    $orderId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        render_template('404.php', ['title' => 'Order Not Found']);
    }
    
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    $stmt = $pdo->prepare('SELECT * FROM payment_uploads WHERE order_id = ? ORDER BY created_at DESC');
    $stmt->execute([$orderId]);
    $uploads = $stmt->fetchAll();
    
    render_template('order_payment.php', [
        'title' => 'Upload Payment - Order ' . $order['order_number'],
        'order' => $order,
        'items' => $items,
        'uploads' => $uploads,
    ]);
}

// POST /order/{id}/payment - Upload payment receipt
if (preg_match('#^/order/(\d+)/payment$#', $path, $m) && $method === 'POST') {
    $orderId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo 'Order not found';
        exit;
    }
    
    if (empty($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        header('Location: /order/' . $orderId . '/payment?error=upload_failed');
        exit;
    }
    
    $file = $_FILES['receipt'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        header('Location: /order/' . $orderId . '/payment?error=invalid_type');
        exit;
    }
    
    $uploadDir = __DIR__ . '/../uploads/payments/' . $orderId;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        header('Location: /order/' . $orderId . '/payment?error=save_failed');
        exit;
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO payment_uploads (order_id, filename, original_filename, file_path, file_size, mime_type, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $orderId,
        $filename,
        $file['name'],
        'uploads/payments/' . $orderId . '/' . $filename,
        $file['size'],
        $file['type'],
        $_POST['notes'] ?? null,
    ]);
    
    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
        ->execute(['payment_uploaded', $orderId]);
    
    header('Location: /order/' . $orderId . '/confirmation');
    exit;
}

// GET /order/{id}/confirmation - Order confirmation
if (preg_match('#^/order/(\d+)/confirmation$#', $path, $m) && $method === 'GET') {
    $orderId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        render_template('404.php', ['title' => 'Order Not Found']);
    }
    
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    render_template('order_confirmation.php', [
        'title' => 'Order Confirmation - ' . $order['order_number'],
        'order' => $order,
        'items' => $items,
    ]);
}

// GET /order/{id} - Order status page
if (preg_match('#^/order/(\d+)$#', $path, $m) && $method === 'GET') {
    $orderId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        render_template('404.php', ['title' => 'Order Not Found']);
    }
    
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $shipments = $stmt->fetchAll();
    
    render_template('order_status.php', [
        'title' => 'Order ' . $order['order_number'] . ' - Streicher',
        'order' => $order,
        'items' => $items,
        'shipments' => $shipments,
    ]);
}

// GET /track - Tracking page
if ($path === '/track' && $method === 'GET') {
    $trackingNumber = $_GET['tracking'] ?? null;
    $shipment = null;
    $events = [];
    $communications = [];
    $unreadCount = 0;
    
    if ($trackingNumber) {
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE tracking_number = ?');
        $stmt->execute([$trackingNumber]);
        $shipment = $stmt->fetch();
        
        if ($shipment) {
            $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
            
            // Fetch communications for this tracking number
            $stmt = $pdo->prepare('SELECT * FROM tracking_communications WHERE tracking_number = ? ORDER BY created_at ASC');
            $stmt->execute([$trackingNumber]);
            $communications = $stmt->fetchAll();
            
            // Count unread messages from admin
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM tracking_communications WHERE tracking_number = ? AND sender_type = "admin" AND is_read = 0');
            $stmt->execute([$trackingNumber]);
            $unreadCount = (int)$stmt->fetchColumn();
            
            // Mark customer-viewed messages as read
            $pdo->prepare('UPDATE tracking_communications SET is_read = 1 WHERE tracking_number = ? AND sender_type = "admin"')
                ->execute([$trackingNumber]);
        }
    }
    
    render_template('tracking.php', [
        'title' => 'Track Shipment - Streicher',
        'trackingNumber' => $trackingNumber,
        'shipment' => $shipment,
        'events' => $events,
        'communications' => $communications,
        'unreadCount' => $unreadCount,
    ]);
}

// ============ ADMIN ROUTES ============

// GET /admin/login
if ($path === '/admin/login' && $method === 'GET') {
    render_template('admin_login.php', ['title' => 'Admin Login']);
}

// POST /admin/login
if ($path === '/admin/login' && $method === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Rate limiting: Check failed attempts in last 15 minutes
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = 0');
    $stmt->execute([$clientIp]);
    $failedAttempts = (int)$stmt->fetchColumn();
    
    if ($failedAttempts >= 5) {
        render_template('admin_login.php', [
            'title' => 'Admin Login',
            'error' => 'Too many failed attempts. Please try again in 15 minutes.',
        ]);
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
        // Log successful attempt
        $stmt = $pdo->prepare('INSERT INTO login_attempts (ip_address, email, success) VALUES (?, ?, 1)');
        $stmt->execute([$clientIp, $email]);
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: /admin');
        exit;
    }
    
    // Log failed attempt
    $stmt = $pdo->prepare('INSERT INTO login_attempts (ip_address, email, success) VALUES (?, ?, 0)');
    $stmt->execute([$clientIp, $email]);
    
    render_template('admin_login.php', [
        'title' => 'Admin Login',
        'error' => 'Invalid credentials',
    ]);
}

// GET /admin/logout
if ($path === '/admin/logout' || $path === '/logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// GET /admin - Dashboard
if ($path === '/admin' && $method === 'GET') {
    require_admin();
    
    // Get stats
    $stats = [];
    $stats['total_orders'] = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $stats['pending_payments'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'payment_uploaded'")->fetchColumn();
    $stats['total_revenue'] = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status NOT IN ('cancelled', 'awaiting_payment')")->fetchColumn();
    $stats['total_products'] = $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
    
    // Recent orders
    $stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 10');
    $recentOrders = $stmt->fetchAll();
    
    // Pending payment orders
    $stmt = $pdo->query("SELECT * FROM orders WHERE status = 'payment_uploaded' ORDER BY created_at DESC LIMIT 5");
    $pendingPayments = $stmt->fetchAll();
    
    render_admin_template('dashboard.php', [
        'title' => 'Admin Dashboard - Streicher',
        'stats' => $stats,
        'recentOrders' => $recentOrders,
        'pendingPayments' => $pendingPayments,
    ]);
}

// GET /admin/orders
if ($path === '/admin/orders' && $method === 'GET') {
    require_admin();
    
    $status = $_GET['status'] ?? null;
    
    $sql = 'SELECT * FROM orders';
    $params = [];
    
    if ($status) {
        $sql .= ' WHERE status = ?';
        $params[] = $status;
    }
    
    $sql .= ' ORDER BY created_at DESC';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    render_admin_template('orders.php', [
        'title' => 'Orders - Admin',
        'orders' => $orders,
        'currentStatus' => $status,
    ]);
}

// GET /admin/orders/{id}
if (preg_match('#^/admin/orders/(\d+)$#', $path, $m) && $method === 'GET') {
    require_admin();
    $orderId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: /admin/orders');
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    $stmt = $pdo->prepare('SELECT * FROM payment_uploads WHERE order_id = ? ORDER BY created_at DESC');
    $stmt->execute([$orderId]);
    $paymentUploads = $stmt->fetchAll();
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $shipments = $stmt->fetchAll();
    
    render_admin_template('order_detail.php', [
        'title' => 'Order ' . $order['order_number'] . ' - Admin',
        'order' => $order,
        'items' => $items,
        'paymentUploads' => $paymentUploads,
        'shipments' => $shipments,
    ]);
}

// GET /admin/products
if ($path === '/admin/products' && $method === 'GET') {
    require_admin();
    
    $stmt = $pdo->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
    $products = $stmt->fetchAll();
    
    render_admin_template('products.php', [
        'title' => 'Products - Admin',
        'products' => $products,
    ]);
}

// GET /admin/products/new
if ($path === '/admin/products/new' && $method === 'GET') {
    require_admin();
    
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY sort_order');
    $categories = $stmt->fetchAll();
    
    render_admin_template('product_form.php', [
        'title' => 'New Product - Admin',
        'categories' => $categories,
        'product' => null,
    ]);
}

// POST /admin/products/new
if ($path === '/admin/products/new' && $method === 'POST') {
    require_admin();
    
    $stmt = $pdo->prepare(
        'INSERT INTO products (sku, category_id, name, short_desc, description, long_description, unit_price, features, specifications, weight_kg, warranty_months, moq, manufacturer, is_featured, is_active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    
    $stmt->execute([
        $_POST['sku'],
        $_POST['category_id'] ?: null,
        $_POST['name'],
        $_POST['short_desc'],
        $_POST['description'] ?? '',
        $_POST['long_description'] ?? '',
        $_POST['unit_price'],
        json_encode(array_filter(explode("\n", $_POST['features'] ?? ''))),
        json_encode([]),
        $_POST['weight_kg'] ?: null,
        $_POST['warranty_months'] ?: 12,
        $_POST['moq'] ?: 1,
        $_POST['manufacturer'] ?? 'Streicher',
        isset($_POST['is_featured']) ? 1 : 0,
        isset($_POST['is_active']) ? 1 : 0,
    ]);
    
    header('Location: /admin/products');
    exit;
}

// GET /admin/products/{id}/edit - Edit product form
if (preg_match('#^/admin/products/(\d+)/edit$#', $path, $m) && $method === 'GET') {
    require_admin();
    $productId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo 'Product not found';
        exit;
    }
    
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY sort_order');
    $categories = $stmt->fetchAll();
    
    render_admin_template('product_form.php', [
        'title' => 'Edit Product - Admin',
        'product' => $product,
        'categories' => $categories,
    ]);
}

// POST /admin/products/{id}/edit - Update product
if (preg_match('#^/admin/products/(\d+)/edit$#', $path, $m) && $method === 'POST') {
    require_admin();
    $productId = (int)$m[1];
    
    // Handle image upload
    $imageUrl = null;
    if (!empty($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($ext, $allowedExts)) {
            $filename = 'product-' . $productId . '-' . time() . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
                $imageUrl = '/images/products/' . $filename;
            }
        }
    }
    
    // Build update query
    $fields = [
        'sku = ?',
        'category_id = ?',
        'name = ?',
        'short_desc = ?',
        'description = ?',
        'long_description = ?',
        'unit_price = ?',
        'features = ?',
        'weight_kg = ?',
        'warranty_months = ?',
        'moq = ?',
        'manufacturer = ?',
        'is_featured = ?',
        'is_active = ?',
    ];
    
    $params = [
        $_POST['sku'],
        $_POST['category_id'] ?: null,
        $_POST['name'],
        $_POST['short_desc'],
        $_POST['description'] ?? '',
        $_POST['long_description'] ?? '',
        $_POST['unit_price'],
        json_encode(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')))),
        $_POST['weight_kg'] ?: null,
        $_POST['warranty_months'] ?: 12,
        $_POST['moq'] ?: 1,
        $_POST['manufacturer'] ?? 'Streicher',
        isset($_POST['is_featured']) ? 1 : 0,
        isset($_POST['is_active']) ? 1 : 0,
    ];
    
    // Add image_url if uploaded
    if ($imageUrl) {
        $fields[] = 'image_url = ?';
        $params[] = $imageUrl;
    }
    
    $params[] = $productId;
    
    $stmt = $pdo->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($params);
    
    header('Location: /admin/products');
    exit;
}

// POST /admin/products/{id}/delete - Delete product
if (preg_match('#^/admin/products/(\d+)/delete$#', $path, $m) && $method === 'POST') {
    require_admin();
    $productId = (int)$m[1];
    
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    
    header('Location: /admin/products');
    exit;
}

// GET /admin/tickets - Support tickets
if ($path === '/admin/tickets' && $method === 'GET') {
    require_admin();
    
    $status = $_GET['status'] ?? null;
    $sql = 'SELECT * FROM support_tickets ORDER BY created_at DESC';
    if ($status) {
        $sql = 'SELECT * FROM support_tickets WHERE status = ? ORDER BY created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query($sql);
    }
    $tickets = $stmt->fetchAll();
    
    render_admin_template('tickets.php', [
        'title' => 'Support Tickets - Admin',
        'tickets' => $tickets,
        'currentStatus' => $status,
    ]);
}

// GET /admin/tickets/{id} - View single ticket
if (preg_match('#^/admin/tickets/(\d+)$#', $path, $m) && $method === 'GET') {
    require_admin();
    $ticketId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE id = ?');
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        http_response_code(404);
        echo 'Ticket not found';
        exit;
    }
    
    render_admin_template('ticket_detail.php', [
        'title' => 'Ticket #' . $ticket['ticket_number'] . ' - Admin',
        'ticket' => $ticket,
    ]);
}

// POST /admin/tickets/{id}/update - Update ticket status/notes
if (preg_match('#^/admin/tickets/(\d+)/update$#', $path, $m) && $method === 'POST') {
    require_admin();
    $ticketId = (int)$m[1];
    
    $status = $_POST['status'] ?? 'new';
    $adminNotes = $_POST['admin_notes'] ?? '';
    
    $stmt = $pdo->prepare('UPDATE support_tickets SET status = ?, admin_notes = ? WHERE id = ?');
    $stmt->execute([$status, $adminNotes, $ticketId]);
    
    header('Location: /admin/tickets/' . $ticketId);
    exit;
}

// GET /admin/shipments - All shipments
if ($path === '/admin/shipments' && $method === 'GET') {
    require_admin();
    
    $status = $_GET['status'] ?? null;
    $sql = 'SELECT s.*, o.order_number, o.billing_address 
            FROM shipments s 
            LEFT JOIN orders o ON s.order_id = o.id 
            ORDER BY s.created_at DESC';
    
    $stmt = $pdo->query($sql);
    $shipments = $stmt->fetchAll();
    
    render_admin_template('shipments.php', [
        'title' => 'Shipments - Admin',
        'shipments' => $shipments,
    ]);
}

// GET /admin/shipments/create - Create manual shipment form
if ($path === '/admin/shipments/create' && $method === 'GET') {
    require_admin();
    
    render_admin_template('shipment_create.php', [
        'title' => 'Create Manual Shipment - Admin',
    ]);
}

// POST /admin/shipments/create - Create manual shipment
if ($path === '/admin/shipments/create' && $method === 'POST') {
    require_admin();
    $data = $_POST;
    
    $trackingNumber = !empty($data['tracking_number']) ? $data['tracking_number'] : generate_tracking_number();
    $carrier = $data['carrier'] ?? 'Streicher Logistics';
    $shippingMethod = $data['shipping_method'] ?? 'air_freight';
    $packageType = $data['package_type'] ?? 'crate';
    $status = $data['status'] ?? 'pending';
    $shippedAt = !empty($data['shipped_at']) ? $data['shipped_at'] : null;
    
    // Create initial tracking event
    $initialEvents = [[
        'timestamp' => !empty($data['shipped_at']) ? $data['shipped_at'] : date('Y-m-d H:i:s'),
        'status' => strtoupper($status),
        'description' => $data['initial_description'] ?? 'Shipment created',
        'location' => $data['initial_location'] ?? 'Regensburg, Germany',
        'facility' => $data['initial_facility'] ?? 'Streicher Logistics Center',
    ]];
    
    // Create customer info JSON
    $customerInfo = json_encode([
        'name' => $data['customer_name'] ?? '',
        'email' => $data['customer_email'] ?? '',
        'phone' => $data['customer_phone'] ?? '',
        'company' => $data['customer_company'] ?? '',
    ]);
    
    // Insert shipment (order_id is NULL for manual shipments)
    $stmt = $pdo->prepare(
        'INSERT INTO shipments (order_id, carrier, tracking_number, status, shipped_at, origin_city, origin_country, destination_city, destination_country, shipping_method, package_type, events, customer_info, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    
    $stmt->execute([
        null, // No order_id for manual shipments
        $carrier,
        $trackingNumber,
        $status,
        $shippedAt,
        $data['origin_city'] ?? 'Regensburg',
        $data['origin_country'] ?? 'DE',
        $data['destination_city'] ?? '',
        $data['destination_country'] ?? '',
        $shippingMethod,
        $packageType,
        json_encode($initialEvents),
        $customerInfo,
        $data['notes'] ?? null,
    ]);
    
    $_SESSION['success_message'] = 'Manual shipment created successfully!';
    header('Location: /admin/shipments');
    exit;
}

// GET /admin/shipments/{id}/edit - Edit shipment
if (preg_match('#^/admin/shipments/(\d+)/edit$#', $path, $m) && $method === 'GET') {
    require_admin();
    $shipmentId = (int)$m[1];
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        header('Location: /admin/shipments');
        exit;
    }
    
    render_admin_template('shipment_edit.php', [
        'title' => 'Edit Shipment - Admin',
        'shipment' => $shipment,
    ]);
}

// POST /admin/shipments/{id}/update-info - Update shipment basic info
if (preg_match('#^/admin/shipments/(\d+)/update-info$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    $data = $_POST;
    
    $stmt = $pdo->prepare(
        'UPDATE shipments SET carrier = ?, status = ?, shipped_at = ? WHERE id = ?'
    );
    $stmt->execute([
        $data['carrier'] ?? 'Streicher Logistics',
        $data['status'] ?? 'pending',
        !empty($data['shipped_at']) ? $data['shipped_at'] : null,
        $shipmentId,
    ]);
    
    $_SESSION['success_message'] = 'Shipment information updated!';
    header('Location: /admin/shipments/' . $shipmentId . '/edit');
    exit;
}

// POST /admin/shipments/{id}/add-event - Add tracking event
if (preg_match('#^/admin/shipments/(\d+)/add-event$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    $data = $_POST;
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        header('Location: /admin/shipments');
        exit;
    }
    
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    
    // Add new event at the beginning (most recent first)
    $newEvent = [
        'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s'),
        'status' => $data['status_code'] ?? 'UPDATE',
        'description' => $data['description'] ?? '',
        'location' => $data['location'] ?? '',
        'facility' => $data['facility'] ?? '',
    ];
    
    array_unshift($events, $newEvent);
    
    // Update shipment
    $stmt = $pdo->prepare('UPDATE shipments SET events = ? WHERE id = ?');
    $stmt->execute([json_encode($events), $shipmentId]);
    
    $_SESSION['success_message'] = 'Tracking event added!';
    header('Location: /admin/shipments/' . $shipmentId . '/edit');
    exit;
}

// POST /admin/shipments/{id}/edit-event - Edit tracking event
if (preg_match('#^/admin/shipments/(\d+)/edit-event$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    $data = $_POST;
    $eventIndex = (int)($data['event_index'] ?? -1);
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        header('Location: /admin/shipments');
        exit;
    }
    
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    
    if (isset($events[$eventIndex])) {
        $events[$eventIndex] = [
            'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s'),
            'status' => $data['status_code'] ?? 'UPDATE',
            'description' => $data['description'] ?? '',
            'location' => $data['location'] ?? '',
            'facility' => $data['facility'] ?? '',
        ];
        
        // Update shipment
        $stmt = $pdo->prepare('UPDATE shipments SET events = ? WHERE id = ?');
        $stmt->execute([json_encode($events), $shipmentId]);
        
        $_SESSION['success_message'] = 'Tracking event updated!';
    }
    
    header('Location: /admin/shipments/' . $shipmentId . '/edit');
    exit;
}

// POST /admin/shipments/{id}/delete-event - Delete tracking event
if (preg_match('#^/admin/shipments/(\d+)/delete-event$#', $path, $m) && $method === 'POST') {
    require_admin();
    $shipmentId = (int)$m[1];
    $data = $_POST;
    $eventIndex = (int)($data['event_index'] ?? -1);
    
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->execute([$shipmentId]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        header('Location: /admin/shipments');
        exit;
    }
    
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    
    if (isset($events[$eventIndex])) {
        array_splice($events, $eventIndex, 1);
        
        // Update shipment
        $stmt = $pdo->prepare('UPDATE shipments SET events = ? WHERE id = ?');
        $stmt->execute([json_encode($events), $shipmentId]);
        
        $_SESSION['success_message'] = 'Tracking event deleted!';
    }
    
    header('Location: /admin/shipments/' . $shipmentId . '/edit');
    exit;
}

// GET /admin/customers - All customers
if ($path === '/admin/customers' && $method === 'GET') {
    require_admin();
    
    // Get unique customers from orders
    $stmt = $pdo->query(
        "SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.email')) as email,
            MAX(JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.company'))) as company,
            MAX(JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.name'))) as name,
            MAX(JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.phone'))) as phone,
            MAX(JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.country'))) as country,
            COUNT(*) as order_count,
            SUM(total_amount) as total_spent,
            MAX(created_at) as last_order
         FROM orders 
         GROUP BY JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.email'))
         ORDER BY last_order DESC"
    );
    $customers = $stmt->fetchAll();
    
    render_admin_template('customers.php', [
        'title' => 'Customers - Admin',
        'customers' => $customers,
    ]);
}

// GET /admin/reports - Reports dashboard
if ($path === '/admin/reports' && $method === 'GET') {
    require_admin();
    
    // Sales by month
    $stmt = $pdo->query(
        "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as order_count,
            SUM(total_amount) as revenue
         FROM orders 
         WHERE status NOT IN ('cancelled')
         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
         ORDER BY month DESC
         LIMIT 12"
    );
    $salesByMonth = $stmt->fetchAll();
    
    // Top products
    $stmt = $pdo->query(
        "SELECT 
            oi.sku,
            p.name as product_name,
            SUM(oi.qty) as total_qty,
            SUM(oi.total) as total_revenue
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE o.status NOT IN ('cancelled')
         GROUP BY oi.sku, p.name
         ORDER BY total_revenue DESC
         LIMIT 10"
    );
    $topProducts = $stmt->fetchAll();
    
    // Orders by status
    $stmt = $pdo->query(
        "SELECT status, COUNT(*) as count FROM orders GROUP BY status"
    );
    $ordersByStatus = $stmt->fetchAll();
    
    render_admin_template('reports.php', [
        'title' => 'Reports - Admin',
        'salesByMonth' => $salesByMonth,
        'topProducts' => $topProducts,
        'ordersByStatus' => $ordersByStatus,
    ]);
}

// GET /admin/settings - Settings page
if ($path === '/admin/settings' && $method === 'GET') {
    require_admin();
    
    // Load settings from database
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $settingsRows = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsRows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    render_admin_template('settings.php', [
        'title' => 'Settings - Admin',
        'settings' => $settings,
    ]);
}

// POST /admin/settings - Update settings
if ($path === '/admin/settings' && $method === 'POST') {
    require_admin();
    
    // Settings to save
    $settingsToSave = [
        'company_name', 'vat_id', 'address',
        'bank_name', 'account_holder', 'iban', 'bic',
        'support_email', 'support_phone', 'sales_email', 'shipping_email',
        'vat_rate', 'currency', 'free_shipping_threshold',
    ];
    
    // Update each setting in database
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($settingsToSave as $key) {
        if (isset($_POST[$key])) {
            $stmt->execute([$key, $_POST[$key]]);
        }
    }
    
    // Handle checkboxes (notifications)
    $checkboxes = ['notify_new_order', 'notify_payment', 'notify_low_stock'];
    foreach ($checkboxes as $key) {
        $value = isset($_POST[$key]) ? '1' : '0';
        $stmt->execute([$key, $value]);
    }
    
    header('Location: /admin/settings?saved=1');
    exit;
}

// ============ STATIC PAGES ============

// GET /contact - Contact page
if ($path === '/contact' && $method === 'GET') {
    render_template('pages/contact.php', ['title' => 'Contact Us - Streicher']);
}

// POST /contact - Contact form submission
if ($path === '/contact' && $method === 'POST') {
    $name = $_POST['name'] ?? '';
    $company = $_POST['company'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Generate ticket number
    $ticketNumber = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    
    // Save to support_tickets table
    $stmt = $pdo->prepare('INSERT INTO support_tickets (ticket_number, name, company, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$ticketNumber, $name, $company, $email, $phone, $subject, $message]);
    
    render_template('pages/contact.php', [
        'title' => 'Contact Us - Streicher',
        'success' => true,
        'ticketNumber' => $ticketNumber,
    ]);
}

// GET /quote - Request Quote page
if ($path === '/quote' && $method === 'GET') {
    $productSku = $_GET['product'] ?? null;
    $product = null;
    
    if ($productSku) {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE sku = ?');
        $stmt->execute([$productSku]);
        $product = $stmt->fetch();
    }
    
    render_template('pages/quote.php', [
        'title' => 'Request a Quote - Streicher',
        'product' => $product,
    ]);
}

// POST /quote - Quote form submission
if ($path === '/quote' && $method === 'POST') {
    $name = $_POST['name'] ?? '';
    $company = $_POST['company'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $country = $_POST['country'] ?? '';
    $category = $_POST['category'] ?? '';
    $quantity = $_POST['quantity'] ?? '1';
    $requirements = $_POST['requirements'] ?? '';
    $productSku = $_POST['product_sku'] ?? '';
    
    // Generate ticket number
    $ticketNumber = 'QTE-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    
    // Build message from quote details
    $message = "Quote Request\n\n";
    $message .= "Product Category: $category\n";
    $message .= "Quantity: $quantity\n";
    if ($productSku) {
        $message .= "Product SKU: $productSku\n";
    }
    $message .= "Country: $country\n\n";
    $message .= "Requirements:\n$requirements";
    
    // Save to support_tickets table
    $stmt = $pdo->prepare('INSERT INTO support_tickets (ticket_number, name, company, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$ticketNumber, $name, $company, $email, $phone, 'Quote Request - ' . $category, $message]);
    
    render_template('pages/quote.php', [
        'title' => 'Request a Quote - Streicher',
        'success' => true,
        'ticketNumber' => $ticketNumber,
    ]);
}

// GET /support - Support page
if ($path === '/support' && $method === 'GET') {
    render_template('pages/support.php', ['title' => 'Technical Support - Streicher']);
}

// GET /faq - FAQ page
if ($path === '/faq' && $method === 'GET') {
    render_template('pages/faq.php', ['title' => 'FAQ - Streicher']);
}

// GET /about - About page
if ($path === '/about' && $method === 'GET') {
    render_template('pages/about.php', ['title' => 'About Us - Streicher']);
}

// GET /profile - Company Profile page
if ($path === '/profile' && $method === 'GET') {
    render_template('pages/profile.php', ['title' => $lang === 'de' ? 'Unternehmensprofil - Streicher' : 'Company Profile - Streicher']);
}

// GET /news - News page
if ($path === '/news' && $method === 'GET') {
    render_template('pages/news.php', ['title' => $lang === 'de' ? 'Neuigkeiten - Streicher' : 'News - Streicher']);
}

// GET /mediathek - Media Library page
if ($path === '/mediathek' && $method === 'GET') {
    render_template('pages/mediathek.php', ['title' => $lang === 'de' ? 'Mediathek - Streicher' : 'Media Library - Streicher']);
}

// GET /business-sectors - Business Sectors page
if ($path === '/business-sectors' && $method === 'GET') {
    render_template('pages/business-sectors.php', ['title' => $lang === 'de' ? 'Geschäftsbereiche - Streicher' : 'Business Sectors - Streicher']);
}

// GET /reference-projects - Reference Projects page
if ($path === '/reference-projects' && $method === 'GET') {
    render_template('pages/reference-projects.php', ['title' => $lang === 'de' ? 'Referenzprojekte - Streicher' : 'Reference Projects - Streicher']);
}

// GET /hse-q - HSE-Q page
if ($path === '/hse-q' && $method === 'GET') {
    render_template('pages/hse-q.php', ['title' => 'HSE-Q - Streicher']);
}

// GET /events - Events page
if ($path === '/events' && $method === 'GET') {
    render_template('pages/events.php', ['title' => $lang === 'de' ? 'Veranstaltungen - Streicher' : 'Events - Streicher']);
}

// GET /careers - Careers page
if ($path === '/careers' && $method === 'GET') {
    render_template('pages/careers.php', ['title' => 'Careers - Streicher']);
}

// GET /privacy - Privacy Policy
if ($path === '/privacy' && $method === 'GET') {
    render_template('pages/privacy.php', ['title' => 'Privacy Policy - Streicher']);
}

// GET /terms - Terms & Conditions
if ($path === '/terms' && $method === 'GET') {
    render_template('pages/terms.php', ['title' => 'Terms & Conditions - Streicher']);
}

// GET /shipping - Shipping Information
if ($path === '/shipping' && $method === 'GET') {
    render_template('pages/shipping.php', ['title' => 'Shipping Information - Streicher']);
}

// GET /returns - Returns Policy
if ($path === '/returns' && $method === 'GET') {
    render_template('pages/returns.php', ['title' => 'Returns Policy - Streicher']);
}

// GET /login - Customer Login
if ($path === '/login' && $method === 'GET') {
    render_template('pages/login.php', ['title' => 'Login - Streicher']);
}

// POST /login - Customer Login
if ($path === '/login' && $method === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        header('Location: /account');
        exit;
    }
    
    render_template('pages/login.php', [
        'title' => 'Login - Streicher',
        'error' => 'Invalid email or password',
    ]);
}

// GET /register - Customer Registration
if ($path === '/register' && $method === 'GET') {
    render_template('pages/register.php', ['title' => 'Register - Streicher']);
}

// GET /account - Customer Account
if ($path === '/account' && $method === 'GET') {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
    
    render_template('pages/account.php', [
        'title' => 'My Account - Streicher',
        'orders' => $orders,
    ]);
}

// Fallback 404
http_response_code(404);
render_template('404.php', ['title' => 'Page Not Found']);
