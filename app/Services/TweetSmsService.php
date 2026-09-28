<?php

namespace App\Services;

use App\Exceptions\SmsDeliveryException;
use App\Support\PalestinianPhone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TweetSmsService
{
    public function send(string $phone, string $message): void
    {
        $key = (string) config('services.tweetsms.key');
        $sender = (string) config('services.tweetsms.sender');
        $endpoint = (string) config('services.tweetsms.endpoint');

        if ($key === '' || $sender === '' || $endpoint === '') {
            throw new SmsDeliveryException('خدمة الرسائل غير مهيأة.');
        }

        $to = PalestinianPhone::digits($phone);

        $response = Http::timeout(20)
            ->acceptJson()
            ->get($endpoint, [
                'comm' => 'sendsms',
                'api_key' => $key,
                'to' => $to,
                'message' => $message,
                'sender' => $sender,
            ]);

        $body = trim($response->body());

        if (! $response->successful() || ! $this->accepted($body)) {
            Log::warning('TweetSMS send failed', [
                'status' => $response->status(),
                'code' => mb_substr($body, 0, 40),
                'to' => $to,
            ]);

            throw new SmsDeliveryException('تعذر إرسال رسالة التحقق.');
        }
    }

    private function accepted(string $body): bool
    {
        if ($body === '' || str_contains(strtolower($body), '<html')) {
            return false;
        }

        $plain = trim(html_entity_decode(strip_tags($body)));

        if (str_starts_with($plain, '{')) {
            $json = json_decode($plain, true);

            if (! is_array($json)) {
                return false;
            }

            $code = $json['code'] ?? $json['status'] ?? $json['result'] ?? null;

            if (is_numeric($code)) {
                return (int) $code >= 0;
            }

            return in_array(strtolower((string) ($json['status'] ?? $json['message'] ?? '')), ['ok', 'success', 'sent'], true);
        }

        // TweetSMS success is "1:{id}:{phone}:{ref}<br />". Errors are negative, like -115.
        if (preg_match('/^(-?\d+)/', $plain, $matches) === 1) {
            return (int) $matches[1] >= 0;
        }

        return false;
    }
}
