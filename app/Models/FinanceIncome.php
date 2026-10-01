<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceIncome extends Model
{
    public const CATEGORIES = [
        'setup' => 'رسوم إعداد',
        'boost' => 'إعلان',
        'other' => 'دخل إضافي',
    ];

    protected $fillable = [
        'title',
        'amount',
        'received_on',
        'category',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_on' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeInPeriod(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->whereDate('received_on', '>=', $start->toDateString())
            ->whereDate('received_on', '<=', $end->toDateString());
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
