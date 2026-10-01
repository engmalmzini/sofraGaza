<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\ExpiryNoticeService;
use App\Services\FavoriteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FavoriteService::class);
        $this->app->singleton(\App\Services\ReferralService::class);
    }

    public function boot(): void
    {
        Carbon::setLocale('ar');

        View::composer('*', function ($view) {
            $user = auth()->user();
            $canShop = ! $user || $user->canShopAsCustomer();
            $cart = app(CartService::class);
            $cartCount = $canShop ? $cart->count() : 0;
            $view->with('canShop', $canShop);
            $view->with('accountHomeUrl', $user?->publicAccountUrl() ?? route('login'));
            $view->with('cartCount', $cartCount);
            $view->with('unreadNotifications', $user?->unreadNotificationsCount() ?? 0);

            $favoriteRestaurantIds = [];
            $favoriteMenuItemIds = [];
            if ($user && $user->canShopAsCustomer()) {
                try {
                    $favoriteIds = app(FavoriteService::class)->idsFor($user);
                    $favoriteRestaurantIds = $favoriteIds['restaurants'];
                    $favoriteMenuItemIds = $favoriteIds['menu_items'];
                } catch (\Throwable) {
                    // Table may not exist before migrate.
                }
            }
            $view->with('favoriteRestaurantIds', $favoriteRestaurantIds);
            $view->with('favoriteMenuItemIds', $favoriteMenuItemIds);

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
            $view->with('cartPreview', ($canShop && $cartCount) ? $cart->quote($user, $currentArea['key'] ?? null) : null);

            $activeGroupOrder = null;
            if ($canShop && $user) {
                try {
                    $activeGroupOrder = app(\App\Services\GroupOrderService::class)->activeFor($user);
                } catch (\Throwable) {
                    $activeGroupOrder = null;
                }
            }
            $view->with('activeGroupOrder', $activeGroupOrder);
            $view->with('groupCheckoutUrl', $activeGroupOrder?->actionUrlFor($user));
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
