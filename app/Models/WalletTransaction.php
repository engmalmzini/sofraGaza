<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    public const TYPE_TOPUP = 'topup';
    public const TYPE_ORDER_PAYMENT = 'order_payment';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'reference_id',
        'reference_type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_TOPUP => 'شحن رصيد',
            self::TYPE_ORDER_PAYMENT => 'دفع طلب',
            self::TYPE_REFUND => 'استرداد رصيد',
            self::TYPE_ADMIN_ADJUSTMENT => 'تعديل إداري',
            default => $this->type,
        };
    }

    public function isCredit(): bool
    {
        return (float) $this->amount > 0;
    }
}
