<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRestaurantOwner(): bool
    {
        return $this->role === 'restaurant_owner';
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

        return route('account.notifications');
    }
}
