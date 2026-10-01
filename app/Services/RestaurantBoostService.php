<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantBoost;
use App\Models\User;
use App\Support\Finance;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class RestaurantBoostService
{
    public function __construct(private NotificationService $notifications) {}

    public function request(Restaurant $restaurant, int $days, UploadedFile $receipt): RestaurantBoost
    {
        if (! $restaurant->isApproved() || $restaurant->panel_suspended) {
            throw new RuntimeException('يجب أن يكون '.$restaurant->venueNounYours().' موثّقاً وظاهراً قبل شراء الإعلان.');
        }

        if ($restaurant->pendingBoost()) {
            throw new RuntimeException('لديك طلب إعلان قيد المراجعة. انتظر تأكيد الإدارة.');
        }

        $days = max(1, min(30, $days));
        $rate = Finance::BOOST_DAILY_RATE;
        $start = $this->nextStartDate($restaurant);
        $end = $start->copy()->addDays($days - 1);
        $path = $receipt->store('receipts', 'public');

        $boost = RestaurantBoost::query()->create([
            'restaurant_id' => $restaurant->id,
            'starts_on' => $start->toDateString(),
            'ends_on' => $end->toDateString(),
            'daily_rate' => $rate,
            'days' => $days,
            'amount' => round($days * $rate, 2),
            'transfer_receipt_path' => $path,
            'title' => 'إعلان لمدة '.$days.' يوم',
            'status' => RestaurantBoost::STATUS_PENDING,
        ]);

        $this->notifications->notifyAdmins(
            'طلب إعلان مطعم',
            "{$restaurant->name} طلب إعلاناً لمدة {$days} يوم بقيمة {$boost->amount} ₪.",
            route('admin.boosts.show', $boost)
        );

        return $boost;
    }

    public function approve(RestaurantBoost $boost, User $admin): string
    {
        if ($boost->isApproved()) {
            return 'هذا الإعلان مفعّل مسبقاً.';
        }

        if (! $boost->isPending()) {
            throw new RuntimeException('لا يمكن تأكيد هذا الطلب لأن حالته حالياً: '.$boost->statusLabel().'.');
        }

        $restaurant = $boost->restaurant;
        $days = max(1, (int) $boost->days);
        $start = $this->nextStartDate($restaurant);
        $end = $start->copy()->addDays($days - 1);

        $boost->update([
            'status' => RestaurantBoost::STATUS_APPROVED,
            'starts_on' => $start->toDateString(),
            'ends_on' => $end->toDateString(),
            'amount' => round($days * (float) $boost->daily_rate, 2),
            'rejection_reason' => null,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        if ($restaurant->owner) {
            $this->notifications->notify(
                $restaurant->owner,
                'تم تفعيل إعلانك',
                $restaurant->venueNounYours().' سيظهر في المقدمة للزبائن من '.$start->format('Y/m/d').' حتى '.$end->format('Y/m/d').'.',
                route('partner.boosts.index')
            );
        }

        return 'تم تأكيد الحوالة وتفعيل إعلان '.$restaurant->name.' لمدة '.$days.' يوم.';
    }

    public function reject(RestaurantBoost $boost, string $reason): string
    {
        if ($boost->isRejected()) {
            return 'تم رفض هذا الطلب مسبقاً.';
        }

        if (! $boost->isPending()) {
            throw new RuntimeException('لا يمكن رفض هذا الطلب لأن حالته حالياً: '.$boost->statusLabel().'.');
        }

        $boost->update([
            'status' => RestaurantBoost::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        if ($boost->restaurant?->owner) {
            $this->notifications->notify(
                $boost->restaurant->owner,
                'لم يتم قبول حوالة الإعلان',
                $reason.' يمكنك إرسال طلب جديد من لوحة المطعم مع إشعار حوالة واضح.',
                route('partner.boosts.index')
            );
        }

        return 'تم رفض طلب الإعلان وإبلاغ صاحب المطعم.';
    }

    private function nextStartDate(Restaurant $restaurant): \Carbon\Carbon
    {
        $today = now()->startOfDay();
        $active = $restaurant->activeBoost();

        if ($active && $active->ends_on->gte($today)) {
            return $active->ends_on->copy()->addDay()->startOfDay();
        }

        return $today;
    }
}
