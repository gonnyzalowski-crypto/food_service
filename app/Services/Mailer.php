<?php

declare(strict_types=1);

namespace Streicher\App\Services;

/**
 * Simple SMTP mailer for sending emails.
 * Works with Mailpit (local dev) or any SMTP service (Sendgrid, Mailgun, etc.)
 */
class Mailer
{
    private string $host;
    private int $port;
    private ?string $username;
    private ?string $password;
    private ?string $encryption;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->host = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->port = (int)($_ENV['MAIL_PORT'] ?? 1025);
        $this->username = $_ENV['MAIL_USERNAME'] ?: null;
        $this->password = $_ENV['MAIL_PASSWORD'] ?: null;
        $this->encryption = $_ENV['MAIL_ENCRYPTION'] ?: null;
        $this->fromAddress = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@streicher.de';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Streicher GmbH';
    }

    /**
     * Send an email using SMTP.
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Plain text body
     * @return bool Success
     */
    public function send(string $to, string $subject, string $body): bool
    {
        try {
            // Open SMTP connection
            $socket = @fsockopen(
                $this->encryption === 'ssl' ? "ssl://{$this->host}" : $this->host,
                $this->port,
                $errno,
                $errstr,
                30
            );

            if (!$socket) {
                error_log("Mailer: Could not connect to {$this->host}:{$this->port} - $errstr");
                return false;
            }

            // Read greeting
            $this->readResponse($socket);

            // EHLO
            $this->sendCommand($socket, "EHLO localhost");

            // STARTTLS if needed
            if ($this->encryption === 'tls') {
                $this->sendCommand($socket, "STARTTLS");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand($socket, "EHLO localhost");
            }

            // AUTH if credentials provided
            if ($this->username && $this->password) {
                $this->sendCommand($socket, "AUTH LOGIN");
                $this->sendCommand($socket, base64_encode($this->username));
                $this->sendCommand($socket, base64_encode($this->password));
            }

            // MAIL FROM
            $this->sendCommand($socket, "MAIL FROM:<{$this->fromAddress}>");

            // RCPT TO
            $this->sendCommand($socket, "RCPT TO:<{$to}>");

            // DATA
            $this->sendCommand($socket, "DATA");

            // Headers + body
            $message = "From: {$this->fromName} <{$this->fromAddress}>\r\n";
            $message .= "To: {$to}\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "\r\n";
            $message .= $body;
            $message .= "\r\n.";

            $this->sendCommand($socket, $message);

            // QUIT
            $this->sendCommand($socket, "QUIT");

            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            error_log("Mailer error: " . $e->getMessage());
            return false;
        }
    }

    private function sendCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->readResponse($socket);
    }

    private function readResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Render an email template with variables.
     *
     * @param string $templatePath Path to template file
     * @param array $vars Variables to extract into template
     * @return string Rendered content
     */
    public static function renderTemplate(string $templatePath, array $vars): string
    {
        extract($vars);
        ob_start();
        require $templatePath;
        return ob_get_clean();
    }
}
