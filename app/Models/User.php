<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'photo_path',
        'bike_photo_path',
        'bike_type',
        'courier_status',
        'courier_rejection_reason',
        'courier_verified_at',
        'points_balance',
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
            'courier_verified_at' => 'datetime',
        ];
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

    public function isRestaurantOwner(): bool
    {
        return $this->role === 'restaurant_owner';
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

    public function deliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id')->latest();
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
}
