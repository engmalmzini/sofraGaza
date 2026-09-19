<?php

namespace App\Providers;

use App\Models\MembershipSubscription;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\NotificationService;
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
            $this->notifyExpiringEntities();
        } catch (\Throwable) {
            // Database may not be ready during first install.
        }
    }

    private function notifyExpiringEntities(): void
    {
        $today = now()->toDateString();
        $cacheKey = 'expiry-notices-'.$today;

        if (cache()->has($cacheKey)) {
            return;
        }

        $restaurantDays = (int) Setting::value('restaurant_expiry_warning_days', 7);
        $membershipDays = (int) Setting::value('membership_expiry_warning_days', 3);
        $notifications = app(NotificationService::class);

        Restaurant::query()
            ->where('is_active', true)
            ->whereDate('expires_at', now()->addDays($restaurantDays)->toDateString())
            ->get()
            ->each(function (Restaurant $restaurant) use ($notifications, $restaurantDays) {
                $notifications->notifyAdmins(
                    'قرب انتهاء عرض مطعم',
                    "باقي {$restaurantDays} أيام على انتهاء عرض {$restaurant->name}.",
                    route('admin.restaurants.edit', $restaurant)
                );
            });

        MembershipSubscription::query()
            ->with('user', 'membership')
            ->where('status', 'approved')
            ->whereDate('ends_at', now()->addDays($membershipDays)->toDateString())
            ->get()
            ->each(function (MembershipSubscription $subscription) use ($notifications, $membershipDays) {
                $notifications->notify(
                    $subscription->user,
                    'قرب انتهاء عضويتك',
                    "باقي {$membershipDays} أيام على انتهاء عضوية {$subscription->membership->name}. جدّد الآن حتى لا تفقد الخصم.",
                    route('memberships.index')
                );
            });

        cache()->put($cacheKey, true, now()->endOfDay());
    }
}
