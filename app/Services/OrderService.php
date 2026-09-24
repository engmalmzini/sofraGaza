<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(
        private CartService $cart,
        private PointsService $points,
        private NotificationService $notifications,
    ) {}

    public function placePurchase(User $user, array $data, UploadedFile $receipt): Order
    {
        $quote = $this->cart->quote($user);

        if ($quote['lines'] === [] || ! $quote['restaurant']) {
            throw new RuntimeException('السلة فارغة.');
        }

        foreach ($quote['lines'] as $line) {
            /** @var MenuItem $item */
            $item = $line['item'];
            if (! $item->is_available) {
                throw new RuntimeException("الصنف {$item->name} لم يعد متوفراً.");
            }
        }

        $path = $receipt->store('receipts', 'public');

        $order = DB::transaction(function () use ($user, $data, $quote, $path) {
            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $quote['restaurant']->id,
                'membership_id' => $quote['membership']?->id,
                'type' => 'purchase',
                'status' => 'pending_confirmation',
                'address_details' => $data['address_details'],
                'phone' => $data['phone'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $quote['subtotal'],
                'discount_percent' => $quote['discount_percent'],
                'discount_amount' => $quote['discount_amount'],
                'delivery_fee' => $quote['delivery_fee'],
                'total' => $quote['total'],
                'transfer_receipt_path' => $path,
            ]);

            foreach ($quote['lines'] as $line) {
                $order->items()->create([
                    'menu_item_id' => $line['item']->id,
                    'name' => $line['item']->name,
                    'price' => $line['item']->price,
                    'quantity' => $line['qty'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        $this->notifications->notifyAdmins(
            'طلب جديد بانتظار التأكيد',
            "طلب #{$order->id} من {$user->name} بقيمة {$order->total} ₪.",
            route('admin.orders.show', $order)
        );

        $this->notifyRestaurantOwner($order, 'طلب جديد على مطعمك', "طلب #{$order->id} من {$user->name} بقيمة {$order->total} ₪.");

        return $order;
    }

    public function placeRedemption(User $user, MenuItem $item, array $data): Order
    {
        if (! $item->is_available || ! $item->restaurant->isVisible()) {
            throw new RuntimeException('لا يمكن استبدال هذا الصنف حالياً.');
        }

        $cost = $this->points->redeemCost($item);

        if ($user->points_balance < $cost) {
            throw new RuntimeException('رصيد النقاط غير كافٍ لهذا الاستبدال.');
        }

        $order = DB::transaction(function () use ($user, $item, $data, $cost) {
            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $item->restaurant_id,
                'membership_id' => $user->activeMembership()?->id,
                'type' => 'redemption',
                'status' => 'pending_confirmation',
                'address_details' => $data['address_details'],
                'phone' => $data['phone'],
                'notes' => $data['notes'] ?? 'استبدال نقاط',
                'subtotal' => 0,
                'total' => 0,
                'points_spent' => $cost,
            ]);

            $order->items()->create([
                'menu_item_id' => $item->id,
                'name' => $item->name.' (استبدال نقاط)',
                'price' => 0,
                'quantity' => 1,
                'line_total' => 0,
            ]);

            $this->points->spend($user, $cost, "استبدال نقاط بـ {$item->name}", $order->id);

            return $order;
        });

        $this->notifications->notifyAdmins(
            'طلب استبدال نقاط',
            "{$user->name} استبدل {$order->points_spent} نقطة من {$item->restaurant->name}.",
            route('admin.orders.show', $order)
        );

        $this->notifyRestaurantOwner($order, 'طلب استبدال نقاط', "{$user->name} استبدل {$order->points_spent} نقطة من مطعمك.");

        return $order;
    }

    private function notifyRestaurantOwner(Order $order, string $title, string $body): void
    {
        $order->loadMissing('restaurant.owner');
        $owner = $order->restaurant?->owner;

        if (! $owner) {
            return;
        }

        $this->notifications->notify(
            $owner,
            $title,
            $body,
            route('partner.orders.show', $order)
        );
    }

    public function changeStatus(Order $order, string $status, ?string $reason = null): void
    {
        $old = $order->status;

        if ($status === 'rejected' && $old === 'pending_confirmation') {
            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            if ($order->points_spent > 0) {
                $this->points->refund(
                    $order->user,
                    $order->points_spent,
                    'إعادة نقاط بعد رفض طلب #'.$order->id,
                    $order->id
                );
            }

            $this->notifications->notify(
                $order->user,
                'تم رفض الطلب',
                $reason ?: "تم رفض طلبك رقم #{$order->id}.",
                route('account.orders.show', $order)
            );

            return;
        }

        if ($status === 'cancelled' && $order->canCancel()) {
            $order->update(['status' => 'cancelled']);

            if ($order->points_spent > 0) {
                $this->points->refund(
                    $order->user,
                    $order->points_spent,
                    'إعادة نقاط بعد إلغاء طلب #'.$order->id,
                    $order->id
                );
            }

            return;
        }

        $allowed = array_keys($order->nextStatuses());
        if (! in_array($status, $allowed, true)) {
            throw new RuntimeException('لا يمكن تحويل حالة الطلب إلى هذه المرحلة.');
        }

        $payload = ['status' => $status];
        if ($status === 'confirmed') {
            $payload['confirmed_at'] = now();
        }
        if ($status === 'delivered') {
            $payload['delivered_at'] = now();
        }

        $order->update($payload);

        if (in_array($status, ['preparing', 'delivering'], true) && ! $order->courier_id) {
            // الطلبات لا تُعرض لكل المندوبين؛ الإدارة تعيّن مندوباً بعينه.
        }

        if ($status === 'delivered') {
            $this->points->earnForOrder($order->fresh());
        }

        $this->notifications->notify(
            $order->user,
            'تحديث حالة الطلب',
            "طلبك رقم #{$order->id} أصبح: ".$order->fresh()->statusLabel(),
            route('account.orders.show', $order)
        );
    }

    public function claimForCourier(Order $order, User $courier): void
    {
        throw new RuntimeException('الطلبات لا تُؤخذ من القائمة العامة. الإدارة ترسل الطلب لمندوب محدد.');
    }

    public function assignCourier(Order $order, User $courier): void
    {
        if (! $courier->isCourierApproved()) {
            throw new RuntimeException('يمكن تعيين المندوبين المقبولين فقط.');
        }

        if (! in_array($order->status, ['preparing', 'delivering'], true)) {
            throw new RuntimeException('الطلب ليس جاهزاً للتعيين على التوصيل بعد.');
        }

        $previousId = $order->courier_id;

        DB::transaction(function () use ($order, $courier) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, ['preparing', 'delivering'], true)) {
                throw new RuntimeException('تعذر تعيين المندوب على هذا الطلب.');
            }

            $locked->update([
                'courier_id' => $courier->id,
                'status' => 'delivering',
            ]);
        });

        $order->refresh()->loadMissing('restaurant');

        if ($previousId && $previousId !== $courier->id) {
            $previous = User::query()->find($previousId);
            if ($previous) {
                $this->notifications->notify(
                    $previous,
                    'أُلغي تعيين طلب',
                    "طلب #{$order->id} نُقل لمندوب آخر.",
                    route('courier.dashboard')
                );
            }
        }

        $this->notifications->notify(
            $courier,
            'تعيين توصيل',
            "الإدارة أرسلت لك طلب #{$order->id} من {$order->restaurant?->name}. افتح لوحتك وتابع التوصيل.",
            route('courier.orders.show', $order)
        );

        $this->notifications->notify(
            $order->user,
            'المندوب في الطريق',
            "مندوب التوصيل {$courier->name} معيّن على طلبك رقم #{$order->id}.",
            route('account.orders.show', $order)
        );
    }

    public function unassignCourier(Order $order): void
    {
        if (! $order->courier_id || $order->status === 'delivered') {
            throw new RuntimeException('لا يوجد مندوب لإلغاء تعيينه على هذا الطلب.');
        }

        $previous = $order->courier;

        $order->update(['courier_id' => null]);

        if ($previous) {
            $this->notifications->notify(
                $previous,
                'أُلغي تعيين طلب',
                "طلب #{$order->id} عاد لقائمة الانتظار.",
                route('courier.dashboard')
            );
        }
    }

    public function completeCourierDelivery(Order $order, User $courier): void
    {
        if ($order->courier_id !== $courier->id || $order->status !== 'delivering') {
            throw new RuntimeException('ما تقدر تسجّل تسليم هذا الطلب.');
        }

        $this->changeStatus($order, 'delivered');
    }
}
