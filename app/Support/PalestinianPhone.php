<?php

namespace App\Support;

class PalestinianPhone
{
    public const PATTERN = '/^(059|056)[0-9]{7,}$/';

    public const HTML_PATTERN = '^(059|056)[0-9]{7,}$';

    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
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
}
