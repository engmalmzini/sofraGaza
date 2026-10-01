<?php

namespace App\Services;

use App\Models\GroupOrder;
use App\Models\GroupOrderMember;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\PalestinianPhone;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use RuntimeException;

class GroupOrderService
{
    public function __construct(
        private CartService $cart,
        private NotificationService $notifications,
        private WalletService $wallet,
    ) {}

    public function create(User $host, Restaurant $restaurant, array $phones): GroupOrder
    {
        if (! $restaurant->isVisible()) {
            throw new RuntimeException('هذا المطعم غير متاح للطلب حالياً.');
        }

        $normalized = $this->uniqueGuestPhones($host, $phones);

        if ($normalized === []) {
            throw new RuntimeException('أضف رقم هاتف واحد على الأقل لشخص يريد الطلب معك.');
        }

        $guests = User::query()
            ->where('role', 'customer')
            ->whereIn('phone', $normalized)
            ->get()
            ->keyBy('phone');

        foreach ($normalized as $phone) {
            if (! $guests->has($phone)) {
                throw new RuntimeException("الرقم {$phone} غير مسجّل كزبون في سفرة غزة. لازم يكون عندو حساب.");
            }
        }

        $existing = $this->activeFor($host);
        if ($existing) {
            throw new RuntimeException('عندك طلب جماعي مفتوح. كمّله أو ألغِه قبل ما تفتح واحد جديد.');
        }

        $group = DB::transaction(function () use ($host, $restaurant, $normalized, $guests) {
            $group = GroupOrder::query()->create([
                'token' => Str::lower(Str::random(24)),
                'host_user_id' => $host->id,
                'restaurant_id' => $restaurant->id,
                'status' => GroupOrder::STATUS_COLLECTING,
                'expires_at' => now()->addHours(6),
            ]);

            $group->members()->create([
                'user_id' => $host->id,
                'phone' => PalestinianPhone::local($host->phone),
                'is_host' => true,
                'status' => GroupOrderMember::STATUS_INVITED,
            ]);

            foreach ($normalized as $phone) {
                $guest = $guests->get($phone);
                $group->members()->create([
                    'user_id' => $guest->id,
                    'phone' => $phone,
                    'is_host' => false,
                    'status' => GroupOrderMember::STATUS_INVITED,
                ]);
            }

            return $group;
        });

        $group->load(['members.user', 'restaurant', 'host']);
        $this->bindSession($group);

        foreach ($group->guests() as $member) {
            if (! $member->user) {
                continue;
            }

            $this->notifications->notify(
                $member->user,
                'طلب جماعي من '.$host->name,
                "{$host->name} فاتح طلب جماعي من {$restaurant->name}. اطلب اللي بدك إياه، وادفع نصيبك. التوصيل واحد للجميع.",
                route('group-orders.show', $group)
            );
        }

        return $group;
    }

    public function activeFor(User $user): ?GroupOrder
    {
        return GroupOrder::query()
            ->with(['members.user', 'restaurant', 'host'])
            ->where('status', GroupOrder::STATUS_COLLECTING)
            ->where(function ($query) use ($user) {
                $query->where('host_user_id', $user->id)
                    ->orWhereHas('members', fn ($members) => $members->where('user_id', $user->id)->where('status', '!=', GroupOrderMember::STATUS_DECLINED));
            })
            ->latest('id')
            ->first();
    }

    public function bindSession(GroupOrder $group): void
    {
        Session::put('group_order_id', $group->id);
        Session::put('group_order_restaurant_id', $group->restaurant_id);
    }

    public function forgetSession(): void
    {
        Session::forget('group_order_id');
        Session::forget('group_order_restaurant_id');
    }

