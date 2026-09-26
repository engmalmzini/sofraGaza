<?php

namespace App\Models;

use App\Models\Concerns\HasStoredReceipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantSubscription extends Model
{
    use HasStoredReceipt;

    public const STATUSES = [
        'pending' => 'بانتظار تأكيد الحوالة',
        'approved' => 'مفعّل',
        'rejected' => 'مرفوض',
    ];

    protected $fillable = [
        'restaurant_id',
        'restaurant_plan_id',
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

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(RestaurantPlan::class, 'restaurant_plan_id');
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

    public function isActive(): bool
    {
        return $this->status === 'approved'
            && $this->ends_at
            && $this->ends_at->gte(now());
    }

    public function isExpiringSoon(int $warningDays = 7): bool
    {
        $days = $this->daysRemaining();

        return $this->isActive() && $days > 0 && $days <= $warningDays;
    }
}
