<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupOrder extends Model
{
    public const STATUS_COLLECTING = 'collecting';
    public const STATUS_READY = 'ready';
    public const STATUS_PLACED = 'placed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'token',
        'host_user_id',
        'restaurant_id',
        'order_id',
        'status',
        'delivery_area',
        'address_details',
        'phone',
        'notes',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupOrderMember::class);
    }

    public function isCollecting(): bool
    {
        return $this->status === self::STATUS_COLLECTING;
    }

    public function isPlaced(): bool
    {
        return $this->status === self::STATUS_PLACED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->isCollecting();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_COLLECTING => 'جاري تجميع الطلبات',
            self::STATUS_READY => 'جاهز للإرسال',
            self::STATUS_PLACED => 'تم إرسال الطلب',
            self::STATUS_CANCELLED => 'ملغي',
            default => $this->status,
        };
    }

    public function hostMember(): ?GroupOrderMember
    {
        return $this->members->firstWhere('is_host', true);
    }

    public function guests()
    {
        return $this->members
            ->where('is_host', false)
            ->where('status', '!=', GroupOrderMember::STATUS_DECLINED)
            ->values();
    }

    public function allGuestsPaid(): bool
    {
        $guests = $this->guests();

        return $guests->isNotEmpty() && $guests->every(fn (GroupOrderMember $member) => $member->isPaid());
    }

    public function hostHasItems(): bool
    {
        $host = $this->hostMember();

        return $host && ! empty($host->items());
    }

    public function canHostPlace(): bool
    {
        return $this->isCollecting()
            && ! $this->isExpired()
            && $this->allGuestsPaid();
    }

    public function foodTotal(): float
    {
        return round($this->members->sum(fn (GroupOrderMember $member) => (float) $member->total), 2);
    }

    public function memberFor(User $user): ?GroupOrderMember
    {
        return $this->members->firstWhere('user_id', $user->id);
    }

    public function actionUrlFor(User $user): string
    {
        if ($this->isPlaced() && $this->order_id) {
            return route('account.orders.show', $this->order_id);
        }

        $member = $this->memberFor($user);

        if ($member?->is_host) {
            return $this->canHostPlace()
                ? route('group-orders.checkout', $this)
                : route('group-orders.show', $this);
        }

        if ($member?->isPaid()) {
            return route('group-orders.show', $this);
        }

        return route('group-orders.pay', $this);
    }
}
