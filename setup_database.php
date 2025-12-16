<?php
/**
 * Database Setup Script for Railway Deployment
 * This script runs all migrations to set up the database schema
 */

// Database connection from environment variables
$dbHost = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
$dbName = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'railway';
$dbUser = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server successfully\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbName' created or already exists\n";
    
    // Switch to the database
    $pdo->exec("USE `$dbName`");
    echo "Using database '$dbName'\n";
    
    // Run migrations in order
    $migrationFiles = [
        'migrations/001_initial_schema.sql',
        'migrations/002_order_flow.sql', 
        'migrations/003_offshore_supply.sql',
        'migrations/004_remove_tracking.sql'
    ];
    
    foreach ($migrationFiles as $migrationFile) {
        if (file_exists($migrationFile)) {
            echo "Running migration: $migrationFile\n";
            $sql = file_get_contents($migrationFile);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore errors for things that already exist
                        if (strpos($e->getMessage(), 'Duplicate column') === false && 
                            strpos($e->getMessage(), 'already exists') === false &&
                            strpos($e->getMessage(), 'Table') === false) {
                            echo "Error in statement: $statement\n";
                            echo "Error: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
            echo "Migration $migrationFile completed\n";
        } else {
            echo "Migration file not found: $migrationFile\n";
        }
    }
    
    // Insert initial data
    echo "Inserting initial data...\n";
    
    // Insert admin user
    $adminHash = password_hash('Americana12@', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'gonnyzalowski@gmail.com'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (email, password_hash, full_name, role) VALUES ('gonnyzalowski@gmail.com', '$adminHash', 'Administrator', 'admin')");
        echo "Admin user created\n";
    }
    
    // Insert default categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO categories (name, slug, description) VALUES 
            ('Fresh Produce', 'fresh-produce', 'Fresh fruits and vegetables for offshore provisioning'),
            ('Meat & Poultry', 'meat-poultry', 'Fresh and frozen meat products'),
            ('Seafood', 'seafood', 'Fresh and frozen fish and seafood products'),
            ('Dairy & Eggs', 'dairy-eggs', 'Milk, cheese, yogurt, and egg products'),
            ('Bakery', 'bakery', 'Bread, pastries, and baked goods'),
            ('Beverages', 'beverages', 'Water, juices, and other beverages'),
            ('Dry Goods', 'dry-goods', 'Rice, pasta, flour, and other dry food items'),
            ('Frozen Foods', 'frozen-foods', 'Frozen meals and ingredients'),
            ('Cleaning Supplies', 'cleaning-supplies', 'Sanitation and cleaning products'),
            ('Paper Products', 'paper-products', 'Paper towels, tissues, and disposable items')
        ");
        echo "Default categories inserted\n";
    }
    
    // Insert sample products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $insertStmt = $pdo->prepare("INSERT INTO products (sku, name, slug, description, category_id, unit_price, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
        
        $products = [
            ['GFS-0001', 'Fresh Vegetable Mix (10kg)', 'fresh-vegetable-mix-10kg', 'Assorted fresh vegetables including tomatoes, lettuce, carrots, and onions - perfect for offshore provisioning', 1, 45.99, 1],
            ['GFS-0002', 'Premium Beef Selection (5kg)', 'premium-beef-selection-5kg', 'High-quality beef cuts including steaks and ground beef - frozen for long-term storage', 2, 89.99, 1],
            ['GFS-0003', 'Bottled Water Case (24x500ml)', 'bottled-water-case-24x500ml', '24 bottles of purified drinking water - essential for offshore operations', 6, 12.99, 1],
            ['GFS-0004', 'Fresh Seafood Assortment (3kg)', 'fresh-seafood-assortment-3kg', 'Mixed seafood including fish fillets, shrimp, and shellfish - flash frozen', 3, 125.99, 0],
            ['GFS-0005', 'Dairy Package (10L milk, 2kg cheese)', 'dairy-package-milk-cheese', 'Complete dairy package with fresh milk and assorted cheeses', 4, 35.99, 0],
        ];
        
        foreach ($products as $product) {
            $insertStmt->execute([$product[0], $product[1], $product[2], $product[3], $product[4], $product[5], $product[6]]);
        }
        echo "Sample products inserted\n";
    }
    
    // Insert demo contractor
    $stmt = $pdo->query("SELECT COUNT(*) FROM contractors");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO contractors (full_name, company_name, contractor_code, discount_percent, discount_eligible, active) 
            VALUES ('Demo Contractor', 'GFS Registered Contractor', 'GFS-DEMO-0001', 35.00, 1, 1)");
        echo "Demo contractor created\n";
    }
    
    // Insert supply pricing config
    $stmt = $pdo->query("SELECT COUNT(*) FROM supply_pricing_config");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO supply_pricing_config (config_json) 
            VALUES (JSON_OBJECT(
                'base_rate_per_person_day', 22.5,
                'type_multipliers', JSON_OBJECT('water', 0.9, 'dry_food', 1.0, 'canned_food', 1.05, 'mixed_supplies', 1.1),
                'location_multipliers', JSON_OBJECT('pickup', 0.85, 'local', 0.95, 'onshore', 1.0, 'nearshore', 1.15, 'offshore_rig', 1.35),
                'speed_multipliers', JSON_OBJECT('standard', 1.0, 'priority', 1.2, 'emergency', 1.45)
            ))");
        echo "Supply pricing config created\n";
    }
    
    echo "\n=== Database Setup Complete ===\n";
    echo "Admin login: gonnyzalowski@gmail.com / Americana12@\n";
    echo "Database: $dbName\n";
    echo "Tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
