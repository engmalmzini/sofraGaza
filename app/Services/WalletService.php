<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function requestTopup(User $user, float $amount, UploadedFile $receipt, string $method = 'jawwal_pay', ?string $notes = null): WalletTopup
    {
        if ($amount <= 0) {
            throw new RuntimeException('المبلغ يجب أن يكون أكبر من صفر.');
        }

        $path = $receipt->store('receipts', 'public');

        $topup = WalletTopup::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => $method,
            'transfer_receipt_path' => $path,
            'status' => WalletTopup::STATUS_PENDING,
            'notes' => $notes,
        ]);

        $this->notifications->notifyAdmins(
            'طلب شحن رصيد جديد',
            "قام {$user->name} بطلب شحن رصيد بقيمة {$amount} ₪ عبر {$topup->paymentMethodLabel()}.",
            route('admin.wallet-topups.index')
        );

        return $topup;
    }

    public function approveTopup(WalletTopup $topup, User $admin): void
    {
        if (! $topup->isPending()) {
            throw new RuntimeException('هذا الطلب تمت مراجعته مسبقاً.');
        }

        DB::transaction(function () use ($topup, $admin) {
            $user = User::query()->whereKey($topup->user_id)->lockForUpdate()->first();
            $newBalance = round((float) $user->wallet_balance + (float) $topup->amount, 2);

            $user->update(['wallet_balance' => $newBalance]);

            $topup->update([
                'status' => WalletTopup::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_TOPUP,
                'amount' => $topup->amount,
                'balance_after' => $newBalance,
                'reference_id' => $topup->id,
                'reference_type' => WalletTopup::class,
                'description' => "شحن رصيد معتمد بقيمة {$topup->amount} ₪",
            ]);
        });

        $this->notifications->notify(
            $topup->user,
            'تمت إضافة الرصيد إلى محفظتك',
            "تم تأكيد تحويلك وإضافة {$topup->amount} ₪ إلى محفظتك بنجاح. يمكنك الآن الدفع منها مباشرة.",
            route('account.wallet')
        );
    }

    public function rejectTopup(WalletTopup $topup, User $admin, string $reason): void
    {
        if (! $topup->isPending()) {
            throw new RuntimeException('هذا الطلب تمت مراجعته مسبقاً.');
        }

        $topup->update([
            'status' => WalletTopup::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->notifications->notify(
            $topup->user,
            'تعذر تأكيد شحن الرصيد',
            "تم رفض طلب شحن الرصيد بقيمة {$topup->amount} ₪. السبب: {$reason}",
            route('account.wallet')
        );
    }

    public function charge(User $user, float $amount, string $description, Model $reference, string $type = WalletTransaction::TYPE_ORDER_PAYMENT): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $description, $reference, $type) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ((float) $lockedUser->wallet_balance < $amount) {
                throw new RuntimeException('رصيد المحفظة غير كافٍ لإتمام عملية الدفع.');
            }

            $newBalance = round((float) $lockedUser->wallet_balance - $amount, 2);
            $lockedUser->update(['wallet_balance' => $newBalance]);

            WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'reference_id' => $reference->getKey(),
                'reference_type' => $reference::class,
                'description' => $description,
            ]);
        });
    }

    public function credit(User $user, float $amount, string $description, Model $reference, string $type = WalletTransaction::TYPE_REFUND): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $description, $reference, $type) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();
            $newBalance = round((float) $lockedUser->wallet_balance + $amount, 2);
            $lockedUser->update(['wallet_balance' => $newBalance]);

            WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_id' => $reference->getKey(),
                'reference_type' => $reference::class,
                'description' => $description,
            ]);
        });
    }

    public function payForOrder(User $user, Order $order): void
    {
        $this->charge(
            $user,
            (float) $order->total,
            "خصم قيمة الطلب #{$order->id}",
            $order,
        );
    }

    public function refundGroupOrder(Order $order, string $reason = 'إلغاء الطلب'): void
    {
        $group = $order->groupOrder()->with('members.user')->first();
        if (! $group) {
            return;
        }

        foreach ($group->members as $member) {
            if (! $member->isPaid() || $member->payment_method !== 'wallet' || ! $member->user) {
                continue;
            }

            $amount = (float) $member->total;
            if ($member->is_host) {
                $amount = round($amount + (float) $order->delivery_fee, 2);
            }

            if ($amount <= 0) {
                continue;
            }

            $this->credit(
                $member->user,
                $amount,
                "استرداد نصيب طلب جماعي #{$order->id} ({$reason})",
                $member,
            );

            $this->notifications->notify(
                $member->user,
                'تم استرداد نصيبك إلى المحفظة',
                "تمت إعادة {$amount} ₪ إلى محفظتك من الطلب الجماعي #{$order->id}.",
                route('account.wallet')
            );
        }
    }

    public function refundOrder(Order $order, string $reason = 'إلغاء الطلب'): void
    {
        if ($order->group_order_id) {
            $this->refundGroupOrder($order, $reason);

            return;
        }

        if ($order->payment_method !== 'wallet' || (float) $order->total <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $reason) {
            $user = User::query()->whereKey($order->user_id)->lockForUpdate()->first();
            $refundAmount = (float) $order->total;
            $newBalance = round((float) $user->wallet_balance + $refundAmount, 2);

            $user->update(['wallet_balance' => $newBalance]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_REFUND,
                'amount' => $refundAmount,
                'balance_after' => $newBalance,
                'reference_id' => $order->id,
                'reference_type' => Order::class,
                'description' => "استرداد إلى المحفظة لطلب #{$order->id} ({$reason})",
            ]);
        });

        $this->notifications->notify(
            $order->user,
            'تم استرداد المبلغ إلى محفظتك',
            "تمت إعادة {$order->total} ₪ إلى رصيد محفظتك بسبب: {$reason}",
            route('account.wallet')
        );
    }

    public function adjustBalance(User $user, float $amount, string $reason, ?User $admin = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $reason, $admin) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();
            $newBalance = round((float) $lockedUser->wallet_balance + $amount, 2);

            if ($newBalance < 0) {
                throw new RuntimeException('لا يمكن أن يصبح الرصيد سالباً.');
            }

            $lockedUser->update(['wallet_balance' => $newBalance]);

            $tx = WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => WalletTransaction::TYPE_ADMIN_ADJUSTMENT,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_id' => $admin?->id,
                'reference_type' => $admin ? User::class : null,
                'description' => $reason,
            ]);

            $verb = $amount >= 0 ? 'إضافة' : 'خصم';
            $abs = abs($amount);

            $this->notifications->notify(
                $lockedUser,
                'تعديل رصيد المحفظة',
                "تم {$verb} {$abs} ₪ في رصيد محفظتك. السبب: {$reason}",
                route('account.wallet')
            );

            return $tx;
        });
    }
}