    public function payGuestShare(GroupOrder $group, User $user, array $data, ?UploadedFile $receipt = null): GroupOrderMember
    {
        $group->load(['members.user', 'restaurant']);
        $this->assertCollecting($group);

        $member = $group->memberFor($user);
        if (! $member || $member->is_host) {
            throw new RuntimeException('هذا الدفع مخصّص للمدعوين في الطلب الجماعي.');
        }

        if ($member->isPaid()) {
            throw new RuntimeException('دفعت نصيبك مسبقاً.');
        }

        $quote = $this->cart->quote($user);
        if ($quote['lines'] === [] || (int) $quote['restaurant']?->id !== (int) $group->restaurant_id) {
            throw new RuntimeException('أضف أصنافك من منيو المطعم أولاً ثم ادفع نصيبك.');
        }

        $foodTotal = (float) $quote['items_total'];
        $paymentMethod = $data['payment_method'] ?? 'receipt';
        $isWallet = $paymentMethod === 'wallet';

        if ($isWallet) {
            if (! $user->hasSufficientWalletBalance($foodTotal)) {
                throw new RuntimeException('رصيد المحفظة غير كافٍ لدفع نصيبك.');
            }
            $path = null;
        } else {
            if (! $receipt) {
                throw new RuntimeException('أرفق صورة إشعار الحوالة لنصيبك.');
            }
            $path = $receipt->store('receipts', 'public');
        }

        $member = DB::transaction(function () use ($member, $quote, $foodTotal, $paymentMethod, $isWallet, $path, $user) {
            $member->update([
                'status' => GroupOrderMember::STATUS_PAID,
                'items_json' => $this->snapshotLines($quote['lines']),
                'subtotal' => $quote['subtotal'],
                'discount_amount' => $quote['discount_amount'],
                'total' => $foodTotal,
                'payment_method' => $paymentMethod,
                'transfer_receipt_path' => $path,
                'paid_at' => now(),
            ]);

            if ($isWallet) {
                $this->wallet->charge(
                    $user,
                    $foodTotal,
                    'دفع نصيب طلب جماعي',
                    $member,
                );
            }

            return $member->fresh();
        });

        $this->cart->clear();
        $group->refresh()->load(['members.user', 'host', 'restaurant']);

        $this->notifications->notify(
            $group->host,
            $user->name.' خلّص طلبه الجماعي',
            "{$user->name} طلب ودفع نصيبه (".number_format((float) $member->total, 2).' ₪) في طلب '.$group->restaurant->name.'.',
            route('group-orders.show', $group)
        );

        if ($group->allGuestsPaid()) {
            $this->notifications->notify(
                $group->host,
                'الكل خلّص — كمّل الطلب الجماعي',
                'كل المدعوين دفعوا نصيبهم. أضف أصنافك إن بدك، وبعدين أرسل الطلب بعنوان واحد.',
                route('group-orders.checkout', $group)
            );
        }

        return $member;
    }

