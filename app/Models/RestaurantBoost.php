<?php

namespace App\Models;

use App\Models\Concerns\HasStoredReceipt;
use App\Support\Finance;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class RestaurantBoost extends Model
{
    use HasStoredReceipt;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'بانتظار التأكيد',
        self::STATUS_APPROVED => 'مفعّل',
        self::STATUS_REJECTED => 'مرفوض',
    ];

    protected $fillable = [
        'restaurant_id',
        'starts_on',
        'ends_on',
        'daily_rate',
        'days',
        'amount',
        'transfer_receipt_path',
        'banner_path',
        'title',
        'notes',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'daily_rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'days' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActiveOn(Builder $query, CarbonInterface|string|null $date = null): Builder
    {
        $day = $date instanceof CarbonInterface ? $date->toDateString() : ($date ?: now()->toDateString());

        return $query->approved()
            ->whereDate('starts_on', '<=', $day)
            ->whereDate('ends_on', '>=', $day);
    }

    public function scopeOverlapping(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->approved()
            ->whereDate('starts_on', '<=', $end->toDateString())
            ->whereDate('ends_on', '>=', $start->toDateString());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isLive(): bool
    {
        if (! $this->isApproved() || ! $this->starts_on || ! $this->ends_on) {
            return false;
        }

        $today = now()->startOfDay();

        return $this->starts_on->lte($today) && $this->ends_on->gte($today);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function campaignStateLabel(): string
    {
        if (! $this->isApproved()) {
            return $this->statusLabel();
        }

        if ($this->isLive()) {
            return 'نشطة';
        }

        if ($this->ends_on && $this->ends_on->lt(now()->startOfDay())) {
            return 'منتهية';
        }

        return 'قادمة';
    }

    public function campaignStateClass(): string
    {
        return match ($this->campaignStateLabel()) {
            'نشطة' => 'bg-emerald-100 text-emerald-900',
            'منتهية' => 'bg-slate-100 text-slate-700',
            'قادمة' => 'bg-sky-100 text-sky-900',
            default => 'bg-amber-100 text-amber-900',
        };
    }

    public function durationDays(): int
    {
        if ((int) $this->days > 0) {
            return (int) $this->days;
        }

        return $this->overlapDays($this->starts_on, $this->ends_on);
    }

    public function remainingDays(): int
    {
        if (! $this->isLive()) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->ends_on, false)) + 1;
    }

    public function overlapDays(CarbonInterface $start, CarbonInterface $end): int
    {
        $from = $this->starts_on->copy()->startOfDay()->max($start->copy()->startOfDay());
        $to = $this->ends_on->copy()->startOfDay()->min($end->copy()->startOfDay());

        if ($to->lt($from)) {
            return 0;
        }

        return (int) round($from->diffInDays($to, true)) + 1;
    }

    public function incomeIn(CarbonInterface $start, CarbonInterface $end): float
    {
        if (! $this->isApproved()) {
            return 0.0;
        }

        return round($this->overlapDays($start, $end) * (float) $this->daily_rate, 2);
    }

    public function totalIncome(): float
    {
        return round($this->durationDays() * (float) $this->daily_rate, 2);
    }

    public function defaultDailyRate(): float
    {
        return (float) ($this->daily_rate ?: Finance::BOOST_DAILY_RATE);
    }

    public function storeCreative(UploadedFile $file): string
    {
        $path = $file->store('boosts/banners', 'public');
        $old = $this->banner_path;

        $this->forceFill(['banner_path' => $path])->save();

        if (filled($old) && $old !== $path && ! str_starts_with((string) $old, 'http://') && ! str_starts_with((string) $old, 'https://')) {
            Storage::disk('public')->delete($old);
        }

        return $path;
    }

    public function hasCreative(): bool
    {
        return filled($this->banner_path);
    }

    public function creativeUrl(): string
    {
        $path = trim((string) $this->banner_path);

        if ($path !== '') {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, '/')) {
                return url($path);
            }

            if (is_file(public_path($path))) {
                return asset($path);
            }

            return Storage::disk('public')->url($path);
        }

        return $this->restaurant?->coverUrl() ?: '';
    }

    public function restaurantHref(): ?string
    {
        if (! $this->restaurant) {
            return null;
        }

        return route('restaurants.show', $this->restaurant);
    }

    /**
     * @return Collection<int, self>
     */
    public static function liveCreatives(int $limit = 6): Collection
    {
        return static::query()
            ->activeOn()
            ->with('restaurant')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->filter(fn (self $boost) => $boost->restaurant && $boost->restaurant->isVisible())
            ->values();
    }
}
