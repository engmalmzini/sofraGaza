<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    public const STATUSES = [
        'pending_confirmation' => 'بانتظار التأكيد',
        'confirmed' => 'مؤكد',
        'preparing' => 'قيد التحضير',
        'delivering' => 'قيد التوصيل',
        'delivered' => 'تم التسليم',
        'rejected' => 'مرفوض',
        'cancelled' => 'ملغي',
    ];

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'membership_id',
        'type',
        'status',
        'address_details',
        'phone',
        'notes',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'delivery_fee',
        'total',
        'points_earned',
        'points_spent',
        'transfer_receipt_path',
        'rejection_reason',
        'confirmed_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function receiptUrl(): ?string
    {
        return $this->transfer_receipt_path
            ? Storage::disk('public')->url($this->transfer_receipt_path)
            : null;
    }

    public function canCancel(): bool
    {
        return $this->status === 'pending_confirmation';
    }

    public function nextStatuses(): array
    {
        return match ($this->status) {
            'pending_confirmation' => ['confirmed' => 'تأكيد الطلب', 'rejected' => 'رفض الطلب'],
            'confirmed' => ['preparing' => 'بدء التحضير'],
            'preparing' => ['delivering' => 'بدء التوصيل'],
            'delivering' => ['delivered' => 'تم التسليم'],
            default => [],
        };
    }
}
