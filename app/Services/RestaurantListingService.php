<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantPlan;
use App\Models\RestaurantSubscription;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RestaurantListingService
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function request(Restaurant $restaurant, RestaurantPlan $plan, UploadedFile $receipt): RestaurantSubscription
    {
        if (! $plan->is_active) {
            throw new RuntimeException('هذه الباقة غير متاحة حالياً.');
        }

        if ($restaurant->pendingListing()) {
            throw new RuntimeException('لديك حوالة قيد المراجعة بالفعل. انتظر تأكيد الإدارة.');
        }

        $path = $receipt->store('receipts', 'public');

        $subscription = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 'pending',
            'transfer_receipt_path' => $path,
        ]);

        $this->notifications->notifyAdmins(
            'حوالة اشتراك مطعم',
            "{$restaurant->name} رفع إشعار حوالة لباقة {$plan->name} بقيمة {$plan->price} ₪.",
            route('admin.listings.show', $subscription)
        );

        return $subscription;
    }

    public function approve(RestaurantSubscription $subscription): string
    {
        if ($subscription->status === 'approved') {
            return 'هذا الاشتراك مفعّل مسبقاً.';
        }

        if ($subscription->status !== 'pending') {
            throw new RuntimeException('لا يمكن تأكيد هذه الحوالة لأن حالتها حالياً: '.$subscription->statusLabel().'.');
        }

        $plan = $subscription->plan;
        abort_unless($plan, 404);

        DB::transaction(function () use ($subscription, $plan) {
            $starts = now();
            $ends = now()->addDays((int) $plan->duration_days);

            $subscription->update([
                'status' => 'approved',
                'starts_at' => $starts,
                'ends_at' => $ends,
                'rejection_reason' => null,
            ]);

            $subscription->restaurant->update([
                'verification_status' => Restaurant::VERIFICATION_APPROVED,
                'rejection_reason' => null,
                'is_active' => true,
                'panel_suspended' => false,
                'starts_at' => $starts->toDateString(),
                'expires_at' => $ends->toDateString(),
                'verified_at' => now(),
            ]);
        });

        $subscription->refresh()->load('restaurant.owner', 'plan');

        if ($subscription->restaurant->owner) {
            $this->notifications->notify(
                $subscription->restaurant->owner,
                'تم تفعيل اشتراكك',
                "تم تأكيد حوالة باقة {$plan->name}. تقدر تضيف المنيو، و{$subscription->restaurant->venueNounYours()} ظاهر على الموقع حتى ".$subscription->ends_at->translatedFormat('d F Y').'.',
                route('partner.dashboard')
            );
        }

        return 'تم تأكيد الدفع وتفعيل اللوحة لمدة '.$plan->durationLabel().'.';
    }

    public function reject(RestaurantSubscription $subscription, string $reason): string
    {
        if ($subscription->status === 'rejected') {
            return 'تم رفض هذه الحوالة مسبقاً.';
        }

        if ($subscription->status !== 'pending') {
            throw new RuntimeException('لا يمكن رفض هذه الحوالة لأن حالتها حالياً: '.$subscription->statusLabel().'.');
        }

        $subscription->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        if ($subscription->restaurant?->owner) {
            $this->notifications->notify(
                $subscription->restaurant->owner,
                'لم يتم قبول حوالة الاشتراك',
                $reason.' يمكنك رفع إشعار جديد من صفحة الاشتراك.',
                route('partner.subscription.index')
            );
        }

        return 'تم رفض الحوالة وإبلاغ صاحب المطعم.';
    }

    public function suspend(Restaurant $restaurant): string
    {
        $restaurant->update([
            'panel_suspended' => true,
            'is_active' => false,
        ]);

        if ($restaurant->owner) {
            $this->notifications->notify(
                $restaurant->owner,
                'تم إيقاف لوحة المطعم',
                'قم بتجديد الاشتراك. بياناتك محفوظة ولن يظهر '.$restaurant->venueNounYours().' على الموقع حتى إعادة التفعيل.',
                route('partner.subscription.index')
            );
        }

        return 'تم إيقاف لوحة '.$restaurant->name.' وإخفاؤه من الموقع. البيانات محفوظة.';
    }

    public function unsuspend(Restaurant $restaurant): string
    {
        $listing = $restaurant->activeListing();

        if (! $listing) {
            throw new RuntimeException('لا يوجد اشتراك ساري. أكّد حوالة الباقة أولاً ثم أعد تفعيل اللوحة.');
        }

        $restaurant->update([
            'panel_suspended' => false,
            'is_active' => true,
            'starts_at' => $listing->starts_at?->toDateString() ?? now()->toDateString(),
            'expires_at' => $listing->ends_at?->toDateString(),
        ]);

        if ($restaurant->owner) {
            $this->notifications->notify(
                $restaurant->owner,
                'تم إعادة فتح اللوحة',
                'اشتراكك ساري ويمكنك إدارة المنيو من جديد.',
                route('partner.dashboard')
            );
        }

        return 'تم إعادة فتح لوحة '.$restaurant->name.'.';
    }
}
