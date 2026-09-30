<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierPayout extends Model
{
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

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payout_method',
        'transfer_details',
        'admin_notes',
        'processed_by',
        'processed_at',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function statusBadgeClass(): string
    {
        return $this->statusClass();
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->payout_method] ?? $this->payout_method;
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
