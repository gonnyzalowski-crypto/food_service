<?php

declare(strict_types=1);

namespace GordonFoodService\App\Services;

class TelegramNotifier
{
    private string $botToken;
    private string $chatId;

    public function __construct(?string $botToken = null, ?string $chatId = null)
    {
        $this->botToken = $botToken ?? ($_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN') ?: '');
        $this->chatId = $chatId ?? ($_ENV['TELEGRAM_USER_ID'] ?? getenv('TELEGRAM_USER_ID') ?: '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    public function send(string $message): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public function notifyNewSupplyRequest(array $request, array $contractor): bool
    {
        $message = "🆕 <b>New Supply Request</b>\n\n";
        $message .= "📋 <b>Request:</b> {$request['request_number']}\n";
        $message .= "🏢 <b>Contractor:</b> {$contractor['company_name']}\n";
        $message .= "👤 <b>Contact:</b> {$contractor['full_name']}\n";
        $message .= "🔑 <b>Code:</b> <code>{$contractor['contractor_code']}</code>\n\n";
        $message .= "👥 <b>Crew:</b> {$request['crew_size']} people\n";
        $message .= "📅 <b>Duration:</b> {$request['duration_days']} days\n";
        
        $types = json_decode($request['supply_types'] ?? '[]', true) ?: [];
        $typesStr = implode(', ', array_map(fn($t) => str_replace('_', ' ', $t), $types));
        $message .= "📦 <b>Supplies:</b> {$typesStr}\n";
        $message .= "📍 <b>Delivery:</b> {$request['delivery_location']} ({$request['delivery_speed']})\n\n";
        
        $basePrice = number_format((float)($request['base_price'] ?? $request['calculated_price']), 2);
        $discountedPrice = number_format((float)$request['calculated_price'], 2);
        $message .= "💰 <b>Price:</b> \${$basePrice}\n";
        $message .= "💵 <b>Discounted:</b> \${$discountedPrice}\n\n";
        $message .= "⏳ <b>Status:</b> Awaiting Review\n\n";
        $message .= "👉 Review in admin panel";

        return $this->send($message);
    }

    public function notifyPaymentSubmitted(array $request, array $contractor, array $payment): bool
    {
        $message = "💳 <b>Payment Submitted</b>\n\n";
        $message .= "📋 <b>Request:</b> {$request['request_number']}\n";
        $message .= "🏢 <b>Contractor:</b> {$contractor['company_name']}\n";
        $message .= "👤 <b>Contact:</b> {$contractor['full_name']}\n\n";
        
        $price = number_format((float)$request['calculated_price'], 2);
        $message .= "💰 <b>Amount:</b> \${$price}\n";
        $message .= "💳 <b>Card:</b> {$payment['card_brand']} ****{$payment['card_last4']}\n";
        $message .= "📅 <b>Expires:</b> {$payment['exp_month']}/{$payment['exp_year']}\n\n";
        $message .= "⚠️ <b>Action Required:</b> Process payment and mark complete\n\n";
        $message .= "👉 Review in admin panel";

        return $this->send($message);
    }

    public function notifyRequestAccepted(array $request, array $contractor): bool
    {
        $message = "✅ <b>Request Accepted</b>\n\n";
        $message .= "📋 <b>Request:</b> {$request['request_number']}\n";
        $message .= "🏢 <b>Contractor:</b> {$contractor['company_name']}\n";
        $message .= "💰 <b>Amount:</b> \$" . number_format((float)$request['calculated_price'], 2) . "\n\n";
        $message .= "📧 Contractor has been notified to submit payment.";

        return $this->send($message);
    }

    public function notifyRequestDeclined(array $request, array $contractor, string $reason): bool
    {
        $message = "❌ <b>Request Declined</b>\n\n";
        $message .= "📋 <b>Request:</b> {$request['request_number']}\n";
        $message .= "🏢 <b>Contractor:</b> {$contractor['company_name']}\n";
        $message .= "📝 <b>Reason:</b> {$reason}";

        return $this->send($message);
    }

    public function notifyTransactionCompleted(array $request, array $contractor): bool
    {
        $message = "🎉 <b>Transaction Completed</b>\n\n";
        $message .= "📋 <b>Request:</b> {$request['request_number']}\n";
        $message .= "🏢 <b>Contractor:</b> {$contractor['company_name']}\n";
        $message .= "💰 <b>Amount:</b> \$" . number_format((float)$request['calculated_price'], 2) . "\n\n";
        $message .= "✅ Payment processed successfully.";

        return $this->send($message);
    }
}
