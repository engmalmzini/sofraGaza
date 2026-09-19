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
}
