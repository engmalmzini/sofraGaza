<?php

namespace App\Models;

use App\Models\Concerns\HasStoredReceipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupOrderMember extends Model
{
    use HasStoredReceipt;

    public const STATUS_INVITED = 'invited';
    public const STATUS_READY = 'ready';
    public const STATUS_PAID = 'paid';
    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'group_order_id',
        'user_id',
        'phone',
        'is_host',
        'status',
        'items_json',
        'subtotal',
        'discount_amount',
        'total',
        'payment_method',
        'transfer_receipt_path',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'is_host' => 'boolean',
            'items_json' => 'array',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function groupOrder(): BelongsTo
    {
        return $this->belongsTo(GroupOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): array
    {
        return $this->items_json ?? [];
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isInvited(): bool
    {
        return $this->status === self::STATUS_INVITED;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function displayName(): string
    {
        return $this->user?->name ?: $this->phone;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_INVITED => 'بانتظار الطلب',
            self::STATUS_READY => 'جاهز — بانتظار الدفع',
            self::STATUS_PAID => 'دفع نصيبه',
            self::STATUS_DECLINED => 'اعتذر',
            default => $this->status,
        };
    }

    public function paymentLabel(): string
    {
        return match ($this->payment_method) {
            'wallet' => 'محفظة',
            'receipt' => 'حوالة / إشعار',
            default => '—',
        };
    }
}
