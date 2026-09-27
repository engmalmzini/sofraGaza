<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\ExpiryNoticeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('ar');

        View::composer('*', function ($view) {
            $cart = app(CartService::class);
            $cartCount = $cart->count();
            $view->with('cartCount', $cartCount);
            $view->with('unreadNotifications', auth()->user()?->unreadNotificationsCount() ?? 0);

            try {
                $areas = \App\Models\Setting::areasWithFees();
            } catch (\Throwable) {
                $areas = config('brand.areas', []);
            }

            $defaultArea = $areas[0] ?? ['key' => 'الرمال', 'label' => 'غزة • حي الرمال', 'delivery_fee' => 10.0];
            $currentArea = session('delivery_area', $defaultArea);
            if (isset($currentArea['key'])) {
                try {
                    $currentArea['delivery_fee'] = \App\Models\Setting::deliveryFeeForArea($currentArea['key']);
                } catch (\Throwable) {
                    $currentArea['delivery_fee'] = 10.0;
                }
            }

            $view->with('deliveryAreas', $areas);
            $view->with('deliveryArea', $currentArea);
            $view->with('cartPreview', $cartCount ? $cart->quote(auth()->user(), $currentArea['key'] ?? null) : null);
        });

        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            app(ExpiryNoticeService::class)->dispatch();
        } catch (\Throwable) {
            // Database may not be ready during first install.
        }
    }
}
