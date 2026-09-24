<?php

namespace App\Services;

use App\Models\MembershipSubscription;
use App\Models\Restaurant;
use App\Models\Setting;

class ExpiryNoticeService
{
    public function __construct(private NotificationService $notifications) {}

    public function dispatch(): void
    {
        $today = now()->toDateString();
        $cacheKey = 'expiry-notices-'.$today;

        if (cache()->has($cacheKey)) {
            return;
        }

        $this->notifyRestaurants();
        $this->notifyMemberships();

        cache()->put($cacheKey, true, now()->endOfDay());
    }

    private function notifyRestaurants(): void
    {
        $days = max(1, (int) Setting::value('restaurant_expiry_warning_days', 7));

        Restaurant::query()
            ->where('is_active', true)
            ->whereDate('expires_at', now()->addDays($days)->toDateString())
            ->get()
            ->each(function (Restaurant $restaurant) use ($days) {
                $this->notifications->notifyAdmins(
                    'قرب انتهاء عرض مطعم',
                    "باقي {$days} أيام على انتهاء عرض {$restaurant->name}.",
                    route('admin.restaurants.edit', $restaurant)
                );
            });
    }

    private function notifyMemberships(): void
    {
        $days = max(1, (int) Setting::value('membership_expiry_warning_days', 3));

        MembershipSubscription::query()
            ->with('user', 'membership')
            ->where('status', 'approved')
            ->whereDate('ends_at', now()->addDays($days)->toDateString())
            ->get()
            ->each(function (MembershipSubscription $subscription) use ($days) {
                if (! $subscription->user) {
                    return;
                }

                $this->notifications->notify(
                    $subscription->user,
                    'قرب انتهاء عضويتك',
                    "باقي {$days} أيام على انتهاء عضوية {$subscription->membership->name}. جدّد الآن حتى لا تفقد الخصم.",
                    route('memberships.index')
                );
            });
    }
}
