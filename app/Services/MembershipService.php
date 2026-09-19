<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipSubscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MembershipService
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function request(User $user, Membership $membership, UploadedFile $receipt): MembershipSubscription
    {
        if (! $membership->is_active) {
            throw new RuntimeException('هذه العضوية غير متاحة حالياً.');
        }

        $pending = $user->subscriptions()->where('status', 'pending')->exists();
        if ($pending) {
            throw new RuntimeException('لديك طلب عضوية قيد المراجعة بالفعل.');
        }

        $path = $receipt->store('receipts', 'public');

        $subscription = MembershipSubscription::create([
            'user_id' => $user->id,
            'membership_id' => $membership->id,
            'amount' => $membership->monthly_price,
            'status' => 'pending',
            'transfer_receipt_path' => $path,
        ]);

        $this->notifications->notifyAdmins(
            'طلب عضوية جديد',
            "{$user->name} طلب عضوية {$membership->name} بقيمة {$membership->monthly_price} ₪.",
            route('admin.subscriptions.show', $subscription)
        );

        return $subscription;
    }

    public function approve(MembershipSubscription $subscription): void
    {
        if ($subscription->status !== 'pending') {
            throw new RuntimeException('لا يمكن اعتماد هذا الطلب.');
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'approved',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);
        });

        $this->notifications->notify(
            $subscription->user,
            'تم تفعيل عضويتك',
            "تم تفعيل عضوية {$subscription->membership->name} حتى ".$subscription->fresh()->ends_at->translatedFormat('d F Y').'.',
            route('memberships.index')
        );
    }

    public function reject(MembershipSubscription $subscription, string $reason): void
    {
        if ($subscription->status !== 'pending') {
            throw new RuntimeException('لا يمكن رفض هذا الطلب.');
        }

        $subscription->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->notifications->notify(
            $subscription->user,
            'تم رفض طلب العضوية',
            $reason,
            route('memberships.index')
        );
    }
}
