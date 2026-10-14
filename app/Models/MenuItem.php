<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Media;

class MenuItem extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'category',
        'description',
        'price',
        'image_path',
        'is_available',
        'earn_points',
        'redeem_points',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'earn_points' => 'integer',
            'redeem_points' => 'integer',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }
}
