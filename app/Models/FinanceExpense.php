<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceExpense extends Model
{
    public const CATEGORIES = [
        'operating' => 'تشغيل',
        'gift' => 'هدية عضوية',
        'other' => 'أخرى',
    ];

    protected $fillable = [
        'title',
        'amount',
        'spent_on',
        'category',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_on' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeInPeriod(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->whereDate('spent_on', '>=', $start->toDateString())
            ->whereDate('spent_on', '<=', $end->toDateString());
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