    public function place(GroupOrder $group, User $host, array $data, ?UploadedFile $receipt = null): Order
    {
        $group->load(['members.user', 'restaurant', 'host']);
        $this->assertCollecting($group);

        if ($group->host_user_id !== $host->id) {
            throw new RuntimeException('صاحب الطلب الجماعي فقط يقدر يرسله.');
        }

        if (! $group->allGuestsPaid()) {
            throw new RuntimeException('استنى لحتى كل المدعوين يخلّصوا ويدفعوا نصيبهم.');
        }

        $quote = $this->cart->quote($host, $data['area'] ?? null);
        if ($quote['restaurant'] && (int) $quote['restaurant']->id !== (int) $group->restaurant_id) {
            throw new RuntimeException('سلتك من مطعم ثاني. أفرغها أو اطلب من منيو الطلب الجماعي.');
        }

        if ($quote['restaurant'] && $quote['lines'] !== []) {
            $hostFood = (float) $quote['items_total'];
            $hostLines = $quote['lines'];
            $hostSubtotal = (float) $quote['subtotal'];
            $hostDiscount = (float) $quote['discount_amount'];
        } else {
            $hostFood = 0.0;
            $hostLines = [];
            $hostSubtotal = 0.0;
            $hostDiscount = 0.0;
        }

        $deliveryFee = (float) ($quote['delivery_fee'] ?? 0);
        $hostPay = round($hostFood + $deliveryFee, 2);
        $paymentMethod = $data['payment_method'] ?? 'receipt';
        $isWallet = $paymentMethod === 'wallet';

        if ($hostPay > 0) {
            if ($isWallet) {
                if (! $host->hasSufficientWalletBalance($hostPay)) {
                    throw new RuntimeException('رصيد المحفظة غير كافٍ لدفع نصيبك مع التوصيل.');
                }
                $path = null;
            } else {
                if (! $receipt) {
                    throw new RuntimeException('أرفق صورة إشعار الحوالة لنصيبك.');
                }
                $path = $receipt->store('receipts', 'public');
            }
        } else {
            $path = null;
            $paymentMethod = $isWallet ? 'wallet' : 'receipt';
        }

        $guestFood = $group->guests()->sum(fn (GroupOrderMember $member) => (float) $member->total);
        $guestSubtotal = $group->guests()->sum(fn (GroupOrderMember $member) => (float) $member->subtotal);
        $guestDiscount = $group->guests()->sum(fn (GroupOrderMember $member) => (float) $member->discount_amount);
        $subtotal = round($hostSubtotal + $guestSubtotal, 2);
        $discount = round($hostDiscount + $guestDiscount, 2);
        $food = round($hostFood + $guestFood, 2);
        $total = round($food + $deliveryFee, 2);

        $order = DB::transaction(function () use (
            $group, $host, $data, $quote, $hostLines, $hostFood, $hostSubtotal, $hostDiscount,
            $path, $paymentMethod, $isWallet, $hostPay, $subtotal, $discount, $total, $deliveryFee
        ) {
            $hostMember = $group->hostMember();
            $hostMember?->update([
                'status' => GroupOrderMember::STATUS_PAID,
                'items_json' => $this->snapshotLines($hostLines),
                'subtotal' => $hostSubtotal,
                'discount_amount' => $hostDiscount,
                'total' => $hostFood,
                'payment_method' => $hostPay > 0 ? $paymentMethod : $hostMember->payment_method,
                'transfer_receipt_path' => $path ?: $hostMember->transfer_receipt_path,
                'paid_at' => now(),
            ]);

            if ($isWallet && $hostPay > 0) {
                $this->wallet->charge(
                    $host,
                    $hostPay,
                    'دفع نصيب الطلب الجماعي مع التوصيل',
                    $hostMember->fresh(),
                );
            }

            $order = Order::query()->create([
                'user_id' => $host->id,
                'restaurant_id' => $group->restaurant_id,
                'group_order_id' => $group->id,
                'membership_id' => $quote['membership']?->id,
                'type' => 'purchase',
                'payment_method' => $paymentMethod,
                'status' => 'pending_confirmation',
                'delivery_area' => $data['area'] ?? $quote['delivery_area']['key'] ?? null,
                'address_details' => $data['address_details'],
                'phone' => $data['phone'],
                'notes' => trim(($data['notes'] ?? '')."\nطلب جماعي — كل واحد دفع نصيبه."),
                'subtotal' => $subtotal,
                'discount_percent' => $subtotal > 0 ? (int) round(($discount / $subtotal) * 100) : 0,
                'discount_amount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'transfer_receipt_path' => $path,
            ]);

            foreach ($group->members()->orderByDesc('is_host')->get() as $member) {
                foreach ($member->items() as $line) {
                    $order->items()->create([
                        'menu_item_id' => $line['menu_item_id'] ?? null,
                        'group_member_id' => $member->id,
                        'name' => $line['name'],
                        'ordered_by_name' => $member->displayName(),
                        'price' => $line['price'],
                        'quantity' => $line['qty'],
                        'line_total' => $line['line_total'],
                        'notes' => $line['notes'] ?? null,
                    ]);
                }
            }

            $group->update([
                'status' => GroupOrder::STATUS_PLACED,
                'order_id' => $order->id,
                'delivery_area' => $order->delivery_area,
                'address_details' => $order->address_details,
                'phone' => $order->phone,
                'notes' => $order->notes,
            ]);

            return $order;
        });

        $this->cart->clear();
        $this->forgetSession();

        $order->load(['restaurant', 'items', 'user']);

        $this->notifications->notifyAdmins(
            'طلب جماعي جديد بانتظار التأكيد',
            "طلب جماعي #{$order->id} من {$host->name} في {$order->restaurant->name} بقيمة {$order->total} ₪ — ".($group->members->count()).' أشخاص.',
            route('admin.orders.show', $order)
        );

        $owner = $order->restaurant?->owner;
        if ($owner) {
            $this->notifications->notify(
                $owner,
                'طلب جماعي وارد لمطعمك',
                "وصل طلب جماعي #{$order->id} من {$host->name} بقيمة {$order->total} ₪.",
                route('partner.orders.show', $order)
            );
        }

        foreach ($group->guests() as $member) {
            if ($member->user) {
                $this->notifications->notify(
                    $member->user,
                    'تم إرسال الطلب الجماعي',
                    "{$host->name} أرسل طلبكم الجماعي من {$order->restaurant->name}. رقم الطلب #{$order->id}.",
                    route('account.orders.show', $order)
                );
            }
        }

        return $order;
    }

