<?php

namespace App\Services;

use App\Models\MembershipSubscription;
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

        $this->notifyMemberships();

        cache()->put($cacheKey, true, now()->endOfDay());
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
