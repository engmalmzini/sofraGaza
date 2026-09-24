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
            $view->with('cartPreview', $cartCount ? $cart->quote(auth()->user()) : null);
            $view->with('unreadNotifications', auth()->user()?->unreadNotificationsCount() ?? 0);
            $areas = config('brand.areas', []);
            $view->with('deliveryAreas', $areas);
            $view->with('deliveryArea', session('delivery_area', $areas[0] ?? ['key' => 'الرمال', 'label' => 'غزة • حي الرمال']));
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
