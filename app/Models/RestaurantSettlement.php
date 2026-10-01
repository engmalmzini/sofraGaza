<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantSettlement extends Model
{
    protected $fillable = [
        'restaurant_id',
        'settled_by',
        'period_start',
        'period_end',
        'sales_total',
        'commission_total',
        'net_total',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'sales_total' => 'decimal:2',
            'commission_total' => 'decimal:2',
            'net_total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function statusLabel(): string
    {
        return $this->isPaid() ? 'تم الدفع' : 'قيد الانتظار';
    }

    public function periodLabel(): string
    {
        return $this->period_start->format('Y/m/d').' — '.$this->period_end->format('Y/m/d');
    }
}
