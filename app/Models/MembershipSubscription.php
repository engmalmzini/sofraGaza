<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MembershipSubscription extends Model
{
    public const STATUSES = [
        'pending' => 'بانتظار التأكيد',
        'approved' => 'مفعّل',
        'rejected' => 'مرفوض',
    ];

    protected $fillable = [
        'user_id',
        'membership_id',
        'amount',
        'status',
        'transfer_receipt_path',
        'starts_at',
        'ends_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
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

    public function daysRemaining(): int
    {
        if (! $this->ends_at || $this->status !== 'approved') {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }
}
