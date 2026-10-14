<?php

namespace App\Models;

use App\Models\Concerns\HasStoredReceipt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantPayout extends Model
{
    use HasStoredReceipt;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const METHODS = [
        'jawwal_pay' => 'محفظة جوال باي (Jawwal Pay)',
        'palpay' => 'محفظة بال باي (PalPay)',
        'bank_palestine' => 'بنك فلسطين',
        'cash' => 'استلام نقدي مباشر',
        'other' => 'طريقة أخرى',
    ];

    public const PERIODS = [
        'today' => 'يومي',
        'week' => 'أسبوعي',
        'month' => 'شهري',
        'all' => 'كل المتاح',
    ];

    protected $fillable = [
        'restaurant_id',
        'amount',
        'status',
        'period',
        'payout_method',
        'transfer_details',
        'admin_notes',
        'transfer_receipt_path',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'قيد المراجعة',
            self::STATUS_COMPLETED => 'تم التحويل',
            self::STATUS_REJECTED => 'مرفوض',
            default => $this->status,
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-900',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-900',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-900',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->payout_method] ?? $this->payout_method;
    }

    public function periodLabel(): string
    {
        return self::PERIODS[$this->period] ?? $this->period;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