    public function cancel(GroupOrder $group, User $user): void
    {
        $group->load(['members.user', 'restaurant', 'host']);
        $this->assertCollecting($group);

        if ($group->host_user_id !== $user->id) {
            throw new RuntimeException('صاحب الطلب الجماعي فقط يقدر يلغيه.');
        }

        DB::transaction(function () use ($group) {
            foreach ($group->members as $member) {
                if ($member->isPaid() && $member->payment_method === 'wallet' && $member->user && (float) $member->total > 0) {
                    $this->wallet->credit(
                        $member->user,
                        (float) $member->total,
                        'استرداد نصيب طلب جماعي ملغي',
                        $member,
                    );
                }
            }

            $group->update(['status' => GroupOrder::STATUS_CANCELLED]);
        });

        $this->forgetSession();

        foreach ($group->guests() as $member) {
            if ($member->user) {
                $this->notifications->notify(
                    $member->user,
                    'تم إلغاء الطلب الجماعي',
                    $group->host->name.' ألغى الطلب الجماعي من '.$group->restaurant->name.'.',
                    route('home')
                );
            }
        }
    }

    public function decline(GroupOrder $group, User $user): void
    {
        $group->load('members.user');
        $this->assertCollecting($group);
        $member = $group->memberFor($user);

        if (! $member || $member->is_host) {
            throw new RuntimeException('لا يمكنك الاعتذار عن هذا الطلب.');
        }

        if ($member->isPaid()) {
            throw new RuntimeException('دفعت نصيبك، تواصل مع صاحب الطلب للإلغاء.');
        }

        $member->update(['status' => GroupOrderMember::STATUS_DECLINED, 'items_json' => []]);
        $group->refresh()->load(['members.user', 'host', 'restaurant']);

        if ((int) Session::get('group_order_id') === (int) $group->id) {
            $this->forgetSession();
        }

        $this->notifications->notify(
            $group->host,
            $user->name.' اعتذر عن الطلب الجماعي',
            $user->name.' مش رح يطلب معاكم هذه المرة.',
            route('group-orders.show', $group)
        );

        if ($group->guests()->isEmpty()) {
            $this->notifications->notify(
                $group->host,
                'ما ضل حدا في الطلب الجماعي',
                'كل المدعوين اعتذروا. ألغِ الطلب أو أضف ناس جدد بفتح طلب جديد.',
                route('group-orders.show', $group)
            );
        } elseif ($group->allGuestsPaid()) {
            $this->notifications->notify(
                $group->host,
                'الكل خلّص — كمّل الطلب الجماعي',
                'المدعوين الباقين دفعوا نصيبهم. أرسل الطلب بعنوان واحد.',
                route('group-orders.checkout', $group)
            );
        }
    }

    public function snapshotLines(array $lines): array
    {
        return array_values(array_map(function (array $line) {
            $item = $line['item'];

            return [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'qty' => (int) $line['qty'],
                'price' => (float) $item->price,
                'line_total' => (float) $line['line_total'],
                'notes' => $line['notes'] ?? null,
            ];
        }, $lines));
    }

    private function uniqueGuestPhones(User $host, array $phones): array
    {
        $hostPhone = PalestinianPhone::local($host->phone);
        $unique = [];

        foreach ($phones as $raw) {
            $phone = PalestinianPhone::local((string) $raw);
            if ($phone === '' || $phone === $hostPhone) {
                continue;
            }
            if (! preg_match(PalestinianPhone::CUSTOMER_PATTERN, $phone)) {
                throw new RuntimeException("الرقم {$phone} غير صالح. استخدم رقم يبدأ بـ 05 من 10 خانات.");
            }
            $unique[$phone] = $phone;
        }

        return array_values($unique);
    }

    private function assertCollecting(GroupOrder $group): void
    {
        if ($group->isExpired()) {
            $group->update(['status' => GroupOrder::STATUS_CANCELLED]);
            throw new RuntimeException('انتهت مهلة الطلب الجماعي.');
        }

        if (! $group->isCollecting()) {
            throw new RuntimeException('هذا الطلب الجماعي لم يعد مفتوحاً.');
        }
    }
}
