<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Media;
use RuntimeException;

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
        'stock_unlimited',
        'stock_qty',
        'earn_points',
        'redeem_points',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'stock_unlimited' => 'boolean',
            'stock_qty' => 'integer',
            'earn_points' => 'integer',
            'redeem_points' => 'integer',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scopeOrderable($query)
    {
        return $query->where('is_available', true)
            ->where(function ($builder) {
                $builder->where('stock_unlimited', true)
                    ->orWhere('stock_qty', '>', 0);
            });
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }

    public static function stockInputRules(): array
    {
        return [
            'stock_mode' => ['required', 'in:unlimited,limited'],
            'stock_qty' => ['exclude_if:stock_mode,unlimited', 'required', 'integer', 'min:0', 'max:99999'],
        ];
    }

    public static function stockAttributes(array $data): array
    {
        $unlimited = ($data['stock_mode'] ?? 'unlimited') === 'unlimited';

        return [
            'stock_unlimited' => $unlimited,
            'stock_qty' => $unlimited ? null : (int) $data['stock_qty'],
        ];
    }

    public function isUnlimitedStock(): bool
    {
        return (bool) $this->stock_unlimited;
    }

    public function remainingStock(): ?int
    {
        return $this->isUnlimitedStock() ? null : max(0, (int) $this->stock_qty);
    }

    public function isOrderable(): bool
    {
        return (bool) $this->is_available
            && ($this->isUnlimitedStock() || (int) $this->stock_qty > 0);
    }

    public function stockLabel(): string
    {
        if ($this->isUnlimitedStock()) {
            return 'لا تنتهي';
        }

        $qty = (int) $this->stock_qty;

        return $qty < 1 ? 'نفدت الكمية' : $qty.' متبقي';
    }

    public function assertCanSell(int $qty): void
    {
        if ($qty < 1) {
            return;
        }

        if (! $this->is_available) {
            throw new RuntimeException("الصنف {$this->name} لم يعد متوفراً.");
        }

        if ($this->isUnlimitedStock()) {
            return;
        }

        $left = (int) $this->stock_qty;
        if ($left < 1) {
            throw new RuntimeException("الصنف {$this->name} نفدت كميته.");
        }

        if ($qty > $left) {
            throw new RuntimeException("المتبقي من {$this->name} هو {$left} فقط.");
        }
    }

    public function consumeStock(int $qty): void
    {
        if ($qty < 1) {
            return;
        }

        $locked = static::query()->whereKey($this->id)->lockForUpdate()->first();
        if (! $locked) {
            throw new RuntimeException("الصنف {$this->name} غير موجود.");
        }

        $locked->assertCanSell($qty);

        if (! $locked->isUnlimitedStock()) {
            $locked->stock_qty = (int) $locked->stock_qty - $qty;
            $locked->save();
            $this->stock_qty = $locked->stock_qty;
            $this->stock_unlimited = $locked->stock_unlimited;
        }
    }

    public function restoreStock(int $qty): void
    {
        if ($qty < 1) {
            return;
        }

        $locked = static::query()->whereKey($this->id)->lockForUpdate()->first();
        if (! $locked || $locked->isUnlimitedStock()) {
            return;
        }

        $locked->stock_qty = (int) $locked->stock_qty + $qty;
        $locked->save();
        $this->stock_qty = $locked->stock_qty;
    }
}
