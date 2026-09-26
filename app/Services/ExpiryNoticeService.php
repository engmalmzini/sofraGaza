<?php

namespace App\Services;

use App\Models\MembershipSubscription;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
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
        $this->notifyRestaurantListings();
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

    private function notifyRestaurantListings(): void
    {
        $days = max(1, (int) Setting::value('restaurant_expiry_warning_days', 7));

        RestaurantSubscription::query()
            ->with(['restaurant.owner', 'plan'])
            ->where('status', 'approved')
            ->whereDate('ends_at', now()->addDays($days)->toDateString())
            ->get()
            ->each(function (RestaurantSubscription $subscription) use ($days) {
                $restaurant = $subscription->restaurant;
                if (! $restaurant?->owner || $restaurant->panel_suspended) {
                    return;
                }

                $this->notifications->notify(
                    $restaurant->owner,
                    'قرب انتهاء اشتراكك',
                    "باقي {$days} أيام على انتهاء باقة {$subscription->plan->name}. جدّد حتى لا تُوقف اللوحة ويختفي {$restaurant->venueNounYours()} من الموقع.",
                    route('partner.subscription.index')
                );

                $this->notifications->notifyAdmins(
                    'قرب انتهاء اشتراك مطعم',
                    "باقي {$days} أيام على اشتراك {$restaurant->name}.",
                    route('admin.restaurants.show', $restaurant)
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
