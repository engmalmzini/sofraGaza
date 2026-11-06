<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'group_member_id',
        'menu_item_id',
        'name',
        'ordered_by_name',
        'price',
        'quantity',
        'line_total',
        'notes',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'options' => 'array',
        ];
    }

    /**
     * @return list<array{group?: string, name?: string, price?: float|int|string}>
     */
    public function selectedOptions(): array
    {
        return is_array($this->options) ? $this->options : [];
    }

    public function choicesLabel(): string
    {
        return \App\Support\MenuOptions::label($this->selectedOptions());
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function groupMember(): BelongsTo
    {
        return $this->belongsTo(GroupOrderMember::class);
    }
}
