<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
    ];

    public static function value(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('app_settings', 60, function () {
            return static::query()->pluck('value', 'key');
        });

        return $settings[$key] ?? $default;
    }

    public static function forgetCache(): void
    {
        Cache::forget('app_settings');
    }

    public static function deliveryFeesByArea(): array
    {
        $raw = static::value('delivery_fees_by_area');
        if (! $raw) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function customAreas(): array
    {
        $raw = static::value('custom_delivery_areas');
        if (! $raw) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function allAreas(): array
    {
        $brandAreas = config('brand.areas', []);
        $custom = static::customAreas();
        $all = array_merge($brandAreas, $custom);

        $keyed = [];
        foreach ($all as $item) {
            if (! empty($item['key'])) {
                $keyed[$item['key']] = $item;
            }
        }

        return array_values($keyed);
    }

    public static function deliveryFeeForArea(?string $areaKey = null): float
    {
        $default = (float) static::value('delivery_fee', 10);
        if (! $areaKey) {
            $areaKey = session('delivery_area')['key'] ?? (config('brand.areas.0.key') ?? 'الرمال');
        }

        if (! $areaKey) {
            return $default;
        }

        $fees = static::deliveryFeesByArea();

        if (isset($fees[$areaKey]) && is_numeric($fees[$areaKey])) {
            return (float) $fees[$areaKey];
        }

        return $default;
    }

    public static function areasWithFees(): array
    {
        $areas = static::allAreas();
        $fees = static::deliveryFeesByArea();
        $default = (float) static::value('delivery_fee', 10);

        return array_map(function ($area) use ($fees, $default) {
            $hasCustom = isset($fees[$area['key']]) && is_numeric($fees[$area['key']]);
            $fee = $hasCustom ? (float) $fees[$area['key']] : $default;

            return array_merge($area, [
                'delivery_fee' => $fee,
                'has_custom_fee' => $hasCustom,
                'is_custom_area' => ! empty($area['is_custom']),
            ]);
        }, $areas);
    }

    public static function paymentAccounts(): array
    {
        $qrPath = static::value('jawwal_pay_qr_path');
        $qrUrl = $qrPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($qrPath) : null;

        $palpayQrPath = static::value('palpay_qr_path');
        $palpayQrUrl = $palpayQrPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($palpayQrPath) : null;

        return [
            'bank_name' => (string) static::value('bank_name', 'بنك فلسطين'),
            'bank_account_number' => (string) static::value('bank_account_number', '2345678'),
            'bank_iban' => (string) static::value('bank_iban', 'PS04PALS000000000002345678'),
            'bank_beneficiary_name' => (string) static::value('bank_beneficiary_name', 'سفرة غزة — Sofra Gaza'),
            'jawwal_pay_number' => (string) static::value('jawwal_pay_number', '0599000000'),
            'jawwal_pay_name' => (string) static::value('jawwal_pay_name', 'محفظة سفرة غزة'),
            'jawwal_pay_qr_path' => $qrPath,
            'jawwal_pay_qr_url' => $qrUrl,
            'palpay_number' => (string) static::value('palpay_number', '0599000000'),
            'palpay_name' => (string) static::value('palpay_name', 'محفظة بال باي — سفرة غزة'),
            'palpay_qr_path' => $palpayQrPath,
            'palpay_qr_url' => $palpayQrUrl,
        ];
    }

    public static function rawHomeBanners(): array
    {
        $raw = static::value('home_banners');
        if (! $raw) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? array_values(array_filter($decoded)) : [];
    }

    public static function homeBanners(): array
    {
        $items = static::rawHomeBanners();

        if (empty($items)) {
            return [
                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&h=300&q=80',
                'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&h=300&q=80',
                'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&h=300&q=80',
            ];
        }

        return array_map(function ($item) {
            $path = is_array($item) ? ($item['image'] ?? $item['url'] ?? '') : (string) $item;

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }, $items);
    }
}
