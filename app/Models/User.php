<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'is_super_admin',
        'admin_active',
        'admin_permissions',
        'photo_path',
        'bike_photo_path',
        'bike_type',
        'courier_status',
        'courier_rejection_reason',
        'courier_verified_at',
        'payout_method',
        'payout_details',
        'points_balance',
        'wallet_balance',
        'referral_code',
        'referred_by_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points_balance' => 'integer',
            'wallet_balance' => 'decimal:2',
            'courier_verified_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'admin_active' => 'boolean',
            'admin_permissions' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (blank($user->referral_code)) {
                $user->referral_code = app(\App\Services\ReferralService::class)->generateUniqueCode();
            }
        });
    }

    public const COURIER_PENDING = 'pending';
    public const COURIER_APPROVED = 'approved';
    public const COURIER_REJECTED = 'rejected';

    public const BIKE_BICYCLE = 'bicycle';
    public const BIKE_ELECTRIC = 'electric';

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->isAdmin() && (bool) $this->is_super_admin;
    }

    public function isActiveAdmin(): bool
    {
        return $this->isAdmin() && $this->admin_active !== false;
    }

    public function adminPermissionKeys(): array
    {
        return array_values(array_filter((array) $this->admin_permissions));
    }

    public function canAccessAdmin(string $module): bool
    {
        if (! $this->isActiveAdmin()) {
            return false;
        }

        if ($module === 'team') {
            return $this->isSuperAdmin();
        }

        if ($module === 'dashboard' || $module === '') {
            return true;
        }

        if ($this->isSuperAdmin() || $this->admin_permissions === null) {
            return true;
        }

        return in_array($module, $this->adminPermissionKeys(), true);
    }

    public function adminRoleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'المدير الأعلى';
        }

        $keys = $this->adminPermissionKeys();
        if ($keys === []) {
            return 'مدير بصلاحيات محدودة';
        }

        $labels = array_map(
            fn (string $key) => \App\Support\AdminAccess::MODULES[$key]['label'] ?? $key,
            array_slice($keys, 0, 3)
        );

        $suffix = count($keys) > 3 ? '…' : '';

        return 'مدير — '.implode('، ', $labels).$suffix;
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AdminAuditLog::class, 'user_id')->latest();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class)->latest();
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_id');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_id');
    }

    public function ensureReferralCode(): string
    {
        if (filled($this->referral_code)) {
            return $this->referral_code;
        }

        $this->forceFill([
            'referral_code' => app(\App\Services\ReferralService::class)->generateUniqueCode(),
        ])->save();

        return $this->referral_code;
    }

    public function isRestaurantOwner(): bool
    {
        return $this->role === 'restaurant_owner';
    }

    public function isPartner(): bool
    {
        return $this->isRestaurantOwner();
    }

    public function partnerPanelRoute(): string
    {
        return route('partner.dashboard');
    }

    public function canShopAsCustomer(): bool
    {
        return ! $this->isAdmin() && ! $this->isRestaurantOwner();
    }

    public function staffHomeRoute(): string
    {
        if ($this->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($this->isRestaurantOwner()) {
            return $this->partnerPanelRoute();
        }

        if ($this->isCourier()) {
            return route('courier.dashboard');
        }

        return route('home');
    }

    public function publicAccountUrl(): string
    {
        if ($this->isCourier()) {
            return route('courier.dashboard');
        }

        if (! $this->canShopAsCustomer()) {
            return $this->staffHomeRoute();
        }

        return route('account.show');
    }

    public static function currentCanShop(): bool
    {
        $user = auth()->user();

        return ! $user || $user->canShopAsCustomer();
    }

    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }

    public function isCourierPending(): bool
    {
        return $this->isCourier() && $this->courier_status === self::COURIER_PENDING;
    }

    public function isCourierRejected(): bool
    {
        return $this->isCourier() && $this->courier_status === self::COURIER_REJECTED;
    }

    public function isCourierApproved(): bool
    {
        return $this->isCourier() && ! $this->isCourierPending() && ! $this->isCourierRejected();
    }

    public function courierStatusLabel(): string
    {
        return match ($this->courier_status) {
            self::COURIER_PENDING => 'جاري التحقق',
            self::COURIER_REJECTED => 'مرفوض',
            default => 'مقبول',
        };
    }

    public function bikeTypeLabel(): string
    {
        return match ($this->bike_type) {
            self::BIKE_ELECTRIC => 'دراجة كهربائية',
            self::BIKE_BICYCLE => 'دراجة هوائية',
            default => 'غير محدد',
        };
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function bikePhotoUrl(): ?string
    {
        return $this->bike_photo_path ? Storage::disk('public')->url($this->bike_photo_path) : null;
    }

    public function ownedRestaurant(): HasOne
    {
        return $this->hasOne(Restaurant::class, 'owner_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->latest();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function hostedGroupOrders(): HasMany
    {
        return $this->hasMany(GroupOrder::class, 'host_user_id');
    }

    public function groupOrderMembers(): HasMany
    {
        return $this->hasMany(GroupOrderMember::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id')->latest();
    }

    public function courierPayouts(): HasMany
    {
        return $this->hasMany(CourierPayout::class)->latest();
    }

    public function courierDeliveredOrders(): HasMany
    {
        return $this->deliveries()->where('status', 'delivered');
    }

    public function courierTotalGrossDeliveryFees(): float
    {
        return (float) $this->courierDeliveredOrders()->sum('delivery_fee');
    }

    public function courierLifetimePlatformFee(): float
    {
        return round($this->courierTotalGrossDeliveryFees() * 0.15, 2);
    }

    public function courierLifetimeNetEarnings(): float
    {
        return round($this->courierTotalGrossDeliveryFees() * 0.85, 2);
    }

    public function courierTotalWithdrawn(): float
    {
        return (float) $this->courierPayouts()->where('status', CourierPayout::STATUS_COMPLETED)->sum('amount');
    }

    public function courierPendingPayoutsAmount(): float
    {
        return (float) $this->courierPayouts()->where('status', CourierPayout::STATUS_PENDING)->sum('amount');
    }

    public function courierAvailableBalance(): float
    {
        $net = $this->courierLifetimeNetEarnings();
        $withdrawn = $this->courierTotalWithdrawn();
        $pending = $this->courierPendingPayoutsAmount();

        return max(0.0, round($net - $withdrawn - $pending, 2));
    }

    public function courierEarningsForPeriod(string $period = 'all'): array
    {
        $query = $this->courierDeliveredOrders()->with(['restaurant', 'user']);

        match ($period) {
            'today' => $query->whereDate('delivered_at', today()),
            'yesterday' => $query->whereDate('delivered_at', today()->subDay()),
            'week' => $query->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('delivered_at', now()->month)->whereYear('delivered_at', now()->year),
            default => null,
        };

        $orders = $query->latest('delivered_at')->get();
        $totalFees = (float) $orders->sum('delivery_fee');
        $platformFee = round($totalFees * 0.15, 2);
        $netEarnings = round($totalFees * 0.85, 2);

        return [
            'period' => $period,
            'orders' => $orders,
            'count' => $orders->count(),
            'total_fees' => $totalFees,
            'platform_fee' => $platformFee,
            'net_earnings' => $netEarnings,
        ];
    }

    public function courierPayoutMethodLabel(): string
    {
        return CourierPayout::METHODS[$this->payout_method] ?? ($this->payout_method ?: 'غير محدد');
    }

    public function isCourierBusy(): bool
    {
        if ($this->relationLoaded('deliveries')) {
            return $this->deliveries->contains(fn (Order $order) => $order->status === 'delivering');
        }

        return $this->deliveries()->where('status', 'delivering')->exists();
    }

    public function activeDelivery(): ?Order
    {
        if ($this->relationLoaded('deliveries')) {
            return $this->deliveries->first(fn (Order $order) => $order->status === 'delivering');
        }

        return $this->deliveries()->where('status', 'delivering')->first();
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class)->latest();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MembershipSubscription::class)->latest();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class)->latest();
    }

    public function activeSubscription(): ?MembershipSubscription
    {
        return $this->subscriptions()
            ->with('membership')
            ->where('status', 'approved')
            ->where('ends_at', '>=', now())
            ->orderByDesc('ends_at')
            ->first();
    }

    public function activeMembership(): ?Membership
    {
        return $this->activeSubscription()?->membership;
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function notificationsInboxRoute(): string
    {
        if ($this->isAdmin()) {
            return route('admin.notifications.index');
        }

        if ($this->isRestaurantOwner() && $this->ownedRestaurant) {
            return route('partner.notifications.index');
        }

        if ($this->isCourier()) {
            return route('courier.notifications.index');
        }

        return route('account.notifications');
    }

    public function walletTopups(): HasMany
    {
        return $this->hasMany(WalletTopup::class)->latest();
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function hasSufficientWalletBalance(float $amount): bool
    {
        return (float) $this->wallet_balance >= $amount;
    }

    public function totalSpent(): float
    {
        return (float) ($this->orders()
            ->whereIn('status', ['confirmed', 'preparing', 'delivering', 'delivered'])
            ->selectRaw('COALESCE(SUM(COALESCE(total, subtotal, 0)), 0) as spent')
            ->value('spent') ?? 0);
    }

    public function tier(): array
    {
        $spent = $this->totalSpent();

        if ($spent >= 1000) {
            return [
                'key' => 'platinum',
                'name' => 'المستوى البلاتيني (VIP)',
                'badge' => 'diamond',
                'icon' => 'diamond',
                'color' => 'bg-gradient-to-r from-purple-700 via-indigo-700 to-slate-900 text-white',
                'text_color' => 'text-purple-600',
                'border_color' => 'border-purple-300',
                'bg_soft' => 'bg-purple-50 text-purple-800 border-purple-200',
                'current_spent' => $spent,
                'min_spent' => 1000,
                'next_tier' => null,
                'next_min' => null,
                'remaining' => 0,
                'progress_percent' => 100,
                'perks' => [
                    'أعلى مستوى زبون في سفرة غزة',
                    'نقاط مضاعفة (2x) على كل وجبة',
                    'أولوية فائقة في التوصيل والدعم',
                ],
            ];
        }

        if ($spent >= 600) {
            $nextMin = 1000;
            $progress = round((($spent - 600) / ($nextMin - 600)) * 100);

            return [
                'key' => 'gold',
                'name' => 'المستوى الذهبي',
                'badge' => 'military_tech',
                'icon' => 'military_tech',
                'color' => 'bg-gradient-to-r from-amber-500 via-yellow-600 to-amber-700 text-white',
                'text_color' => 'text-amber-600',
                'border_color' => 'border-amber-300',
                'bg_soft' => 'bg-amber-50 text-amber-900 border-amber-200',
                'current_spent' => $spent,
                'min_spent' => 600,
                'next_tier' => 'البلاتيني (VIP)',
                'next_min' => $nextMin,
                'remaining' => round($nextMin - $spent, 1),
                'progress_percent' => min(100, max(0, $progress)),
                'perks' => [
                    'نقاط مضاعفة (1.5x) على كل وجبة',
                    'أولوية في سرعة تحضير وتوصيل الطلبات',
                    'خصومات حصرية لكبار الزبائن',
                ],
            ];
        }

        if ($spent >= 300) {
            $nextMin = 600;
            $progress = round((($spent - 300) / ($nextMin - 300)) * 100);

            return [
                'key' => 'silver',
                'name' => 'المستوى الفضي',
                'badge' => 'workspace_premium',
                'icon' => 'workspace_premium',
                'color' => 'bg-gradient-to-r from-slate-400 via-zinc-500 to-slate-600 text-white',
                'text_color' => 'text-slate-600',
                'border_color' => 'border-slate-300',
                'bg_soft' => 'bg-slate-100 text-slate-800 border-slate-200',
                'current_spent' => $spent,
                'min_spent' => 300,
                'next_tier' => 'الذهبي',
                'next_min' => $nextMin,
                'remaining' => round($nextMin - $spent, 1),
                'progress_percent' => min(100, max(0, $progress)),
                'perks' => [
                    'نقاط مكافآت إضافية (1.25x)',
                    'أولوية خدمة العملاء والمتابعة',
                ],
            ];
        }

        if ($spent >= 150) {
            $nextMin = 300;
            $progress = round((($spent - 150) / ($nextMin - 150)) * 100);

            return [
                'key' => 'bronze',
                'name' => 'المستوى البرونزي',
                'badge' => 'emoji_events',
                'icon' => 'emoji_events',
                'color' => 'bg-gradient-to-r from-amber-700 via-yellow-800 to-orange-900 text-white',
                'text_color' => 'text-amber-800',
                'border_color' => 'border-amber-700/30',
                'bg_soft' => 'bg-orange-50 text-orange-900 border-orange-200',
                'current_spent' => $spent,
                'min_spent' => 150,
                'next_tier' => 'الفضي',
                'next_min' => $nextMin,
                'remaining' => round($nextMin - $spent, 1),
                'progress_percent' => min(100, max(0, $progress)),
                'perks' => [
                    'دخول نادي زبائن سفرة غزة المعتمدين',
                    'عروض وكوبونات موسمية خاصة',
                ],
            ];
        }

        $nextMin = 150;
        $progress = round(($spent / $nextMin) * 100);

        return [
            'key' => 'starter',
            'name' => 'المستوى المبتدئ',
            'badge' => 'stars',
            'icon' => 'stars',
            'color' => 'bg-gradient-to-r from-slate-200 to-stone-300 text-slate-800',
            'text_color' => 'text-stone-600',
            'border_color' => 'border-slate-200',
            'bg_soft' => 'bg-stone-100 text-stone-700 border-stone-200',
            'current_spent' => $spent,
            'min_spent' => 0,
            'next_tier' => 'البرونزي',
            'next_min' => $nextMin,
            'remaining' => round($nextMin - $spent, 1),
            'progress_percent' => min(100, max(0, $progress)),
            'perks' => [
                'اكتساب نقاط الولاء مع كل طلب',
                'ترقية تلقائية للمستوى البرونزي عند الوصول لـ 150 ₪',
            ],
        ];
    }
}
