<?php

namespace App\Support;

use App\Models\Setting;

class Finance
{
    public const RESTAURANT_COMMISSION_RATE = 0.10;

    public const COURIER_ORDER_SHARE_RATE = 0.15;

    public const BOOST_DAILY_RATE = 20.0;

    public const PREMIUM_GIFT_MIN_PRICE = 100.0;

    public static function restaurantCommissionPercent(): float
    {
        $percent = (float) Setting::value('restaurant_commission_percent', 10);

        return max(0.0, min(100.0, $percent));
    }

    public static function restaurantCommissionRate(): float
    {
        return round(self::restaurantCommissionPercent() / 100, 4);
    }

    public static function restaurantCommissionLabel(): string
    {
        $percent = self::restaurantCommissionPercent();
        $formatted = fmod($percent, 1.0) === 0.0
            ? (string) (int) $percent
            : rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');

        return $formatted.'%';
    }
}
