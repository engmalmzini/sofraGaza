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

    public function approve(MembershipSubscription $subscription): string
    {
        if ($subscription->status === 'approved') {
            return 'العضوية مفعّلة مسبقاً.';
        }

        if ($subscription->status !== 'pending') {
            throw new RuntimeException('لا يمكن تفعيل هذا الطلب لأن حالته حالياً: '.$subscription->statusLabel().'.');
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'approved',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);
        });

        $subscription->refresh();

        $this->notifications->notify(
            $subscription->user,
            'تم تفعيل عضويتك',
            "تم تفعيل عضوية {$subscription->membership->name} حتى ".$subscription->ends_at->translatedFormat('d F Y').'. بطاقتك الرقمية ظاهرة الآن في ملفك الشخصي.',
            route('account.show')
        );

        return 'تم تفعيل العضوية لمدة 30 يوماً. البطاقة الرقمية ظهرت في حساب الزبون — أرسل نسخة واتساب يدوياً كتأكيد إضافي.';
    }

    public function reject(MembershipSubscription $subscription, string $reason): string
    {
        if ($subscription->status === 'rejected') {
            return 'تم رفض هذا الطلب مسبقاً.';
        }

        if ($subscription->status !== 'pending') {
            throw new RuntimeException('لا يمكن رفض هذا الطلب لأن حالته حالياً: '.$subscription->statusLabel().'.');
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

        return 'تم رفض طلب العضوية.';
    }

    public function requestCard(User $user, ?string $note = null): string
    {
        $subscription = $user->activeSubscription();

        if (! $subscription) {
            throw new RuntimeException('فعّل عضويتك أولاً قبل طلب بطاقة الموقع.');
        }

        if ($subscription->card_status === 'pending') {
            return 'طلب البطاقة قيد التجهيز مسبقاً.';
        }

        if ($subscription->card_status === 'ready') {
            return 'بطاقتك جاهزة للاستلام.';
        }

        if (! $subscription->canRequestCard()) {
            throw new RuntimeException('لا يمكن طلب البطاقة حالياً.');
        }

        $subscription->update([
            'card_status' => 'pending',
            'card_requested_at' => now(),
            'card_fulfilled_at' => null,
            'card_note' => $note,
        ]);

        $this->notifications->notifyAdmins(
            'طلب بطاقة موقع',
            "{$user->name} طلب بطاقة عضوية {$subscription->membership->name} ({$subscription->cardNumber()}).",
            route('admin.subscriptions.show', $subscription)
        );

        $this->notifications->notify(
            $user,
            'تم استلام طلب البطاقة',
            'سنجهّز بطاقة الموقع الخاصة بعضويتك. يصلك تنبيه عند جاهزيتها للاستلام.',
            route('memberships.index')
        );

        return 'تم إرسال طلب بطاقة المطعم. أبرزها داخل المطاعم المشتركة لتطبيق الخصم.';
    }

    public function markCard(MembershipSubscription $subscription, string $status): string
    {
        if ($subscription->status !== 'approved') {
            throw new RuntimeException('فعّل العضوية أولاً قبل تجهيز البطاقة.');
        }

        if (! isset(MembershipSubscription::CARD_STATUSES[$status])) {
            throw new RuntimeException('حالة البطاقة غير صحيحة.');
        }

        if (! $subscription->card_status) {
            throw new RuntimeException('الزبون لم يطلب بطاقة بعد.');
        }

        $subscription->update([
            'card_status' => $status,
            'card_fulfilled_at' => $status === 'pending' ? null : now(),
        ]);

        $label = MembershipSubscription::CARD_STATUSES[$status];

        if ($status === 'ready') {
            $this->notifications->notify(
                $subscription->user,
                'بطاقتك جاهزة',
                'بطاقة الموقع جاهزة للاستلام. راجع صفحة العضويات للتفاصيل.',
                route('memberships.index')
            );
        }

        if ($status === 'delivered') {
            $this->notifications->notify(
                $subscription->user,
                'تم تسليم بطاقتك',
                'تم تسليم بطاقة الموقع. بالتوفيق مع خصم العضوية في طلباتك.',
                route('memberships.index')
            );
        }

        return 'تم تحديث حالة البطاقة: '.$label;
    }
}
