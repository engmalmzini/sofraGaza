<?php

namespace App\Models;

use App\Models\Concerns\HasStoredReceipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipSubscription extends Model
{
    use HasStoredReceipt;

    public const STATUSES = [
        'pending' => 'بانتظار التأكيد',
        'approved' => 'مفعّل',
        'rejected' => 'مرفوض',
    ];

    public const CARD_STATUSES = [
        'pending' => 'قيد التجهيز',
        'ready' => 'جاهزة للاستلام',
        'delivered' => 'تم التسليم',
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
        'card_status',
        'card_requested_at',
        'card_fulfilled_at',
        'card_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'card_requested_at' => 'datetime',
            'card_fulfilled_at' => 'datetime',
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

    public function daysRemaining(): int
    {
        if (! $this->ends_at || $this->status !== 'approved') {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    public function cardNumber(): string
    {
        return 'SG-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public static function parseCardNumber(?string $input): ?int
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        if ($digits === '') {
            return null;
        }

        return (int) ltrim($digits, '0') ?: (int) $digits;
    }

    public static function findActiveByCardNumber(?string $input): ?self
    {
        $id = self::parseCardNumber($input);

        if (! $id) {
            return null;
        }

        return self::query()
            ->with(['user', 'membership'])
            ->whereKey($id)
            ->where('status', 'approved')
            ->where('ends_at', '>=', now())
            ->first();
    }

    public function cardStatusLabel(): string
    {
        return self::CARD_STATUSES[$this->card_status] ?? 'غير مطلوبة';
    }

    public function canRequestCard(): bool
    {
        return $this->status === 'approved'
            && $this->ends_at?->gte(now())
            && ! in_array($this->card_status, ['pending', 'ready'], true);
    }
}
