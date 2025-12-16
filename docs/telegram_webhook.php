<?php
/**
 * Telegram Webhook Handler
 * Receives messages from Telegram and routes admin replies to users
 */

declare(strict_types=1);

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            putenv(trim($line));
        }
    }
}

$botToken = getenv('TELEGRAM_BOT_TOKEN') ?: $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;

if (!$botToken) {
    http_response_code(500);
    exit('Bot token not configured');
}

// Get the update from Telegram
$content = file_get_contents('php://input');
$update = json_decode($content, true);

if (!$update) {
    http_response_code(400);
    exit('Invalid request');
}

// Log the update for debugging
file_put_contents(__DIR__ . '/../logs/telegram_webhook.log', date('Y-m-d H:i:s') . " - " . $content . "\n", FILE_APPEND);

// Database connection
$dbHost = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'Gordon Food Service';
$dbUser = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$dbPort = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed');
}

// Process the message
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    
    // Check if this is a reply command: /reply TRACKING_NUMBER message
    if (preg_match('/^\/reply\s+([A-Z0-9]+)\s+(.+)$/is', $text, $matches)) {
        $trackingNumber = strtoupper(trim($matches[1]));
        $replyMessage = trim($matches[2]);
        
        // Find the shipment by tracking number
        $stmt = $pdo->prepare('SELECT id, order_id FROM shipments WHERE tracking_number = ?');
        $stmt->execute([$trackingNumber]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shipment) {
            // Insert the admin reply into tracking_communications
            $stmt = $pdo->prepare(
                'INSERT INTO tracking_communications (order_id, tracking_number, sender_type, sender_name, message_type, message)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $shipment['order_id'],
                $trackingNumber,
                'admin',
                'Gordon Food Service Support (via Telegram)',
                'message',
                $replyMessage,
            ]);
            
            // Send confirmation to admin
            sendTelegramMessage($botToken, $chatId, "✅ Reply sent to tracking $trackingNumber:\n\n$replyMessage");
        } else {
            sendTelegramMessage($botToken, $chatId, "❌ Tracking number not found: $trackingNumber");
        }
    }
    // Help command
    elseif ($text === '/start' || $text === '/help') {
        $helpMessage = "🤖 *Gordon Food Service Admin Bot*\n\n";
        $helpMessage .= "I notify you when customers send messages.\n\n";
        $helpMessage .= "*Commands:*\n";
        $helpMessage .= "/reply TRACKING_NUMBER Your message - Reply to a customer\n";
        $helpMessage .= "/help - Show this help message\n\n";
        $helpMessage .= "*Example:*\n";
        $helpMessage .= "`/reply STR20251213ABC123 Your shipment is on the way!`";
        
        sendTelegramMessage($botToken, $chatId, $helpMessage, 'Markdown');
    }
    // Unknown command
    else {
        sendTelegramMessage($botToken, $chatId, "Use /reply TRACKING_NUMBER message to reply to customers.\nUse /help for more info.");
    }
}

http_response_code(200);
echo 'OK';

function sendTelegramMessage(string $token, int $chatId, string $text, string $parseMode = null): bool {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
    ];
    if ($parseMode) {
        $data['parse_mode'] = $parseMode;
    }
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data),
            'timeout' => 10,
        ],
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    return $result !== false;
}
