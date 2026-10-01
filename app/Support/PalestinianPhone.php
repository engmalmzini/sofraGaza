<?php

namespace App\Support;

class PalestinianPhone
{
    public const PATTERN = '/^(059|056)[0-9]{7,}$/';

    public const HTML_PATTERN = '^(059|056)[0-9]{7,}$';

    public const CUSTOMER_PATTERN = '/^05[0-9]{8}$/';

    public const CUSTOMER_HTML_PATTERN = '^05[0-9]{8}$';

    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?: '';
    }

    public static function local(?string $value): string
    {
        $digits = self::digits($value);

        if (str_starts_with($digits, '00972')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '972') && strlen($digits) >= 12) {
            $digits = '0'.substr($digits, 3);
        }

        return $digits;
    }

    public static function international(?string $value): ?string
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '972'.substr($digits, 1);
        }

        return $digits;
    }

    public static function whatsappUrl(?string $phone, ?string $text = null): ?string
    {
        $intl = self::international($phone);

        if (! $intl) {
            return null;
        }

        $url = 'https://wa.me/'.$intl;

        if ($text) {
            $url .= '?text='.rawurlencode($text);
        }

        return $url;
    }

    public static function rules(bool $uniqueUser = false): array
    {
        $rules = ['required', 'string', 'min:10', 'max:15', 'regex:'.self::PATTERN];

        if ($uniqueUser) {
            $rules[] = 'unique:users,phone';
        }

        return $rules;
    }

    public static function messages(string $field = 'phone'): array
    {
        return [
            "{$field}.required" => 'رقم الهاتف مطلوب.',
            "{$field}.min" => 'رقم الهاتف يجب ألا يقل عن 10 أرقام.',
            "{$field}.regex" => 'رقم الهاتف يجب أن يبدأ بـ 059 أو 056 ويتكون من 10 أرقام على الأقل.',
            "{$field}.unique" => 'رقم الهاتف مسجّل مسبقاً.',
        ];
    }

    public static function customerRules(bool $uniqueUser = false): array
    {
        $rules = ['required', 'string', 'size:10', 'regex:'.self::CUSTOMER_PATTERN];

        if ($uniqueUser) {
            $rules[] = 'unique:users,phone';
        }

        return $rules;
    }

    public static function customerMessages(string $field = 'phone'): array
    {
        return [
            "{$field}.required" => 'رقم الهاتف مطلوب.',
            "{$field}.size" => 'رقم الهاتف يجب أن يتكون من 10 أرقام.',
            "{$field}.regex" => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            "{$field}.unique" => 'رقم الهاتف مسجّل مسبقاً.',
        ];
    }

    public static function mask(string $phone): string
    {
        $digits = self::digits($phone);

        if (strlen($digits) < 7) {
            return $digits;
        }

        return substr($digits, 0, 3).str_repeat('*', strlen($digits) - 6).substr($digits, -3);
    }
}
