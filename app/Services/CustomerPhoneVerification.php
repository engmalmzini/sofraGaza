<?php

namespace App\Services;

use App\Exceptions\PhoneVerificationException;
use Illuminate\Support\Facades\RateLimiter;

class CustomerPhoneVerification
{
    public const SESSION_KEY = 'customer_register_phone';

    public const CODE_TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function step(): string
    {
        $state = $this->state();

        if ($state === null) {
            return 'phone';
        }

        if (! empty($state['verified'])) {
            return 'account';
        }

        if (! empty($state['phone'])) {
            return 'code';
        }

        return 'phone';
    }

    public function phone(): ?string
    {
        $phone = $this->state()['phone'] ?? null;

        return is_string($phone) && $phone !== '' ? $phone : null;
    }

    public function verifiedPhone(): ?string
    {
        $state = $this->state();

        if ($state === null || empty($state['verified']) || empty($state['phone'])) {
            return null;
        }

        return $state['phone'];
    }

    public function issue(string $phone, ?string $ip = null): void
    {
        $this->ensureCanSend($phone, $ip);

        $code = (string) random_int(100000, 999999);

        app(TweetSmsService::class)->send(
            $phone,
            "رمز التحقق في سفرة غزة: {$code}"
        );

        RateLimiter::hit($this->phoneKey($phone), 3600);
        RateLimiter::hit($this->cooldownKey($phone), 60);

        session([
            self::SESSION_KEY => [
                'phone' => $phone,
                'code_hash' => $this->hash($code),
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES)->getTimestamp(),
                'attempts' => 0,
                'verified' => false,
            ],
        ]);
    }

    public function confirm(string $code): void
    {
        $state = $this->state();

        if ($state === null || empty($state['phone'])) {
            throw new PhoneVerificationException('أدخل رقم الهاتف أولاً.');
        }

        if (! empty($state['verified'])) {
            return;
        }

        if (empty($state['code_hash']) || (int) ($state['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            throw new PhoneVerificationException('تجاوزت عدد المحاولات. اطلب رمزاً جديداً.');
        }

        if ((int) ($state['expires_at'] ?? 0) < now()->getTimestamp()) {
            throw new PhoneVerificationException('انتهت صلاحية الرمز. اطلب رمزاً جديداً.');
        }

        $digits = preg_replace('/\D+/', '', $code) ?? '';
        $state['attempts'] = (int) ($state['attempts'] ?? 0) + 1;
        $matches = hash_equals($state['code_hash'], $this->hash($digits));

        if (! $matches) {
            if ($state['attempts'] >= self::MAX_ATTEMPTS) {
                $state['code_hash'] = null;
            }

            session([self::SESSION_KEY => $state]);

            throw new PhoneVerificationException(
                $state['attempts'] >= self::MAX_ATTEMPTS
                    ? 'تجاوزت عدد المحاولات. اطلب رمزاً جديداً.'
                    : 'رمز التحقق غير صحيح.'
            );
        }

        session([
            self::SESSION_KEY => [
                'phone' => $state['phone'],
                'verified' => true,
            ],
        ]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function ensureCanSend(string $phone, ?string $ip): void
    {
        if ($ip) {
            $ipKey = 'customer-sms-ip:'.$ip;

            if (RateLimiter::tooManyAttempts($ipKey, 8)) {
                throw new PhoneVerificationException('محاولات كثيرة من هذا الجهاز. حاول لاحقاً.');
            }

            RateLimiter::hit($ipKey, 3600);
        }

        if (RateLimiter::tooManyAttempts($this->phoneKey($phone), 5)) {
            throw new PhoneVerificationException('تجاوزت عدد رسائل التحقق لهذا الرقم. حاول لاحقاً.');
        }

        if (RateLimiter::tooManyAttempts($this->cooldownKey($phone), 1)) {
            throw new PhoneVerificationException('انتظر دقيقة قبل طلب رمز جديد.');
        }
    }

    private function state(): ?array
    {
        $state = session(self::SESSION_KEY);

        return is_array($state) ? $state : null;
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function phoneKey(string $phone): string
    {
        return 'customer-sms:'.$phone;
    }

    private function cooldownKey(string $phone): string
    {
        return 'customer-sms-wait:'.$phone;
    }
}
