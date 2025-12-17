<?php
declare(strict_types=1);

// EARLY HEALTH CHECK - before any requires that might fail
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestPath === '/health' || $requestPath === '/healthz') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'timestamp' => date('c')]);
    exit;
}

// Serve files from /assets/ path - MUST be early before any requires
if (preg_match('#^/assets/(.+)$#', $requestPath, $matches)) {
    $assetFile = $matches[1];
    $assetPath = __DIR__ . '/assets/' . $assetFile;
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
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];
        $ext = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($assetPath);
        exit;
    }
}

// Debug endpoint - before any requires
if ($requestPath === '/debug-db') {
    header('Content-Type: application/json');
    $debug = [
        'DATABASE_URL' => !empty(getenv('DATABASE_URL')) ? 'set' : 'not set',
        'DB_HOST' => getenv('DB_HOST') ?: 'not set',
        'DB_PORT' => getenv('DB_PORT') ?: 'not set',
        'DB_NAME' => getenv('DB_NAME') ?: 'not set',
        'DB_USER' => getenv('DB_USER') ?: 'not set',
    ];
    
    $testHost = getenv('DB_HOST') ?: 'mysql.railway.internal';
    $testPort = getenv('DB_PORT') ?: '3306';
    $testName = getenv('DB_NAME') ?: 'railway';
    $testUser = getenv('DB_USER') ?: 'root';
    $testPass = getenv('DB_PASS') ?: '';
    
    $debug['using_host'] = $testHost;
    $debug['using_port'] = $testPort;
    $debug['using_name'] = $testName;
    $debug['using_user'] = $testUser;
    
    try {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $testHost, $testPort);
        $pdo = new PDO($dsn, $testUser, $testPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        $debug['connection'] = 'SUCCESS';
        $debug['server_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '$testName'");
        $debug['database_exists'] = $stmt->rowCount() > 0;
        
        if ($debug['database_exists']) {
            $pdo->exec("USE `$testName`");
            $stmt = $pdo->query("SHOW TABLES");
            $debug['table_count'] = $stmt->rowCount();
        }
    } catch (PDOException $e) {
        $debug['connection'] = 'FAILED';
        $debug['error'] = $e->getMessage();
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

// EARLY SETUP DATABASE - before any requires that might fail
if ($requestPath === '/setup-database') {
    header('Content-Type: text/html; charset=utf-8');
    
    $setupDbHost = getenv('DB_HOST') ?: 'mysql.railway.internal';
    $setupDbPort = getenv('DB_PORT') ?: '3306';
    $setupDbName = getenv('DB_NAME') ?: 'railway';
    $setupDbUser = getenv('DB_USER') ?: 'root';
    $setupDbPass = getenv('DB_PASS') ?: '';
    
    echo "<h1>Gordon Food Service - Database Setup</h1>";
    echo "<p>Host: $setupDbHost:$setupDbPort</p>";
    echo "<p>Database: $setupDbName</p>";
    echo "<p>User: $setupDbUser</p>";
    
    try {
        // Connect without database name first
        $pdo = new PDO(
            "mysql:host=$setupDbHost;port=$setupDbPort;charset=utf8mb4",
            $setupDbUser, $setupDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<p style='color:green'>✓ Connected to MySQL server</p>";
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$setupDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color:green'>✓ Database '$setupDbName' ready</p>";
        
        // Use database
        $pdo->exec("USE `$setupDbName`");
        
        // Create tables
        $tables = [];
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(255),
            phone VARCHAR(50),
            role ENUM('customer', 'admin') DEFAULT 'customer',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'users';

        $pdo->exec("CREATE TABLE IF NOT EXISTS contractors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            contractor_code VARCHAR(32) NOT NULL UNIQUE,
            discount_percent DECIMAL(5,2) DEFAULT 35.00,
            discount_eligible TINYINT(1) DEFAULT 1,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'contractors';

        $pdo->exec("CREATE TABLE IF NOT EXISTS supply_pricing_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            config_json JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'supply_pricing_config';

        $pdo->exec("CREATE TABLE IF NOT EXISTS supply_requests (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            request_number VARCHAR(64) NOT NULL UNIQUE,
            contractor_id INT NOT NULL,
            duration_days INT NOT NULL,
            crew_size INT NOT NULL,
            supply_types JSON NOT NULL,
            delivery_location VARCHAR(50) NOT NULL,
            delivery_speed VARCHAR(50) NOT NULL,
            storage_life_months INT NULL,
            base_price DECIMAL(14,2) NULL,
            calculated_price DECIMAL(14,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            status VARCHAR(50) DEFAULT 'awaiting_review',
            effective_date DATE NULL,
            notes TEXT NULL,
            reviewed_by INT NULL,
            reviewed_at TIMESTAMP NULL,
            decline_reason TEXT NULL,
            payment_instructions TEXT NULL,
            approved_at TIMESTAMP NULL,
            declined_at TIMESTAMP NULL,
            payment_submitted_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (contractor_id) REFERENCES contractors(id)
        )");
        $tables[] = 'supply_requests';

        $pdo->exec("CREATE TABLE IF NOT EXISTS supply_request_payments (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            supply_request_id BIGINT NOT NULL,
            contractor_id INT NOT NULL,
            billing_name VARCHAR(255) NULL,
            phone VARCHAR(50) NULL,
            billing_address JSON NULL,
            card_brand VARCHAR(50) NULL,
            card_last4 VARCHAR(4) NULL,
            exp_month INT NULL,
            exp_year INT NULL,
            encrypted_payload TEXT NOT NULL,
            iv_b64 VARCHAR(64) NOT NULL,
            tag_b64 VARCHAR(64) NOT NULL,
            created_ip VARCHAR(45) NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supply_request_id) REFERENCES supply_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (contractor_id) REFERENCES contractors(id) ON DELETE CASCADE
        )");
        $tables[] = 'supply_request_payments';

        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'settings';

        $pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
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

        $pdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            to_email VARCHAR(255),
            subject VARCHAR(255),
            status VARCHAR(50),
            error_message TEXT,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'email_logs';

        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255),
            success TINYINT(1) DEFAULT 0,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, attempted_at)
        )");
        $tables[] = 'login_attempts';

        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE,
            user_id INT NULL,
            status ENUM('pending','awaiting_payment','payment_uploaded','payment_confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
            total DECIMAL(14,2),
            currency CHAR(3) DEFAULT 'USD',
            billing_address JSON,
            shipping_address JSON,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'orders';

        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT NULL,
            parent_id INT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'categories';

        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sku VARCHAR(100) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE,
            description TEXT,
            category_id INT NULL,
            unit_price DECIMAL(12,2),
            stock_quantity INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            is_featured TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'products';

        $pdo->exec("CREATE TABLE IF NOT EXISTS shipments (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT NULL,
            supply_request_id BIGINT NULL,
            carrier VARCHAR(100),
            tracking_number VARCHAR(255),
            status VARCHAR(50) DEFAULT 'pending',
            shipped_at TIMESTAMP NULL,
            delivered_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'shipments';

        echo "<p style='color:green'>✓ Created tables: " . implode(', ', $tables) . "</p>";

        // Insert default data
        $stmt = $pdo->query("SELECT COUNT(*) FROM supply_pricing_config");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO supply_pricing_config (config_json) VALUES (JSON_OBJECT(
                'base_rate_per_person_day', 22.5,
                'type_multipliers', JSON_OBJECT('water', 0.9, 'dry_food', 1.0, 'canned_food', 1.05, 'mixed_supplies', 1.1, 'toiletries', 1.05),
                'location_multipliers', JSON_OBJECT('pickup', 0.85, 'local', 0.95, 'onshore', 1.0, 'nearshore', 1.15, 'offshore_rig', 1.35),
                'speed_multipliers', JSON_OBJECT('standard', 1.0, 'priority', 1.2, 'emergency', 1.45)
            ))");
            echo "<p style='color:green'>✓ Inserted pricing config</p>";
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $adminHash = password_hash('Americana12@', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute(['gonnyzalowski@gmail.com', $adminHash, 'Administrator']);
            echo "<p style='color:green'>✓ Created admin user</p>";
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM contractors");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO contractors (full_name, company_name, contractor_code, discount_percent, discount_eligible, active) VALUES ('Demo Contractor', 'GFS Registered Contractor', 'GFS-DEMO-0001', 35.00, 1, 1)");
            echo "<p style='color:green'>✓ Created demo contractor</p>";
        }

        echo "<h2 style='color:green'>✓ Database Setup Complete!</h2>";
        echo "<p><strong>Admin login:</strong> gonnyzalowski@gmail.com / Americana12@</p>";
        echo "<p><a href='/'>Go to Home</a> | <a href='/supply'>Supply Portal</a> | <a href='/admin'>Admin Panel</a></p>";
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    exit;
}

// Temporarily show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

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

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/translations.php';

use Dotenv\Dotenv;
use GordonFoodService\App\Services\SupplyPricingWorker;

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

// English-only (en-US)
$lang = 'en-US';

// Load env
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Telegram notification helper function
function sendTelegramNotification(string $message, ?string $documentUrl = null): bool {
    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN') ?? null;
    $chatId = $_ENV['TELEGRAM_USER_ID'] ?? getenv('TELEGRAM_USER_ID') ?? null;
    
    if (!$botToken || !$chatId) {
        return false;
    }
    
    // Send text message
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data),
            'timeout' => 10,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    // If there's a document, send it too
    if ($documentUrl && $result !== false) {
        $docUrl = "https://api.telegram.org/bot$botToken/sendDocument";
        $baseUrl = rtrim(
            $_ENV['APP_URL'] ?? getenv('APP_URL') ?? ('http' . (!empty($_SERVER['HTTPS']) ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')),
            '/'
        );
        $path = (strpos($documentUrl, 'http') === 0) ? null : ((str_starts_with($documentUrl, '/')) ? $documentUrl : '/' . $documentUrl);
        $fullDocUrl = (strpos($documentUrl, 'http') === 0) ? $documentUrl : ($baseUrl . $path);
        $docData = [
            'chat_id' => $chatId,
            'document' => $fullDocUrl,
            'caption' => 'Attachment from customer',
        ];
        $docOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($docData),
                'timeout' => 15,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        $docContext = stream_context_create($docOptions);
        @file_get_contents($docUrl, false, $docContext);
    }
    
    return $result !== false;
}

 // Disable legacy endpoints from the previous Gordon Food Service store (ecommerce/tracking/tools)
 if (in_array($requestPath, [
     '/telegram-webhook',
     '/update-product-images',
     '/seed-software-products',
     '/setup-software-category',
     '/seed-aviation-products',
     '/update-aviation-images',
 ], true)) {
     http_response_code(410);
     exit('Disabled');
 }

// Telegram webhook endpoint - process admin replies from Telegram
if ($requestPath === '/telegram-webhook') {
    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN') ?? null;
    
    if (!$botToken) {
        http_response_code(500);
        exit('Bot token not configured');
    }
    
    $content = file_get_contents('php://input');
    $update = json_decode($content, true);
    
    if (!$update || !isset($update['message'])) {
        http_response_code(200);
        exit('OK');
    }
    
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    
    // Database connection for webhook
    $whDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
    $whDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $whDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'gordon_food_service';
    $whDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $whDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $whPdo = new PDO(
            "mysql:host=$whDbHost;port=$whDbPort;dbname=$whDbName;charset=utf8mb4",
            $whDbUser, $whDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        http_response_code(200);
        exit('OK');
    }
    
    // Process /reply command
    if (preg_match('/^\/reply\s+([A-Z0-9]+)\s+(.+)$/is', $text, $matches)) {
        $trackingNumber = strtoupper(trim($matches[1]));
        $replyMessage = trim($matches[2]);
        
        $stmt = $whPdo->prepare('SELECT id, order_id FROM shipments WHERE tracking_number = ?');
        $stmt->execute([$trackingNumber]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shipment) {
            $stmt = $whPdo->prepare(
                'INSERT INTO tracking_communications (order_id, tracking_number, sender_type, sender_name, message_type, message)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $shipment['order_id'],
                $trackingNumber,
                'admin',
                'Gordon Food Service Support',
                'message',
                $replyMessage,
            ]);
            
            // Send confirmation
            $confirmUrl = "https://api.telegram.org/bot$botToken/sendMessage";
            $confirmData = ['chat_id' => $chatId, 'text' => "✅ Reply sent to tracking $trackingNumber:\n\n$replyMessage"];
            $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode($confirmData)]]);
            @file_get_contents($confirmUrl, false, $ctx);
        } else {
            $errorUrl = "https://api.telegram.org/bot$botToken/sendMessage";
            $errorData = ['chat_id' => $chatId, 'text' => "❌ Tracking number not found: $trackingNumber"];
            $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode($errorData)]]);
            @file_get_contents($errorUrl, false, $ctx);
        }
    }
    // Help command
    elseif ($text === '/start' || $text === '/help') {
        $helpMsg = "🤖 Gordon Food Service Admin Bot\n\nI notify you when customers send messages.\n\nCommands:\n/reply TRACKING_NUMBER Your message - Reply to a customer\n/help - Show this help\n\nExample:\n/reply GFS20251213ABC123 Your delivery is on the way!";
        $helpUrl = "https://api.telegram.org/bot$botToken/sendMessage";
        $helpData = ['chat_id' => $chatId, 'text' => $helpMsg];
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode($helpData)]]);
        @file_get_contents($helpUrl, false, $ctx);
    }
    
    http_response_code(200);
    exit('OK');
}

