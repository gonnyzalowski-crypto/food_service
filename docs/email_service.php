<?php
/**
 * Email Service for Gordon Food Service Admin Dashboard
 * 
 * Requirements to make this work:
 * 1. SMTP credentials in .env file:
 *    - MAIL_HOST (e.g., smtp.gmail.com, smtp.sendgrid.net)
 *    - MAIL_PORT (e.g., 587 for TLS, 465 for SSL)
 *    - MAIL_USERNAME (your email/API key)
 *    - MAIL_PASSWORD (your password/API key)
 *    - MAIL_FROM_ADDRESS (e.g., store@Gordon Food Servicegmbh.com)
 *    - MAIL_FROM_NAME (e.g., Gordon Food Service GmbH)
 * 
 * Recommended providers:
 * - SendGrid (free tier: 100 emails/day)
 * - Mailgun (free tier: 5,000 emails/month)
 * - Amazon SES (very cheap, ~$0.10 per 1000 emails)
 * - Gmail SMTP (limited to 500/day, requires app password)
 */

class EmailService {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromAddress;
    private $fromName;
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->host = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $this->username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->fromAddress = $_ENV['MAIL_FROM_ADDRESS'] ?? 'store@Gordon Food Servicegmbh.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Gordon Food Service GmbH';
    }
    
    /**
     * Send an email using SMTP
     */
    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool {
        // Log the email attempt
        $this->logEmail($to, $subject, $body, 'pending');
        
        // If no SMTP configured, use PHP mail() as fallback
        if (empty($this->username) || empty($this->password)) {
            return $this->sendWithPhpMail($to, $subject, $body, $isHtml);
        }
        
        try {
            // Use socket-based SMTP (no external dependencies)
            $result = $this->sendWithSmtp($to, $subject, $body, $isHtml);
            $this->updateEmailStatus($to, $subject, $result ? 'sent' : 'failed');
            return $result;
        } catch (Exception $e) {
            $this->updateEmailStatus($to, $subject, 'failed', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send using PHP's built-in mail function
     */
    private function sendWithPhpMail(string $to, string $subject, string $body, bool $isHtml): bool {
        $headers = [
            'From' => $this->fromName . ' <' . $this->fromAddress . '>',
            'Reply-To' => $this->fromAddress,
            'X-Mailer' => 'PHP/' . phpversion(),
        ];
        
        if ($isHtml) {
            $headers['MIME-Version'] = '1.0';
            $headers['Content-type'] = 'text/html; charset=UTF-8';
        }
        
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }
        
        $result = @mail($to, $subject, $body, $headerString);
        $this->updateEmailStatus($to, $subject, $result ? 'sent' : 'failed');
        return $result;
    }
    
    /**
     * Send using SMTP socket connection
     */
    private function sendWithSmtp(string $to, string $subject, string $body, bool $isHtml): bool {
        $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if (!$socket) {
            throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
        }
        
        // Read greeting
        $this->smtpRead($socket);
        
        // EHLO
        $this->smtpWrite($socket, "EHLO " . gethostname());
        $this->smtpRead($socket);
        
        // STARTTLS if port 587
        if ($this->port == 587) {
            $this->smtpWrite($socket, "STARTTLS");
            $this->smtpRead($socket);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->smtpWrite($socket, "EHLO " . gethostname());
            $this->smtpRead($socket);
        }
        
        // AUTH LOGIN
        $this->smtpWrite($socket, "AUTH LOGIN");
        $this->smtpRead($socket);
        $this->smtpWrite($socket, base64_encode($this->username));
        $this->smtpRead($socket);
        $this->smtpWrite($socket, base64_encode($this->password));
        $response = $this->smtpRead($socket);
        
        if (strpos($response, '235') === false) {
            fclose($socket);
            throw new Exception("SMTP authentication failed");
        }
        
        // MAIL FROM
        $this->smtpWrite($socket, "MAIL FROM:<{$this->fromAddress}>");
        $this->smtpRead($socket);
        
        // RCPT TO
        $this->smtpWrite($socket, "RCPT TO:<$to>");
        $this->smtpRead($socket);
        
        // DATA
        $this->smtpWrite($socket, "DATA");
        $this->smtpRead($socket);
        
        // Headers and body
        $contentType = $isHtml ? 'text/html' : 'text/plain';
        $message = "From: {$this->fromName} <{$this->fromAddress}>\r\n";
        $message .= "To: $to\r\n";
        $message .= "Subject: $subject\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: $contentType; charset=UTF-8\r\n";
        $message .= "\r\n";
        $message .= $body;
        $message .= "\r\n.";
        
        $this->smtpWrite($socket, $message);
        $this->smtpRead($socket);
        
        // QUIT
        $this->smtpWrite($socket, "QUIT");
        fclose($socket);
        
        return true;
    }
    
    private function smtpWrite($socket, string $data): void {
        fwrite($socket, $data . "\r\n");
    }
    
    private function smtpRead($socket): string {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $response;
    }
    
    /**
     * Log email to database
     */
    private function logEmail(string $to, string $subject, string $body, string $status): void {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO email_logs (to_email, subject, body, status, created_at) VALUES (?, ?, ?, ?, NOW())'
            );
            $stmt->execute([$to, $subject, $body, $status]);
        } catch (Exception $e) {
            // Table might not exist yet
        }
    }
    
    private function updateEmailStatus(string $to, string $subject, string $status, string $error = null): void {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE email_logs SET status = ?, error = ?, sent_at = NOW() WHERE to_email = ? AND subject = ? ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute([$status, $error, $to, $subject]);
        } catch (Exception $e) {
            // Ignore
        }
    }
    
    /**
     * Get email templates
     */
    public function getOrderConfirmationEmail(array $order): string {
        $orderNumber = $order['order_number'];
        $total = number_format((float)$order['total'], 2);
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0066cc; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
        </div>
        <div class="content">
            <p>Thank you for your order!</p>
            <p><strong>Order Number:</strong> {$orderNumber}</p>
            <p><strong>Total:</strong> €{$total}</p>
            <p>Please transfer the payment to our bank account and upload your receipt.</p>
        </div>
        <div class="footer">
            <p>Gordon Food Service GmbH | Industriestraße 45, 93055 Regensburg, Germany</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    public function getPaymentConfirmedEmail(array $order): string {
        $orderNumber = $order['order_number'];
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #16a34a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Payment Confirmed</h1>
        </div>
        <div class="content">
            <p>Great news! Your payment for order <strong>{$orderNumber}</strong> has been confirmed.</p>
            <p>We are now processing your order and will send you tracking information once shipped.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