// Database setup endpoint - initialize all tables
if ($requestPath === '/setup-database') {
    $setupDbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_ENV['MYSQL_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal');
    $setupDbPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? $_ENV['MYSQL_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306');
    $setupDbName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? $_ENV['MYSQL_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? 'railway');
    $setupDbUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? $_ENV['MYSQL_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root');
    $setupDbPass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? $_ENV['MYSQL_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '');
    
    try {
        // First connect WITHOUT database name to create it if needed
        $setupPdo = new PDO(
            "mysql:host=$setupDbHost;port=$setupDbPort;charset=utf8mb4",
            $setupDbUser, $setupDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create database if it doesn't exist
        $setupPdo->exec("CREATE DATABASE IF NOT EXISTS `$setupDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Now connect to the database
        $setupPdo->exec("USE `$setupDbName`");
        
        $tables = [];

        // Food-service only schema (no legacy products/orders/shipments/tracking)
        $setupPdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(255),
            phone VARCHAR(50),
            role ENUM('customer', 'admin') DEFAULT 'customer',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'users';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS contractors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            contractor_code VARCHAR(32) NOT NULL UNIQUE,
            discount_percent DECIMAL(5,2) DEFAULT 35.00,
            discount_eligible TINYINT(1) DEFAULT 1,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'contractors';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS supply_pricing_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            config_json JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'supply_pricing_config';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS supply_requests (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            request_number VARCHAR(64) NOT NULL UNIQUE,
            contractor_id INT NOT NULL,
            duration_days INT NOT NULL,
            crew_size INT NOT NULL,
            supply_types JSON NOT NULL,
            delivery_location VARCHAR(50) NOT NULL,
            delivery_speed VARCHAR(50) NOT NULL,
            storage_life_months INT NULL,
            base_price DECIMAL(14,2) NULL,
            calculated_price DECIMAL(14,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            status VARCHAR(50) DEFAULT 'awaiting_review',
            effective_date DATE NULL,
            notes TEXT NULL,
            reviewed_by INT NULL,
            reviewed_at TIMESTAMP NULL,
            decline_reason TEXT NULL,
            payment_instructions TEXT NULL,
            approved_at TIMESTAMP NULL,
            declined_at TIMESTAMP NULL,
            payment_submitted_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (contractor_id) REFERENCES contractors(id)
        )");
        $tables[] = 'supply_requests';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS supply_request_payments (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            supply_request_id BIGINT NOT NULL,
            contractor_id INT NOT NULL,
            billing_name VARCHAR(255) NULL,
            phone VARCHAR(50) NULL,
            billing_address JSON NULL,
            card_brand VARCHAR(50) NULL,
            card_last4 VARCHAR(4) NULL,
            exp_month INT NULL,
            exp_year INT NULL,
            encrypted_payload TEXT NOT NULL,
            iv_b64 VARCHAR(64) NOT NULL,
            tag_b64 VARCHAR(64) NOT NULL,
            created_ip VARCHAR(45) NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supply_request_id) REFERENCES supply_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (contractor_id) REFERENCES contractors(id) ON DELETE CASCADE
        )");
        $tables[] = 'supply_request_payments';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'settings';

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

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            to_email VARCHAR(255),
            subject VARCHAR(255),
            status VARCHAR(50),
            error_message TEXT,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'email_logs';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255),
            success TINYINT(1) DEFAULT 0,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, attempted_at)
        )");
        $tables[] = 'login_attempts';

        $stmt = $setupPdo->query("SELECT COUNT(*) FROM supply_pricing_config");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO supply_pricing_config (config_json) VALUES (JSON_OBJECT(
                'base_rate_per_person_day', 22.5,
                'type_multipliers', JSON_OBJECT('water', 0.9, 'dry_food', 1.0, 'canned_food', 1.05, 'mixed_supplies', 1.1, 'toiletries', 1.05),
                'location_multipliers', JSON_OBJECT('pickup', 0.85, 'local', 0.95, 'onshore', 1.0, 'nearshore', 1.15, 'offshore_rig', 1.35),
                'speed_multipliers', JSON_OBJECT('standard', 1.0, 'priority', 1.2, 'emergency', 1.45)
            ))");
        }

        $stmt = $setupPdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $adminHash = password_hash('Americana12@', PASSWORD_DEFAULT);
            $stmt = $setupPdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute(['gonnyzalowski@gmail.com', $adminHash, 'Administrator']);
        }

        // Create demo contractor
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM contractors");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO contractors (full_name, company_name, contractor_code, discount_percent, discount_eligible, active) VALUES ('Demo Contractor', 'GFS Registered Contractor', 'GFS-DEMO-0001', 35.00, 1, 1)");
        }

        header('Content-Type: text/html');
        echo '<h1>Gordon Food Service (Galveston) Database Setup Complete</h1>';
        echo '<p>Created tables: ' . implode(', ', $tables) . '</p>';
        echo '<p>Default admin: gonnyzalowski@gmail.com / Americana12@</p>';
        echo '<p><strong>Change the admin password immediately!</strong></p>';
        echo '<p><a href="/supply">Go to Supply Portal</a> | <a href="/admin">Go to Admin</a></p>';
        exit;
        
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
            currency VARCHAR(3) DEFAULT 'USD',
            stock_quantity INT DEFAULT 0,
            lead_time_days INT DEFAULT 14,
            weight_kg DECIMAL(10,2),
            dimensions VARCHAR(100),
            image_url VARCHAR(500),
            product_type VARCHAR(20) DEFAULT 'hardware',
            is_active TINYINT(1) DEFAULT 1,
            is_featured TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )");
        $tables[] = 'products';
        
        // Add product_type column if missing
        try { $setupPdo->exec("ALTER TABLE products ADD COLUMN product_type VARCHAR(20) DEFAULT 'hardware'"); } catch (PDOException $e) {}

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
            order_type VARCHAR(20) DEFAULT 'hardware',
            subtotal DECIMAL(12,2),
            tax_amount DECIMAL(12,2),
            shipping_amount DECIMAL(12,2),
            total_amount DECIMAL(12,2),
            currency VARCHAR(3) DEFAULT 'USD',
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
        
        // Add order_type column if missing
        try { $setupPdo->exec("ALTER TABLE orders ADD COLUMN order_type VARCHAR(20) DEFAULT 'hardware'"); } catch (PDOException $e) {}
        
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
            shipment_id BIGINT NOT NULL,
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
            order_id INT NULL,
            tracking_number VARCHAR(100) NULL,
            sender_type VARCHAR(50) NOT NULL,
            sender_name VARCHAR(255) NULL,
            message_type VARCHAR(50) DEFAULT 'message',
            message TEXT NULL,
            document_name VARCHAR(255) NULL,
            document_path VARCHAR(500) NULL,
            document_type VARCHAR(100) NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'tracking_communications';
        
        // Add missing columns to tracking_communications if they don't exist
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN tracking_number VARCHAR(100) NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN sender_name VARCHAR(255) NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN message_type VARCHAR(50) DEFAULT 'message'"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN document_name VARCHAR(255) NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN document_path VARCHAR(500) NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN document_type VARCHAR(100) NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications ADD COLUMN is_read TINYINT(1) DEFAULT 0"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications MODIFY COLUMN sender_type VARCHAR(50) NOT NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE tracking_communications MODIFY COLUMN message TEXT NULL"); } catch (PDOException $e) {}

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS contractors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            contractor_code VARCHAR(32) NOT NULL UNIQUE,
            discount_percent DECIMAL(5,2) DEFAULT 35.00,
            discount_eligible TINYINT(1) DEFAULT 1,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $tables[] = 'contractors';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS supply_pricing_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            config_json JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $tables[] = 'supply_pricing_config';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS supply_requests (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            request_number VARCHAR(64) NOT NULL UNIQUE,
            contractor_id INT NOT NULL,
            duration_days INT NOT NULL,
            crew_size INT NOT NULL,
            supply_types JSON NOT NULL,
            delivery_location VARCHAR(50) NOT NULL,
            delivery_speed VARCHAR(50) NOT NULL,
            storage_life_months INT NULL,
            calculated_price DECIMAL(14,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            status VARCHAR(50) DEFAULT 'submitted',
            effective_date DATE NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (contractor_id) REFERENCES contractors(id)
        )");
        $tables[] = 'supply_requests';

        $setupPdo->exec("CREATE TABLE IF NOT EXISTS supply_request_payments (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            supply_request_id BIGINT NOT NULL,
            contractor_id INT NOT NULL,
            billing_name VARCHAR(255) NULL,
            phone VARCHAR(50) NULL,
            billing_address JSON NULL,
            card_brand VARCHAR(50) NULL,
            card_last4 VARCHAR(4) NULL,
            exp_month INT NULL,
            exp_year INT NULL,
            encrypted_payload TEXT NOT NULL,
            iv_b64 VARCHAR(64) NOT NULL,
            tag_b64 VARCHAR(64) NOT NULL,
            created_ip VARCHAR(45) NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supply_request_id) REFERENCES supply_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (contractor_id) REFERENCES contractors(id) ON DELETE CASCADE
        )");
        $tables[] = 'supply_request_payments';

        try { $setupPdo->exec("ALTER TABLE supply_requests MODIFY COLUMN status VARCHAR(50) DEFAULT 'awaiting_review'"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN reviewed_by INT NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN reviewed_at TIMESTAMP NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN decline_reason TEXT NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN payment_instructions TEXT NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN approved_at TIMESTAMP NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN declined_at TIMESTAMP NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN payment_submitted_at TIMESTAMP NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN completed_at TIMESTAMP NULL"); } catch (PDOException $e) {}
        try { $setupPdo->exec("ALTER TABLE supply_requests ADD COLUMN base_price DECIMAL(14,2) NULL"); } catch (PDOException $e) {}

        $stmt = $setupPdo->query("SELECT COUNT(*) FROM supply_pricing_config");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO supply_pricing_config (config_json) VALUES (JSON_OBJECT(
                'base_rate_per_person_day', 22.5,
                'type_multipliers', JSON_OBJECT('water', 0.9, 'dry_food', 1.0, 'canned_food', 1.05, 'mixed_supplies', 1.1),
                'location_multipliers', JSON_OBJECT('pickup', 0.85, 'local', 0.95, 'onshore', 1.0, 'nearshore', 1.15, 'offshore_rig', 1.35),
                'speed_multipliers', JSON_OBJECT('standard', 1.0, 'priority', 1.2, 'emergency', 1.45)
            ))");
        }
        
        // Create default admin user if not exists
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $adminHash = password_hash('Americana12@', PASSWORD_DEFAULT);
            $setupPdo->exec("INSERT INTO users (email, password_hash, full_name, role) VALUES ('gonnyzalowski@gmail.com', '$adminHash', 'Administrator', 'admin')");
        }

        $stmt = $setupPdo->query('SELECT COUNT(*) FROM contractors');
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO contractors (full_name, company_name, contractor_code, discount_percent, discount_eligible, active)
                VALUES ('Demo Contractor', 'GFS Registered Contractor', 'GFS-DEMO-0001', 35.00, 1, 1)");
        }
        
        // Insert default categories if empty
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO categories (name, slug, description) VALUES 
                ('Fresh Produce', 'fresh-produce', 'Fresh fruits and vegetables for offshore provisioning'),
                ('Meat & Poultry', 'meat-poultry', 'Fresh and frozen meat products'),
                ('Seafood', 'seafood', 'Fresh and frozen fish and seafood products'),
                ('Dairy & Eggs', 'dairy-eggs', 'Milk, cheese, yogurt, and egg products'),
                ('Bakery', 'bakery', 'Bread, pastries, and baked goods'),
                ('Beverages', 'beverages', 'Water, juices, and other beverages')
            ");
        }
        
        // Add Dry Goods category if missing
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM categories WHERE slug = 'dry-goods'");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO categories (name, slug, description) VALUES ('Dry Goods', 'dry-goods', 'Rice, pasta, flour, and other dry food items')");
        }
        
        // Add Frozen Foods category if missing
        $stmt = $setupPdo->query("SELECT COUNT(*) FROM categories WHERE slug = 'frozen-foods'");
        if ($stmt->fetchColumn() == 0) {
            $setupPdo->exec("INSERT INTO categories (name, slug, description) VALUES ('Frozen Foods', 'frozen-foods', 'Frozen meals and ingredients')");
        }
        
        $products = [];
        $softwareProducts = [];
        $stmt = $setupPdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'slug'");
        $stmt->execute();
        $canSeedLegacyProducts = ((int)$stmt->fetchColumn() > 0);

        if ($canSeedLegacyProducts) {
            $stmt = $setupPdo->query("SELECT COUNT(*) FROM products");
            if ($stmt->fetchColumn() == 0) {
                $products = [
                    ['Fresh Vegetable Mix (10kg)', 'fresh-vegetable-mix-10kg', 1, 45.99, 'Assorted fresh vegetables including tomatoes, lettuce, carrots, and onions - perfect for offshore provisioning'],
                    ['Premium Beef Selection (5kg)', 'premium-beef-selection-5kg', 2, 89.99, 'High-quality beef cuts including steaks and ground beef - frozen for long-term storage'],
                    ['Bottled Water Case (24x500ml)', 'bottled-water-case-24x500ml', 6, 12.99, '24 bottles of purified drinking water - essential for offshore operations'],
                ];

                $insertStmt = $setupPdo->prepare("INSERT INTO products (sku, name, slug, description, category_id, unit_price, image_url, is_active, is_featured, product_type) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)");

                foreach ($products as $i => $p) {
                    $sku = 'GFS-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT);
                    $slug = $p[1];
                    $imageUrl = '/images/' . $slug . '/1.jpg';
                    $isFeatured = $i < 3 ? 1 : 0;
                    $insertStmt->execute([$sku, $p[0], $slug, $p[4], $p[2], $p[3], $imageUrl, $isFeatured, 'hardware']);
                }
            }
        }
        
        // Add missing columns for order flow (safe to run multiple times)
        $migrations = [];
        
        // Add events column to shipments if missing
        try {
            $setupPdo->exec("ALTER TABLE shipments ADD COLUMN events JSON NULL");
            $migrations[] = 'shipments.events';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                // Column already exists, ignore
            }
        }
        
        // Add shipped_at to orders if missing
        try {
            $setupPdo->exec("ALTER TABLE orders ADD COLUMN shipped_at TIMESTAMP NULL");
            $migrations[] = 'orders.shipped_at';
        } catch (PDOException $e) {}
        
        // Add payment_confirmed_at to orders if missing
        try {
            $setupPdo->exec("ALTER TABLE orders ADD COLUMN payment_confirmed_at TIMESTAMP NULL");
            $migrations[] = 'orders.payment_confirmed_at';
        } catch (PDOException $e) {}
        
        // Add payment_confirmed_by to orders if missing
        try {
            $setupPdo->exec("ALTER TABLE orders ADD COLUMN payment_confirmed_by INT NULL");
            $migrations[] = 'orders.payment_confirmed_by';
        } catch (PDOException $e) {}
        
        // Add total column to orders if missing (alias for total_amount)
        try {
            $setupPdo->exec("ALTER TABLE orders ADD COLUMN total DECIMAL(14,2) NULL");
            $migrations[] = 'orders.total';
        } catch (PDOException $e) {}
        
        // Add qty column to order_items if missing (alias for quantity)
        try {
            $setupPdo->exec("ALTER TABLE order_items ADD COLUMN qty INT NULL");
            $migrations[] = 'order_items.qty';
        } catch (PDOException $e) {}
        
        // Sync qty with quantity for existing records
        try {
            $setupPdo->exec("UPDATE order_items SET qty = quantity WHERE qty IS NULL AND quantity IS NOT NULL");
        } catch (PDOException $e) {}
        
        // Sync total with total_amount for existing records
        try {
            $setupPdo->exec("UPDATE orders SET total = total_amount WHERE total IS NULL AND total_amount IS NOT NULL");
        } catch (PDOException $e) {}
        
        header('Content-Type: text/html');
        echo '<h1>Gordon Food Service (Galveston) Database Setup Complete</h1>';
        echo '<p>Created tables: ' . implode(', ', $tables) . '</p>';
        echo '<p>Migrations applied: ' . (empty($migrations) ? 'none (all columns exist)' : implode(', ', $migrations)) . '</p>';
        echo '<p>Seeded ' . count($products ?? []) . ' legacy products</p>';
        echo '<p>Default admin: gonnyzalowski@gmail.com / Americana12@</p>';
        echo '<p><strong>Change the admin password immediately!</strong></p>';
        echo '<p><a href="/supply">Go to Supply Portal</a> | <a href="/admin">Go to Admin</a></p>';
        exit;
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Database Setup Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Update product images endpoint - scans filesystem and updates database
if ($requestPath === '/update-product-images') {
    $imgDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
    $imgDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $imgDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'gordon_food_service';
    $imgDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $imgDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $imgPdo = new PDO(
            "mysql:host=$imgDbHost;port=$imgDbPort;dbname=$imgDbName;charset=utf8mb4",
            $imgDbUser, $imgDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $updated = [];
        $imagesDir = __DIR__ . '/images';
        
        // Get all products
        $products = $imgPdo->query('SELECT id, slug, image_url FROM products')->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $product) {
            $slug = $product['slug'];
            $productImgDir = $imagesDir . '/' . $slug;
            
            if (is_dir($productImgDir)) {
                $files = glob($productImgDir . '/*.*');
                if (!empty($files)) {
                    // Sort to get 1.* first
                    usort($files, function($a, $b) {
                        return basename($a) <=> basename($b);
                    });
                    $newImageUrl = '/images/' . $slug . '/' . basename($files[0]);
                    
                    if ($newImageUrl !== $product['image_url']) {
                        $stmt = $imgPdo->prepare('UPDATE products SET image_url = ? WHERE id = ?');
                        $stmt->execute([$newImageUrl, $product['id']]);
                        $updated[] = $slug . ': ' . $newImageUrl;
                    }
                }
            }
        }
        
        header('Content-Type: text/html');
        echo '<h1>Product Images Updated</h1>';
        echo '<p>Updated ' . count($updated) . ' products:</p>';
        echo '<ul>';
        foreach ($updated as $u) {
            echo '<li>' . htmlspecialchars($u) . '</li>';
        }
        echo '</ul>';
        echo '<p><a href="/catalog">View Catalog</a></p>';
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Update Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Seed software products endpoint - adds software products to existing database
if ($requestPath === '/seed-software-products') {
    $sftDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
    $sftDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $sftDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'gordon_food_service';
    $sftDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $sftDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $sftPdo = new PDO(
            "mysql:host=$sftDbHost;port=$sftDbPort;dbname=$sftDbName;charset=utf8mb4",
            $sftDbUser, $sftDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Add product_type column if missing
        try { $sftPdo->exec("ALTER TABLE products ADD COLUMN product_type VARCHAR(20) DEFAULT 'hardware'"); } catch (PDOException $e) {}
        
        // Check if software products already exist
        $stmt = $sftPdo->query("SELECT COUNT(*) FROM products WHERE product_type = 'software'");
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: text/html');
            echo '<h1>Software Products Already Exist</h1>';
            echo '<p>Software products have already been seeded.</p>';
            echo '<p><a href="/catalog">View Catalog</a></p>';
            exit;
        }
        
        $softwareProducts = [
            ['PipeFlow Pro Enterprise', 'pipeflow-pro-enterprise', 1, 125000, 'Advanced pipeline flow simulation and analysis software with real-time monitoring, leak detection algorithms, and predictive maintenance. Includes perpetual license key and 24/7 support.'],
            ['PlantDesign Suite 2024', 'plantdesign-suite-2024', 1, 98000, 'Comprehensive 3D plant design and engineering software for process industries. Features P&ID creation, equipment sizing, and regulatory compliance tools. Enterprise license included.'],
            ['Pipeline Integrity Manager', 'pipeline-integrity-manager', 1, 145000, 'Enterprise pipeline integrity management system with corrosion modeling, risk assessment, and inspection scheduling. Includes API integration and multi-site deployment.'],
            ['MechCAD Professional', 'mechcad-professional', 2, 115000, 'Industrial-grade mechanical CAD software with FEA analysis, thermal simulation, and fatigue life prediction. Perpetual license with source code access for customization.'],
            ['TurboMachinery Simulator', 'turbomachinery-simulator', 2, 135000, 'High-fidelity turbomachinery design and simulation platform for compressors, turbines, and pumps. Includes CFD integration and performance optimization tools.'],
            ['Structural Analysis Pro', 'structural-analysis-pro', 2, 89000, 'Advanced structural analysis software for heavy machinery and industrial equipment. Features dynamic load analysis, vibration modeling, and safety factor calculations.'],
            ['DrillSim Enterprise', 'drillsim-enterprise', 3, 185000, 'Real-time drilling simulation and optimization platform with wellbore stability analysis, torque & drag modeling, and automated drilling parameter optimization.'],
            ['WellPlan Professional', 'wellplan-professional', 3, 165000, 'Comprehensive well planning software with 3D trajectory design, anti-collision analysis, and casing design optimization. Includes real-time data integration.'],
            ['MudLogic Analyzer', 'mudlogic-analyzer', 3, 95000, 'Drilling fluid analysis and optimization software with rheology modeling, solids control simulation, and chemical treatment recommendations.'],
            ['HydroSim Professional', 'hydrosim-professional', 4, 78000, 'Hydraulic system design and simulation software with component sizing, circuit optimization, and energy efficiency analysis. Includes extensive component library.'],
            ['FluidPower Designer', 'fluidpower-designer', 4, 112000, 'Enterprise fluid power system design platform with real-time simulation, failure mode analysis, and predictive maintenance algorithms.'],
            ['Servo Control Suite', 'servo-control-suite', 4, 145000, 'Advanced servo-hydraulic control system software with PID tuning, motion profiling, and multi-axis synchronization. Includes hardware interface modules.'],
            ['SCADA Master Enterprise', 'scada-master-enterprise', 5, 195000, 'Industrial SCADA platform with unlimited tags, historian, alarm management, and cybersecurity features. Includes redundancy and disaster recovery capabilities.'],
            ['ProcessControl Pro', 'processcontrol-pro', 5, 125000, 'Advanced process control software with model predictive control, neural network optimization, and real-time performance monitoring.'],
            ['InstruCalib Manager', 'instrucalib-manager', 5, 68000, 'Instrument calibration management system with automated scheduling, compliance reporting, and audit trail. Supports all major instrument protocols.'],
        ];
        
        $insertStmt = $sftPdo->prepare("INSERT INTO products (sku, name, slug, description, category_id, unit_price, image_url, is_active, is_featured, product_type) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 'software')");
        
        $inserted = [];
        foreach ($softwareProducts as $i => $p) {
            $sku = 'SFT-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT);
            $insertStmt->execute([$sku, $p[0], $p[1], $p[4], $p[2], $p[3], '/assets/software-product.svg']);
            $inserted[] = $p[0];
        }
        
        header('Content-Type: text/html');
        echo '<h1>Software Products Seeded</h1>';
        echo '<p>Added ' . count($inserted) . ' software products:</p>';
        echo '<ul>';
        foreach ($inserted as $name) {
            echo '<li>' . htmlspecialchars($name) . '</li>';
        }
        echo '</ul>';
        echo '<p><a href="/catalog">View Catalog</a></p>';
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Seeding Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Setup Software category and reassign software products
if ($requestPath === '/setup-software-category') {
    $swDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
    $swDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $swDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'gordon_food_service';
    $swDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $swDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $swPdo = new PDO(
            "mysql:host=$swDbHost;port=$swDbPort;dbname=$swDbName;charset=utf8mb4",
            $swDbUser, $swDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Add Engineering Software category if not exists
        $stmt = $swPdo->query("SELECT id FROM categories WHERE slug = 'engineering-software'");
        $softwareCatId = $stmt->fetchColumn();
        
        if (!$softwareCatId) {
            $swPdo->exec("INSERT INTO categories (name, slug, description) VALUES ('Engineering Software', 'engineering-software', 'Enterprise software solutions for industrial engineering, simulation, and process control')");
            $softwareCatId = $swPdo->lastInsertId();
        }
        
        // Reassign all software products to the new category
        $stmt = $swPdo->prepare("UPDATE products SET category_id = ? WHERE product_type = 'software'");
        $stmt->execute([$softwareCatId]);
        $updated = $stmt->rowCount();
        
        header('Content-Type: text/html');
        echo '<h1>Software Category Setup Complete</h1>';
        echo '<p>Category ID: ' . $softwareCatId . '</p>';
        echo '<p>Products reassigned: ' . $updated . '</p>';
        echo '<p><a href="/catalog?category=engineering-software">View Software Products</a></p>';
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Setup Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Seed Aviation Engineering products endpoint
if ($requestPath === '/seed-aviation-products') {
    $avDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
    $avDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $avDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'gordon_food_service';
    $avDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $avDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $avPdo = new PDO(
            "mysql:host=$avDbHost;port=$avDbPort;dbname=$avDbName;charset=utf8mb4",
            $avDbUser, $avDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Add Aviation Engineering category if not exists
        $stmt = $avPdo->query("SELECT id FROM categories WHERE slug = 'aviation-engineering'");
        $aviationCatId = $stmt->fetchColumn();
        
        if (!$aviationCatId) {
            $avPdo->exec("INSERT INTO categories (name, slug, description) VALUES ('Aviation Engineering', 'aviation-engineering', 'Aircraft maintenance equipment, ground support systems, and aerospace manufacturing tools')");
            $aviationCatId = $avPdo->lastInsertId();
        }
        
        // Get Engineering Software category ID for aviation software
        $stmt = $avPdo->query("SELECT id FROM categories WHERE slug = 'engineering-software'");
        $softwareCatId = $stmt->fetchColumn();
        
        // Check if aviation products already exist
        $stmt = $avPdo->query("SELECT COUNT(*) FROM products WHERE sku LIKE 'AVN-%'");
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: text/html');
            echo '<h1>Aviation Products Already Exist</h1>';
            echo '<p>Aviation products have already been seeded.</p>';
            echo '<p><a href="/catalog?category=aviation-engineering">View Aviation Products</a></p>';
            exit;
        }
        
        // Aviation Hardware Products (10 products, $50K-$180K)
        $aviationHardware = [
            ['Aircraft Engine Test Stand', 'aircraft-engine-test-stand', $aviationCatId, 175000, 'Heavy-duty engine test stand for turbofan and turboprop engines up to 50,000 lbs thrust. Includes data acquisition system and safety containment.', 'hardware'],
            ['Wing Assembly Jig System', 'wing-assembly-jig-system', $aviationCatId, 165000, 'Precision wing assembly jig for commercial aircraft manufacturing. CNC-controlled positioning with ±0.001" accuracy.', 'hardware'],
            ['Aircraft Hydraulic Ground Power Unit', 'aircraft-hydraulic-gpu', $aviationCatId, 125000, 'Mobile hydraulic ground power unit providing 3000 PSI at 45 GPM for aircraft system testing and maintenance.', 'hardware'],
            ['Fuselage Autoclave System', 'fuselage-autoclave-system', $aviationCatId, 180000, 'Industrial autoclave for composite fuselage curing. 12m length, 4m diameter, 200°C/400 PSI rated.', 'hardware'],
            ['Aircraft Wheel & Brake Test Rig', 'aircraft-wheel-brake-test-rig', $aviationCatId, 95000, 'Dynamic wheel and brake assembly test system for certification testing. Simulates landing loads up to 500,000 lbs.', 'hardware'],
            ['Turbine Blade Inspection System', 'turbine-blade-inspection-system', $aviationCatId, 145000, 'Automated turbine blade inspection system with 3D scanning, CT imaging, and defect detection AI.', 'hardware'],
            ['Aircraft Fuel System Test Bench', 'aircraft-fuel-system-test-bench', $aviationCatId, 85000, 'Complete fuel system test bench for pumps, valves, and fuel quantity indicating systems. ATEX certified.', 'hardware'],
            ['Landing Gear Drop Test Tower', 'landing-gear-drop-test-tower', $aviationCatId, 155000, 'Vertical drop test facility for landing gear qualification. 15m drop height, 20-ton capacity.', 'hardware'],
            ['Avionics Integration Test Station', 'avionics-integration-test-station', $aviationCatId, 68000, 'Comprehensive avionics test station for flight management, navigation, and communication systems integration.', 'hardware'],
            ['Aircraft Painting & Coating Booth', 'aircraft-painting-booth', $aviationCatId, 52000, 'Temperature and humidity controlled paint booth for aircraft components. Meets aerospace coating specifications.', 'hardware'],
        ];
        
        // Aviation Software Products (3 products, $25K-$150K) - goes to Engineering Software category
        $aviationSoftware = [
            ['AeroCAD Pro Suite', 'aerocad-pro-suite', $softwareCatId, 145000, 'Professional aerospace CAD/CAM software for aircraft structural design, stress analysis, and manufacturing planning. Includes CATIA and STEP file compatibility.', 'software'],
            ['FlightSim Certification Platform', 'flightsim-certification-platform', $softwareCatId, 98000, 'Level D flight simulator certification software with real-time aerodynamic modeling, weather simulation, and FAA/EASA compliance reporting.', 'software'],
            ['AeroMaint MRO Manager', 'aeromaint-mro-manager', $softwareCatId, 35000, 'Aircraft maintenance, repair, and overhaul management system. Tracks component life, schedules inspections, and manages airworthiness directives.', 'software'],
        ];
        
        $insertStmt = $avPdo->prepare("INSERT INTO products (sku, name, slug, description, category_id, unit_price, image_url, is_active, is_featured, product_type) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, ?)");
        
        $inserted = [];
        
        // Insert hardware products
        foreach ($aviationHardware as $i => $p) {
            $sku = 'AVN-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT);
            $insertStmt->execute([$sku, $p[0], $p[1], $p[4], $p[2], $p[3], '/assets/aviation-product.svg', $p[5]]);
            $inserted[] = ['name' => $p[0], 'type' => 'Hardware', 'price' => $p[3]];
        }
        
        // Insert software products
        foreach ($aviationSoftware as $i => $p) {
            $sku = 'AVN-SFT-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT);
            $insertStmt->execute([$sku, $p[0], $p[1], $p[4], $p[2], $p[3], '/assets/software-product.svg', $p[5]]);
            $inserted[] = ['name' => $p[0], 'type' => 'Software', 'price' => $p[3]];
        }
        
        header('Content-Type: text/html');
        echo '<h1>Aviation Products Seeded</h1>';
        echo '<p>Added ' . count($inserted) . ' aviation products:</p>';
        echo '<table border="1" cellpadding="8"><tr><th>Product</th><th>Type</th><th>Price</th></tr>';
        foreach ($inserted as $item) {
            echo '<tr><td>' . htmlspecialchars($item['name']) . '</td><td>' . $item['type'] . '</td><td>$' . number_format($item['price'], 2) . '</td></tr>';
        }
        echo '</table>';
        echo '<p><a href="/catalog?category=aviation-engineering">View Aviation Hardware</a> | <a href="/catalog?category=engineering-software">View Aviation Software</a></p>';
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Seeding Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Update aviation images endpoint
if ($requestPath === '/update-aviation-images') {
    $imgDbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
    $imgDbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    $imgDbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'gordon_food_service';
    $imgDbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $imgDbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    
    try {
        $imgPdo = new PDO(
            "mysql:host=$imgDbHost;port=$imgDbPort;dbname=$imgDbName;charset=utf8mb4",
            $imgDbUser, $imgDbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Map SKUs to specific images
        $imageMap = [
            'AVN-0001' => '/assets/aviation/engine-test-stand.svg',
            'AVN-0002' => '/assets/aviation/wing-assembly-jig.svg',
            'AVN-0003' => '/assets/aviation/hydraulic-gpu.svg',
            'AVN-0004' => '/assets/aviation/autoclave.svg',
            'AVN-0005' => '/assets/aviation/wheel-brake-test.svg',
            'AVN-0006' => '/assets/aviation/turbine-inspection.svg',
            'AVN-0007' => '/assets/aviation/fuel-test-bench.svg',
            'AVN-0008' => '/assets/aviation/landing-gear-tower.svg',
            'AVN-0009' => '/assets/aviation/avionics-test.svg',
            'AVN-0010' => '/assets/aviation/paint-booth.svg',
        ];
        
        $updateStmt = $imgPdo->prepare("UPDATE products SET image_url = ? WHERE sku = ?");
        $updated = [];
        
        foreach ($imageMap as $sku => $imageUrl) {
            $updateStmt->execute([$imageUrl, $sku]);
            if ($updateStmt->rowCount() > 0) {
                $updated[] = ['sku' => $sku, 'image' => $imageUrl];
            }
        }
        
        header('Content-Type: text/html');
        echo '<h1>Aviation Product Images Updated</h1>';
        echo '<p>Updated ' . count($updated) . ' products:</p>';
        echo '<table border="1" cellpadding="8"><tr><th>SKU</th><th>Image</th><th>Preview</th></tr>';
        foreach ($updated as $item) {
            echo '<tr><td>' . htmlspecialchars($item['sku']) . '</td><td>' . htmlspecialchars($item['image']) . '</td><td><img src="' . htmlspecialchars($item['image']) . '" width="80" height="80"></td></tr>';
        }
        echo '</table>';
        echo '<p><a href="/catalog?category=aviation-engineering">View Aviation Products</a></p>';
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: text/html');
        http_response_code(500);
        echo '<h1>Update Failed</h1>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// Support both local and Railway MySQL environment variables
// Railway provides DATABASE_URL which we should parse first
$databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;

if ($databaseUrl) {
    // Parse DATABASE_URL (format: mysql://user:pass@host:port/dbname)
    $dbParts = parse_url($databaseUrl);
    $dbHost = $dbParts['host'] ?? '127.0.0.1';
    $dbPort = $dbParts['port'] ?? '3306';
    $dbName = ltrim($dbParts['path'] ?? '/railway', '/');
    $dbUser = $dbParts['user'] ?? 'root';
    $dbPass = $dbParts['pass'] ?? '';
} else {
    // Fallback to individual environment variables
    $dbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQL_HOST'] ?? $_ENV['MYSQLHOST'] ?? getenv('MYSQL_HOST') ?? '127.0.0.1';
    $dbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQL_PORT'] ?? $_ENV['MYSQLPORT'] ?? getenv('MYSQL_PORT') ?? '3306';
    $dbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQL_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? getenv('MYSQL_DATABASE') ?? 'gordon_food_service';
    $dbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQL_USER'] ?? $_ENV['MYSQLUSER'] ?? getenv('MYSQL_USER') ?? 'root';
    $dbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQL_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQL_PASSWORD') ?? '';
}

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
    // Try to create database first, then reconnect
    try {
        // Connect without database name
        $setupDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbHost, $dbPort);
        $setupPdo = new PDO($setupDsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Create database if it doesn't exist
        $setupPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Now try to connect to the database
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Run database setup if tables don't exist
        $stmt = $pdo->query("SHOW TABLES");
        if ($stmt->rowCount() == 0) {
            // Run setup script
            $setupScript = __DIR__ . '/../setup_database.php';
            if (file_exists($setupScript)) {
                // Include setup script but don't output to browser
                ob_start();
                include $setupScript;
                ob_clean();
            }
        }
        
    } catch (PDOException $setupError) {
        http_response_code(503);
        $title = 'Temporarily Closed - Gordon Food Service';
        ob_start();
        require __DIR__ . '/templates/pages/closed.php';
        $content = ob_get_clean();
        require __DIR__ . '/templates/layout.php';
        exit;
    }
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

function format_price(float $amount, string $currency = 'USD'): string {
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
    return 'GFS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function generate_tracking_number(): string {
    return 'GFS' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 10));
}

function generate_supply_request_number(): string {
    return 'SUP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function require_contractor(): void {
    if (empty($_SESSION['contractor_id'])) {
        header('Location: /supply');
        exit;
    }
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

function get_setting_value(string $key): ?string {
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return is_string($value) ? $value : null;
    } catch (Throwable $e) {
        return null;
    }
}

function set_setting_value(string $key, string $value): void {
    global $pdo;
    try {
        $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $stmt->execute([$key, $value]);
    } catch (Throwable $e) {
    }
}

function get_payment_encryption_key(): string {
    $key = get_setting_value('payment_encryption_key');
    if (is_string($key) && $key !== '') {
        return $key;
    }

    $newKey = base64_encode(random_bytes(32));
    set_setting_value('payment_encryption_key', $newKey);
    return $newKey;
}

function encrypt_payment_payload(array $payload): array {
    $keyB64 = get_payment_encryption_key();
    $key = base64_decode($keyB64, true);
    if (!is_string($key) || strlen($key) !== 32) {
        $key = hash('sha256', (string)$keyB64, true);
    }

    $iv = random_bytes(12);
    $tag = '';
    $json = json_encode($payload);
    if (!is_string($json)) {
        throw new RuntimeException('Failed to encode payment payload.');
    }

    $ciphertext = openssl_encrypt($json, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) {
        throw new RuntimeException('Failed to encrypt payment payload.');
    }

    return [
        'ciphertext_b64' => base64_encode($ciphertext),
        'iv_b64' => base64_encode($iv),
        'tag_b64' => base64_encode($tag),
    ];
}

function decrypt_payment_payload(string $ciphertextB64, string $ivB64, string $tagB64): ?array {
    $keyB64 = get_payment_encryption_key();
    $key = base64_decode($keyB64, true);
    if (!is_string($key) || strlen($key) !== 32) {
        $key = hash('sha256', (string)$keyB64, true);
    }

    $ciphertext = base64_decode($ciphertextB64, true);
    $iv = base64_decode($ivB64, true);
    $tag = base64_decode($tagB64, true);
    if (!is_string($ciphertext) || !is_string($iv) || !is_string($tag)) {
        return null;
    }

    $json = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if (!is_string($json) || $json === '') {
        return null;
    }
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : null;
}

function purge_expired_supply_payments(): void {
    global $pdo;
    try {
        $pdo->exec("UPDATE supply_requests sr
            JOIN supply_request_payments p ON p.supply_request_id = sr.id
            SET sr.status = 'approved_awaiting_payment', sr.payment_submitted_at = NULL
            WHERE p.expires_at <= NOW() AND sr.status = 'payment_submitted_processing'");

        $pdo->exec('DELETE FROM supply_request_payments WHERE expires_at <= NOW()');
    } catch (Throwable $e) {
    }
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Disable legacy tracking endpoints - replaced with supply portal
if (preg_match('#^/(track|tracking|api/track|api/tracking)#', $path)) {
    http_response_code(404);
    require __DIR__ . '/templates/pages/closed.php';
    require __DIR__ . '/templates/layout.php';
    exit;
}

if (str_starts_with($path, '/api/')) {
     json_response(['error' => 'This endpoint is not available. Please use /supply.'], 410);
 }

if (in_array($path, ['/api/products', '/api/cart', '/api/checkout'], true)) {
    json_response(['error' => 'Product checkout has been disabled. Use /supply.'], 410);
}

if ($path === '/api/track' || str_starts_with($path, '/api/tracking/')) {
    json_response(['error' => 'Shipment tracking has been replaced by the contractor Supply Portal. Use /supply.'], 410);
}

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
        $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)';
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
    
    json_response(['error' => 'Product checkout has been disabled. Use /supply.'], 410);
}

// POST /api/checkout - Create order
if ($path === '/api/checkout' && $method === 'POST') {
    json_response(['error' => 'Product checkout has been disabled. Use /supply.'], 410);
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
    json_response(['error' => 'Tracking has been replaced by contractor Supply Portal. Use /supply.'], 410);
}

// POST /api/tracking/{tracking_number}/message
if (preg_match('#^/api/tracking/([A-Za-z0-9]+)/message$#', $path, $m) && $method === 'POST') {
    json_response(['error' => 'Tracking has been replaced by contractor Supply Portal. Use /supply.'], 410);
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
    $carrier = 'GFS Logistics';
    $shippingMethod = $data['shipping_method'] ?? 'air_freight';
    $packageType = $data['package_type'] ?? 'crate';
    
    // Shipping method descriptions
    $methodDescriptions = [
        'air_freight' => 'Air Freight - International Express',
        'sea_freight' => 'Sea Freight - Heavy Cargo',
        'local_van' => 'Local Van Delivery',
        'motorcycle' => 'Motorcycle Courier Express',
    ];
    
    // Create shipment with tracking events
    $stmt = $pdo->prepare(
        'INSERT INTO shipments (order_id, carrier, tracking_number, status, events)
         VALUES (?, ?, ?, ?, ?)'
    );
    
    $initialEvents = [
        [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'SHIPPED',
            'description' => 'Shipment picked up from warehouse via ' . ($methodDescriptions[$shippingMethod] ?? 'GFS Logistics'),
            'location' => 'Galveston, TX',
            'facility' => 'Galveston Distribution Hub',
        ],
    ];
    
    $stmt->execute([
        $orderId,
        $carrier,
        $trackingNumber,
        'shipped',
        json_encode($initialEvents),
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

// POST /admin/shipments/{id}/update-tracking - Disabled, replaced with supply portal
if (preg_match('#^/admin/shipments/(\d+)/update-tracking$#', $path, $m) && $method === 'POST') {
    require_admin();
    json_response(['error' => 'Shipment tracking has been replaced by contractor Supply Portal. Use /supply.'], 410);
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
        'INSERT INTO tracking_communications (order_id, tracking_number, sender_type, message_type, message) VALUES (?, ?, ?, ?, ?)'
    )->execute([
        $shipment['order_id'],
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

// POST /admin/shipments/{id}/clear-customs - Disabled, replaced with supply portal
if (preg_match('#^/admin/shipments/(\d+)/clear-customs$#', $path, $m) && $method === 'POST') {
    require_admin();
    json_response(['error' => 'Shipment tracking has been replaced by contractor Supply Portal. Use /supply.'], 410);
}

// POST /admin/tracking/{tracking_number}/message - Disabled, replaced with supply portal
if (preg_match('#^/admin/tracking/([A-Za-z0-9]+)/message$#', $path, $m) && $method === 'POST') {
    require_admin();
    json_response(['error' => 'Shipment tracking has been replaced by contractor Supply Portal. Use /supply.'], 410);
}

// ============ HTML ROUTES ============

// GET / - Homepage landing page
if ($path === '/' && $method === 'GET') {
    render_template('home.php', [
        'title' => 'Gordon Food Service - Offshore & Onshore Provisioning',
    ]);
}

if ($path === '/supply' && $method === 'GET') {
    purge_expired_supply_payments();
    $contractor = null;
    $requests = [];
    $error = null;
    $info = $_SESSION['flash_info'] ?? null;
    unset($_SESSION['flash_info']);

    if (!empty($_SESSION['contractor_id'])) {
        $stmt = $pdo->prepare('SELECT * FROM contractors WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$_SESSION['contractor_id']]);
        $contractor = $stmt->fetch();

        if (!$contractor || empty($contractor['active'])) {
            unset($_SESSION['contractor_id']);
            $contractor = null;
            $error = 'Your contractor access has been disabled.';
        }
    }

    if ($contractor) {
        $showAll = !empty($_GET['all']);
        $sql = 'SELECT * FROM supply_requests WHERE contractor_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 2 YEAR) ORDER BY created_at DESC';
        if (!$showAll) {
            $sql .= ' LIMIT 5';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([(int)$contractor['id']]);
        $requests = $stmt->fetchAll();
    }

    render_template('supply.php', [
        'title' => 'Supply Portal - Gordon Food Service',
        'contractor' => $contractor,
        'requests' => $requests,
        'error' => $error,
        'info' => $info,
        'showAll' => !empty($_GET['all']),
    ]);
}

if ($path === '/supply/code' && $method === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        render_template('supply.php', ['title' => 'Supply Portal - Gordon Food Service', 'error' => 'Invalid request. Please try again.']);
    }

    $code = strtoupper(trim((string)($_POST['contractor_code'] ?? '')));
    if ($code === '') {
        render_template('supply.php', ['title' => 'Supply Portal - Gordon Food Service', 'error' => 'Contractor code is required.']);
    }

    $stmt = $pdo->prepare('SELECT * FROM contractors WHERE contractor_code = ? AND active = 1 LIMIT 1');
    $stmt->execute([$code]);
    $contractor = $stmt->fetch();
    if (!$contractor) {
        render_template('supply.php', ['title' => 'Supply Portal - Gordon Food Service', 'error' => 'Invalid contractor code.']);
    }

    session_regenerate_id(true);
    $_SESSION['contractor_id'] = (int)$contractor['id'];
    header('Location: /supply');
    exit;
}

if ($path === '/supply/logout' && $method === 'GET') {
    unset($_SESSION['contractor_id']);
    header('Location: /supply');
    exit;
}

if ($path === '/supply/request' && $method === 'POST') {
    require_contractor();
    if (!verify_csrf()) {
        http_response_code(403);
        render_template('supply.php', ['title' => 'Supply Portal - Gordon Food Service', 'error' => 'Invalid request. Please try again.']);
    }

    $stmt = $pdo->prepare('SELECT * FROM contractors WHERE id = ? AND active = 1 LIMIT 1');
    $stmt->execute([(int)$_SESSION['contractor_id']]);
    $contractor = $stmt->fetch();
    if (!$contractor) {
        unset($_SESSION['contractor_id']);
        header('Location: /supply');
        exit;
    }

    $input = [
        'duration_days' => (int)($_POST['duration_days'] ?? 0),
        'crew_size' => (int)($_POST['crew_size'] ?? 0),
        'supply_types' => $_POST['supply_types'] ?? [],
        'delivery_location' => (string)($_POST['delivery_location'] ?? ''),
        'delivery_speed' => (string)($_POST['delivery_speed'] ?? ''),
        'storage_life_months' => ($_POST['storage_life_months'] ?? null) !== null ? (int)($_POST['storage_life_months'] ?? 6) : null,
    ];

    try {
        $worker = new SupplyPricingWorker($pdo);
        $pricing = $worker->calculate($input, $contractor);
        $requestNumber = generate_supply_request_number();

        $stmt = $pdo->prepare(
            'INSERT INTO supply_requests (request_number, contractor_id, duration_days, crew_size, supply_types, delivery_location, delivery_speed, storage_life_months, base_price, calculated_price, currency, status, effective_date, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $requestNumber,
            (int)$contractor['id'],
            (int)$input['duration_days'],
            (int)$input['crew_size'],
            json_encode($input['supply_types']),
            (string)$input['delivery_location'],
            (string)$input['delivery_speed'],
            $input['storage_life_months'],
            (float)$pricing['base_price'],
            (float)$pricing['calculated_price'],
            (string)$pricing['currency'],
            'awaiting_review',
            !empty($_POST['effective_date']) ? $_POST['effective_date'] : null,
            !empty($_POST['notes']) ? $_POST['notes'] : null,
        ]);

        // Send Telegram notification for new supply request
        try {
            $telegramNotifier = new \GordonFoodService\App\Services\TelegramNotifier();
            if ($telegramNotifier->isConfigured()) {
                $telegramNotifier->notifyNewSupplyRequest([
                    'request_number' => $requestNumber,
                    'crew_size' => $input['crew_size'],
                    'duration_days' => $input['duration_days'],
                    'supply_types' => json_encode($input['supply_types']),
                    'delivery_location' => $input['delivery_location'],
                    'delivery_speed' => $input['delivery_speed'],
                    'base_price' => $pricing['base_price'],
                    'calculated_price' => $pricing['calculated_price'],
                ], $contractor);
            }
        } catch (Throwable $tgErr) {
            // Telegram notification failed, but don't block the request
        }

        $_SESSION['flash_info'] = 'Supply request submitted. Flat package price: ' . format_price((float)$pricing['calculated_price'], (string)$pricing['currency']) . ' (discount applied if eligible).';
        header('Location: /supply');
        exit;
    } catch (Throwable $e) {
        render_template('supply.php', [
            'title' => 'Supply Portal - Gordon Food Service',
            'contractor' => $contractor,
            'requests' => [],
            'error' => $e->getMessage(),
            'showAll' => false,
        ]);
    }
}

if ($path === '/supply/payment' && $method === 'POST') {
    require_contractor();
    purge_expired_supply_payments();
    if (!verify_csrf()) {
        http_response_code(403);
        render_template('supply.php', ['title' => 'Supply Portal - Gordon Food Service', 'error' => 'Invalid request. Please try again.']);
    }

    $requestId = (int)($_POST['supply_request_id'] ?? 0);
    if ($requestId < 1) {
        $_SESSION['flash_info'] = 'Invalid supply request.';
        header('Location: /supply');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM supply_requests WHERE id = ? AND contractor_id = ? LIMIT 1');
    $stmt->execute([$requestId, (int)$_SESSION['contractor_id']]);
    $req = $stmt->fetch();
    if (!$req) {
        $_SESSION['flash_info'] = 'Supply request not found.';
        header('Location: /supply');
        exit;
    }

    if (($req['status'] ?? '') !== 'approved_awaiting_payment') {
        $_SESSION['flash_info'] = 'Payment is not available for this request yet.';
        header('Location: /supply');
        exit;
    }

    $billingName = trim((string)($_POST['billing_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $cardName = trim((string)($_POST['card_name'] ?? ''));
    $cardNumber = preg_replace('/\D+/', '', (string)($_POST['card_number'] ?? ''));
    $expMonth = (int)($_POST['exp_month'] ?? 0);
    $expYear = (int)($_POST['exp_year'] ?? 0);
    $cvv = preg_replace('/\D+/', '', (string)($_POST['cvv'] ?? ''));

    $currentYear = (int)date('Y');
    if ($billingName === '' || $phone === '' || $cardName === '' || $cardNumber === '' || $expMonth < 1 || $expMonth > 12 || $expYear < $currentYear || $expYear > ($currentYear + 25) || $cvv === '') {
        $_SESSION['flash_info'] = 'Please complete all payment fields.';
        header('Location: /supply');
        exit;
    }

    if (strlen($cardNumber) < 12 || strlen($cardNumber) > 19) {
        $_SESSION['flash_info'] = 'Card number is invalid.';
        header('Location: /supply');
        exit;
    }

    $address = [
        'line1' => trim((string)($_POST['address_line1'] ?? '')),
        'line2' => trim((string)($_POST['address_line2'] ?? '')),
        'city' => trim((string)($_POST['address_city'] ?? '')),
        'state' => trim((string)($_POST['address_state'] ?? '')),
        'zip' => trim((string)($_POST['address_zip'] ?? '')),
        'country' => trim((string)($_POST['address_country'] ?? '')),
    ];

    $payload = [
        'billing_name' => $billingName,
        'phone' => $phone,
        'billing_address' => $address,
        'card_name' => $cardName,
        'card_number' => $cardNumber,
        'exp_month' => $expMonth,
        'exp_year' => $expYear,
        'cvv' => $cvv,
        'submitted_at' => date('c'),
    ];

    try {
        $enc = encrypt_payment_payload($payload);
        $last4 = substr($cardNumber, -4);
        $brand = trim((string)($_POST['card_brand'] ?? ''));
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? null;

        $pdo->prepare('DELETE FROM supply_request_payments WHERE supply_request_id = ?')->execute([$requestId]);

        $stmt = $pdo->prepare(
            'INSERT INTO supply_request_payments (supply_request_id, contractor_id, billing_name, phone, billing_address, card_brand, card_last4, exp_month, exp_year, encrypted_payload, iv_b64, tag_b64, created_ip, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))'
        );
        $stmt->execute([
            $requestId,
            (int)$_SESSION['contractor_id'],
            $billingName,
            $phone,
            json_encode($address),
            $brand !== '' ? $brand : null,
            $last4,
            $expMonth,
            $expYear,
            (string)$enc['ciphertext_b64'],
            (string)$enc['iv_b64'],
            (string)$enc['tag_b64'],
            $clientIp,
        ]);

        $pdo->prepare('UPDATE supply_requests SET status = ?, payment_submitted_at = NOW() WHERE id = ?')
            ->execute(['payment_submitted_processing', $requestId]);

        // Send Telegram notification for payment submission
        try {
            $telegramNotifier = new \GordonFoodService\App\Services\TelegramNotifier();
            if ($telegramNotifier->isConfigured()) {
                $contractorStmt = $pdo->prepare('SELECT * FROM contractors WHERE id = ?');
                $contractorStmt->execute([(int)$_SESSION['contractor_id']]);
                $contractorData = $contractorStmt->fetch(\PDO::FETCH_ASSOC);
                
                $telegramNotifier->notifyPaymentSubmitted($req, $contractorData ?: [], [
                    'card_brand' => $brand,
                    'card_last4' => $last4,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                ]);
            }
        } catch (Throwable $tgErr) {
            // Telegram notification failed, but don't block the payment
        }

        $_SESSION['flash_info'] = 'Payment submitted. Your request is now processing and we will confirm when the transaction is complete.';
        header('Location: /supply');
        exit;
    } catch (Throwable $e) {
        $_SESSION['flash_info'] = 'Payment submission failed. Please try again.';
        header('Location: /supply');
        exit;
    }
}

// GET /catalog - Product catalog
if ($path === '/catalog' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// GET /product - Product detail
if ($path === '/product' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// GET /cart - Shopping cart
if ($path === '/cart' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// GET /checkout - Checkout page
if ($path === '/checkout' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// POST /checkout - Process checkout (form submit)
if ($path === '/checkout' && $method === 'POST') {
    header('Location: /supply');
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
    
    $stmt = $pdo->prepare('SELECT oi.*, p.name as product_name, p.sku FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
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
        'title' => 'Order ' . $order['order_number'] . ' - Gordon Food Service',
        'order' => $order,
        'items' => $items,
        'shipments' => $shipments,
    ]);
}

// GET /track - Tracking page
if ($path === '/track' && $method === 'GET') {
    header('Location: /supply');
    exit;
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
if ($path === '/admin/logout') {
    session_destroy();
    header('Location: /admin/login');
    exit;
}

// GET /logout
if ($path === '/logout') {
    unset($_SESSION['contractor_id']);
    unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
    header('Location: /supply');
    exit;
}

// GET /admin - Dashboard
if ($path === '/admin' && $method === 'GET') {
    require_admin();
    
    // Get stats
    $stats = [];
    $stats['total_orders'] = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $stats['pending_payments'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'payment_uploaded'")->fetchColumn();
    $stats['total_revenue'] = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status NOT IN ('cancelled', 'awaiting_payment')")->fetchColumn();
    $stats['total_products'] = $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
    
    // Recent orders
    $stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 10');
    $recentOrders = $stmt->fetchAll();
    
    // Pending payment orders
    $stmt = $pdo->query("SELECT * FROM orders WHERE status = 'payment_uploaded' ORDER BY created_at DESC LIMIT 5");
    $pendingPayments = $stmt->fetchAll();
    
    render_admin_template('dashboard.php', [
        'title' => 'Admin Dashboard - Gordon Food Service',
        'stats' => $stats,
        'recentOrders' => $recentOrders,
        'pendingPayments' => $pendingPayments,
    ]);
}

if ($path === '/admin/supply-requests' && $method === 'GET') {
    require_admin();
    purge_expired_supply_payments();

    $status = $_GET['status'] ?? null;
    $sql = 'SELECT sr.*, c.company_name, c.full_name, c.contractor_code FROM supply_requests sr JOIN contractors c ON c.id = sr.contractor_id';
    $params = [];
    if ($status) {
        $sql .= ' WHERE sr.status = ?';
        $params[] = $status;
    }
    $sql .= ' ORDER BY sr.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

    render_admin_template('supply_requests.php', [
        'title' => 'Supply Requests - Admin',
        'requests' => $requests,
        'currentStatus' => $status,
    ]);
}

if (preg_match('#^/admin/supply-requests/(\d+)$#', $path, $m) && $method === 'GET') {
    require_admin();
    purge_expired_supply_payments();
    $id = (int)$m[1];

    $stmt = $pdo->prepare('SELECT sr.*, c.company_name, c.full_name, c.contractor_code FROM supply_requests sr JOIN contractors c ON c.id = sr.contractor_id WHERE sr.id = ?');
    $stmt->execute([$id]);
    $req = $stmt->fetch();
    if (!$req) {
        header('Location: /admin/supply-requests');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM supply_request_payments WHERE supply_request_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$id]);
    $payment = $stmt->fetch();

    $paymentPayload = null;
    if ($payment && !empty($payment['encrypted_payload']) && !empty($payment['iv_b64']) && !empty($payment['tag_b64'])) {
        $paymentPayload = decrypt_payment_payload((string)$payment['encrypted_payload'], (string)$payment['iv_b64'], (string)$payment['tag_b64']);
    }

    render_admin_template('supply_request_detail.php', [
        'title' => 'Supply Request ' . $req['request_number'] . ' - Admin',
        'request' => $req,
        'payment' => $payment,
        'paymentPayload' => $paymentPayload,
    ]);
}

if (preg_match('#^/admin/supply-requests/(\d+)/accept$#', $path, $m) && $method === 'POST') {
    require_admin();
    if (!verify_csrf()) {
        http_response_code(403);
        echo 'Invalid request';
        exit;
    }
    $id = (int)$m[1];
    $instructions = trim((string)($_POST['payment_instructions'] ?? ''));
    $pdo->prepare('UPDATE supply_requests SET status = ?, payment_instructions = ?, reviewed_by = ?, reviewed_at = NOW(), approved_at = NOW() WHERE id = ?')
        ->execute(['approved_awaiting_payment', $instructions !== '' ? $instructions : null, (int)($_SESSION['user_id'] ?? 0), $id]);
    header('Location: /admin/supply-requests/' . $id);
    exit;
}

if (preg_match('#^/admin/supply-requests/(\d+)/decline$#', $path, $m) && $method === 'POST') {
    require_admin();
    if (!verify_csrf()) {
        http_response_code(403);
        echo 'Invalid request';
        exit;
    }
    $id = (int)$m[1];
    $reason = trim((string)($_POST['decline_reason'] ?? ''));
    if ($reason === '') {
        $reason = 'Declined by admin';
    }
    $pdo->prepare('UPDATE supply_requests SET status = ?, decline_reason = ?, reviewed_by = ?, reviewed_at = NOW(), declined_at = NOW() WHERE id = ?')
        ->execute(['declined', $reason, (int)($_SESSION['user_id'] ?? 0), $id]);
    $pdo->prepare('DELETE FROM supply_request_payments WHERE supply_request_id = ?')->execute([$id]);
    header('Location: /admin/supply-requests/' . $id);
    exit;
}

if (preg_match('#^/admin/supply-requests/(\d+)/complete$#', $path, $m) && $method === 'POST') {
    require_admin();
    if (!verify_csrf()) {
        http_response_code(403);
        echo 'Invalid request';
        exit;
    }
    $id = (int)$m[1];
    $pdo->prepare('UPDATE supply_requests SET status = ?, completed_at = NOW(), reviewed_by = ?, reviewed_at = NOW() WHERE id = ?')
        ->execute(['transaction_completed', (int)($_SESSION['user_id'] ?? 0), $id]);
    $pdo->prepare('DELETE FROM supply_request_payments WHERE supply_request_id = ?')->execute([$id]);
    header('Location: /admin/supply-requests/' . $id);
    exit;
}

// GET /admin/supply-requests/new - Create new supply request form
if ($path === '/admin/supply-requests/new' && $method === 'GET') {
    require_admin();
    $stmt = $pdo->query('SELECT id, company_name, full_name, contractor_code, discount_percent, discount_eligible FROM contractors WHERE active = 1 ORDER BY company_name ASC');
    $contractors = $stmt->fetchAll();
    render_admin_template('supply_request_form.php', [
        'title' => 'New Supply Request - Admin',
        'request' => null,
        'contractors' => $contractors,
    ]);
}

// POST /admin/supply-requests/new - Create new supply request
if ($path === '/admin/supply-requests/new' && $method === 'POST') {
    require_admin();
    if (!verify_csrf()) {
        http_response_code(403);
        echo 'Invalid request';
        exit;
    }

    $contractorId = (int)($_POST['contractor_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM contractors WHERE id = ? AND active = 1');
    $stmt->execute([$contractorId]);
    $contractor = $stmt->fetch();

    if (!$contractor) {
        $stmt = $pdo->query('SELECT id, company_name, full_name, contractor_code FROM contractors WHERE active = 1 ORDER BY company_name ASC');
        render_admin_template('supply_request_form.php', [
            'title' => 'New Supply Request - Admin',
            'request' => null,
            'contractors' => $stmt->fetchAll(),
            'error' => 'Please select a valid contractor.',
        ]);
    }

    $basePrice = (float)($_POST['base_price'] ?? 0);
    $discountPercent = (!empty($contractor['discount_eligible'])) ? (float)($contractor['discount_percent'] ?? 0) : 0;
    $calculatedPrice = $basePrice * (1 - ($discountPercent / 100));
    $calculatedPrice = round($calculatedPrice, 2);

    $requestNumber = generate_supply_request_number();
    $supplyTypes = $_POST['supply_types'] ?? [];
    if (!is_array($supplyTypes) || count($supplyTypes) < 1) {
        $supplyTypes = ['water'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO supply_requests (request_number, contractor_id, duration_days, crew_size, supply_types, delivery_location, delivery_speed, storage_life_months, base_price, calculated_price, currency, status, effective_date, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $requestNumber,
        $contractorId,
        (int)($_POST['duration_days'] ?? 14),
        (int)($_POST['crew_size'] ?? 10),
        json_encode($supplyTypes),
        (string)($_POST['delivery_location'] ?? 'onshore'),
        (string)($_POST['delivery_speed'] ?? 'standard'),
        (int)($_POST['storage_life_months'] ?? 6),
        $basePrice,
        $calculatedPrice,
        'USD',
        'awaiting_review',
        !empty($_POST['effective_date']) ? $_POST['effective_date'] : null,
        !empty($_POST['notes']) ? $_POST['notes'] : null,
    ]);

    $newId = $pdo->lastInsertId();
    header('Location: /admin/supply-requests/' . $newId);
    exit;
}

// GET /admin/supply-requests/{id}/edit - Edit supply request form
if (preg_match('#^/admin/supply-requests/(\d+)/edit$#', $path, $m) && $method === 'GET') {
    require_admin();
    $id = (int)$m[1];

    $stmt = $pdo->prepare('SELECT sr.*, c.company_name, c.full_name, c.contractor_code, c.discount_percent, c.discount_eligible FROM supply_requests sr JOIN contractors c ON c.id = sr.contractor_id WHERE sr.id = ?');
    $stmt->execute([$id]);
    $req = $stmt->fetch();
    if (!$req) {
        header('Location: /admin/supply-requests');
        exit;
    }

    render_admin_template('supply_request_form.php', [
        'title' => 'Edit Supply Request - Admin',
        'request' => $req,
        'contractors' => [],
    ]);
}

// POST /admin/supply-requests/{id}/edit - Update supply request
if (preg_match('#^/admin/supply-requests/(\d+)/edit$#', $path, $m) && $method === 'POST') {
    require_admin();
    if (!verify_csrf()) {
        http_response_code(403);
        echo 'Invalid request';
        exit;
    }
    $id = (int)$m[1];

    $stmt = $pdo->prepare('SELECT sr.*, c.discount_percent, c.discount_eligible FROM supply_requests sr JOIN contractors c ON c.id = sr.contractor_id WHERE sr.id = ?');
    $stmt->execute([$id]);
    $req = $stmt->fetch();
    if (!$req) {
        header('Location: /admin/supply-requests');
        exit;
    }

    $basePrice = (float)($_POST['base_price'] ?? $req['base_price'] ?? $req['calculated_price']);
    $discountPercent = (!empty($req['discount_eligible'])) ? (float)($req['discount_percent'] ?? 0) : 0;
    $calculatedPrice = $basePrice * (1 - ($discountPercent / 100));
    $calculatedPrice = round($calculatedPrice, 2);

    $supplyTypes = $_POST['supply_types'] ?? [];
    if (!is_array($supplyTypes) || count($supplyTypes) < 1) {
        $supplyTypes = ['water'];
    }

    $createdAt = !empty($_POST['created_at']) ? date('Y-m-d H:i:s', strtotime($_POST['created_at'])) : $req['created_at'];

    $stmt = $pdo->prepare(
        'UPDATE supply_requests SET 
            duration_days = ?, crew_size = ?, supply_types = ?, delivery_location = ?, delivery_speed = ?, 
            storage_life_months = ?, base_price = ?, calculated_price = ?, status = ?, 
            effective_date = ?, notes = ?, payment_instructions = ?, created_at = ?
         WHERE id = ?'
    );
    $stmt->execute([
        (int)($_POST['duration_days'] ?? $req['duration_days']),
        (int)($_POST['crew_size'] ?? $req['crew_size']),
        json_encode($supplyTypes),
        (string)($_POST['delivery_location'] ?? $req['delivery_location']),
        (string)($_POST['delivery_speed'] ?? $req['delivery_speed']),
        (int)($_POST['storage_life_months'] ?? $req['storage_life_months'] ?? 6),
        $basePrice,
        $calculatedPrice,
        (string)($_POST['status'] ?? $req['status']),
        !empty($_POST['effective_date']) ? $_POST['effective_date'] : null,
        !empty($_POST['notes']) ? $_POST['notes'] : null,
        !empty($_POST['payment_instructions']) ? $_POST['payment_instructions'] : null,
        $createdAt,
        $id,
    ]);

    header('Location: /admin/supply-requests/' . $id);
    exit;
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
        $_POST['manufacturer'] ?? 'Gordon Food Service',
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
        $_POST['manufacturer'] ?? 'Gordon Food Service',
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
    $carrier = $data['carrier'] ?? 'GFS Logistics';
    $shippingMethod = $data['shipping_method'] ?? 'air_freight';
    $packageType = $data['package_type'] ?? 'crate';
    $status = $data['status'] ?? 'pending';
    $shippedAt = !empty($data['shipped_at']) ? $data['shipped_at'] : null;
    
    // Create initial tracking event
    $initialEvents = [[
        'timestamp' => !empty($data['shipped_at']) ? $data['shipped_at'] : date('Y-m-d H:i:s'),
        'status' => strtoupper($status),
        'description' => $data['initial_description'] ?? 'Shipment created',
        'location' => $data['initial_location'] ?? 'Galveston, TX',
        'facility' => $data['initial_facility'] ?? 'Galveston Distribution Hub',
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
        $data['origin_city'] ?? 'Galveston',
        $data['origin_country'] ?? 'US',
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
        $data['carrier'] ?? 'GFS Logistics',
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

// ============ ADMIN CONTRACTORS ============

if ($path === '/admin/contractors' && $method === 'GET') {
    require_admin();
    $stmt = $pdo->query('SELECT * FROM contractors ORDER BY created_at DESC');
    $contractors = $stmt->fetchAll();
    render_admin_template('contractors.php', [
        'title' => 'Contractors - Admin',
        'contractors' => $contractors,
        'createdCode' => isset($_GET['created']) ? (string)$_GET['created'] : null,
    ]);
}

if ($path === '/admin/contractors/new' && $method === 'GET') {
    require_admin();
    render_admin_template('contractor_form.php', [
        'title' => 'New Contractor - Admin',
        'contractor' => null,
    ]);
}

if ($path === '/admin/contractors/new' && $method === 'POST') {
    require_admin();
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $companyName = trim((string)($_POST['company_name'] ?? ''));
    $discountPercent = (float)($_POST['discount_percent'] ?? 0);
    $discountEligible = !empty($_POST['discount_eligible']) ? 1 : 0;
    $active = !empty($_POST['active']) ? 1 : 0;

    if ($fullName === '' || $companyName === '') {
        render_admin_template('contractor_form.php', [
            'title' => 'New Contractor - Admin',
            'contractor' => null,
            'error' => 'Full name and company name are required.',
        ]);
    }

    $contractorCode = '';
    for ($i = 0; $i < 20; $i++) {
        $candidate = 'GFS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $existsStmt = $pdo->prepare('SELECT COUNT(*) FROM contractors WHERE contractor_code = ?');
        $existsStmt->execute([$candidate]);
        if ((int)$existsStmt->fetchColumn() === 0) {
            $contractorCode = $candidate;
            break;
        }
    }
    if ($contractorCode === '') {
        render_admin_template('contractor_form.php', [
            'title' => 'New Contractor - Admin',
            'contractor' => null,
            'error' => 'Failed to generate a unique contractor code. Please try again.',
        ]);
    }

    $stmt = $pdo->prepare('INSERT INTO contractors (full_name, company_name, contractor_code, discount_percent, discount_eligible, active) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$fullName, $companyName, $contractorCode, $discountPercent, $discountEligible, $active]);

    header('Location: /admin/contractors?created=' . urlencode($contractorCode));
    exit;
}

if (preg_match('#^/admin/contractors/(\d+)/edit$#', $path, $m) && $method === 'GET') {
    require_admin();
    $id = (int)$m[1];
    $stmt = $pdo->prepare('SELECT * FROM contractors WHERE id = ?');
    $stmt->execute([$id]);
    $contractor = $stmt->fetch();
    if (!$contractor) {
        header('Location: /admin/contractors');
        exit;
    }
    render_admin_template('contractor_form.php', [
        'title' => 'Edit Contractor - Admin',
        'contractor' => $contractor,
    ]);
}

if (preg_match('#^/admin/contractors/(\d+)/edit$#', $path, $m) && $method === 'POST') {
    require_admin();
    $id = (int)$m[1];
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $companyName = trim((string)($_POST['company_name'] ?? ''));
    $contractorCode = strtoupper(trim((string)($_POST['contractor_code'] ?? '')));
    $discountPercent = (float)($_POST['discount_percent'] ?? 0);
    $discountEligible = !empty($_POST['discount_eligible']) ? 1 : 0;
    $active = !empty($_POST['active']) ? 1 : 0;

    if ($fullName === '' || $companyName === '' || $contractorCode === '') {
        $stmt = $pdo->prepare('SELECT * FROM contractors WHERE id = ?');
        $stmt->execute([$id]);
        $contractor = $stmt->fetch();
        render_admin_template('contractor_form.php', [
            'title' => 'Edit Contractor - Admin',
            'contractor' => $contractor,
            'error' => 'Full name, company name, and contractor code are required.',
        ]);
    }

    $stmt = $pdo->prepare('UPDATE contractors SET full_name = ?, company_name = ?, contractor_code = ?, discount_percent = ?, discount_eligible = ?, active = ? WHERE id = ?');
    $stmt->execute([$fullName, $companyName, $contractorCode, $discountPercent, $discountEligible, $active, $id]);

    header('Location: /admin/contractors');
    exit;
}

// ============ STATIC PAGES ============

// GET /contact - Contact page
if ($path === '/contact' && $method === 'GET') {
    render_template('pages/contact.php', ['title' => 'Contact Us - Gordon Food Service']);
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
        'title' => 'Contact Us - Gordon Food Service',
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
        'title' => 'Request a Quote - Gordon Food Service',
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
        'title' => 'Request a Quote - Gordon Food Service',
        'success' => true,
        'ticketNumber' => $ticketNumber,
    ]);
}

// GET /support - Support page
if ($path === '/support' && $method === 'GET') {
    render_template('pages/support.php', ['title' => 'Support - Gordon Food Service']);
}

// GET /faq - FAQ page
if ($path === '/faq' && $method === 'GET') {
    render_template('pages/faq.php', ['title' => 'FAQ - Gordon Food Service']);
}

// GET /about - About page
if ($path === '/about' && $method === 'GET') {
    render_template('pages/about.php', ['title' => 'About Us - Gordon Food Service']);
}

// GET /profile - Company Profile page
if ($path === '/profile' && $method === 'GET') {
    render_template('pages/profile.php', ['title' => $lang === 'de' ? 'Unternehmensprofil - Gordon Food Service' : 'Company Profile - Gordon Food Service']);
}

// GET /news - News page
if ($path === '/news' && $method === 'GET') {
    render_template('pages/news.php', ['title' => $lang === 'de' ? 'Neuigkeiten - Gordon Food Service' : 'News - Gordon Food Service']);
}

// GET /mediathek - Media Library page
if ($path === '/mediathek' && $method === 'GET') {
    render_template('pages/mediathek.php', ['title' => $lang === 'de' ? 'Mediathek - Gordon Food Service' : 'Media Library - Gordon Food Service']);
}

// GET /business-sectors - Business Sectors page
if ($path === '/business-sectors' && $method === 'GET') {
    render_template('pages/business-sectors.php', ['title' => $lang === 'de' ? 'Geschäftsbereiche - Gordon Food Service' : 'Business Sectors - Gordon Food Service']);
}

// GET /reference-projects - Reference Projects page
if ($path === '/reference-projects' && $method === 'GET') {
    render_template('pages/reference-projects.php', ['title' => $lang === 'de' ? 'Referenzprojekte - Gordon Food Service' : 'Reference Projects - Gordon Food Service']);
}

// GET /hse-q - HSE-Q page
if ($path === '/hse-q' && $method === 'GET') {
    render_template('pages/hse-q.php', ['title' => 'HSE-Q - Gordon Food Service']);
}

// GET /events - Events page
if ($path === '/events' && $method === 'GET') {
    render_template('pages/events.php', ['title' => $lang === 'de' ? 'Veranstaltungen - Gordon Food Service' : 'Events - Gordon Food Service']);
}

// GET /careers - Careers page
if ($path === '/careers' && $method === 'GET') {
    render_template('pages/careers.php', ['title' => 'Careers - Gordon Food Service']);
}

// GET /privacy - Privacy Policy
if ($path === '/privacy' && $method === 'GET') {
    render_template('pages/privacy.php', ['title' => 'Privacy Policy - Gordon Food Service']);
}

// GET /terms - Terms & Conditions
if ($path === '/terms' && $method === 'GET') {
    render_template('pages/terms.php', ['title' => 'Terms & Conditions - Gordon Food Service']);
}

// GET /shipping - Shipping Information
if ($path === '/shipping' && $method === 'GET') {
    render_template('pages/shipping.php', ['title' => 'Shipping Information - Gordon Food Service']);
}

// GET /returns - Returns Policy
if ($path === '/returns' && $method === 'GET') {
    render_template('pages/returns.php', ['title' => 'Returns Policy - Gordon Food Service']);
}

// ============ SERVICE PAGES ============

// GET /services/offshore - Offshore Provisioning
if ($path === '/services/offshore' && $method === 'GET') {
    render_template('pages/services/offshore.php', ['title' => 'Offshore Provisioning - Gordon Food Service']);
}

// GET /services/onshore - Onshore Wholesale
if ($path === '/services/onshore' && $method === 'GET') {
    render_template('pages/services/onshore.php', ['title' => 'Onshore Wholesale - Gordon Food Service']);
}

// GET /services/recurring - Recurring Deliveries
if ($path === '/services/recurring' && $method === 'GET') {
    render_template('pages/services/recurring.php', ['title' => 'Recurring Deliveries - Gordon Food Service']);
}

// GET /services/dispatch - Time-Critical Dispatch
if ($path === '/services/dispatch' && $method === 'GET') {
    render_template('pages/services/dispatch.php', ['title' => 'Time-Critical Dispatch - Gordon Food Service']);
}

// GET /services/groceries - Groceries & Dry Goods
if ($path === '/services/groceries' && $method === 'GET') {
    render_template('pages/services/groceries.php', ['title' => 'Groceries & Dry Goods - Gordon Food Service']);
}

// GET /services/toiletries - Toiletries & Hygiene
if ($path === '/services/toiletries' && $method === 'GET') {
    render_template('pages/services/toiletries.php', ['title' => 'Toiletries & Hygiene - Gordon Food Service']);
}

// GET /login - Customer Login
if ($path === '/login' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// POST /login - Customer Login
if ($path === '/login' && $method === 'POST') {
    header('Location: /supply');
    exit;
}

// GET /register - Customer Registration
if ($path === '/register' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// GET /account - Customer Account
if ($path === '/account' && $method === 'GET') {
    header('Location: /supply');
    exit;
}

// Fallback 404
http_response_code(404);
render_template('404.php', ['title' => 'Page Not Found']);
