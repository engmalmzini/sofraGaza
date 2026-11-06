<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use RuntimeException;

class MenuOffer extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'price',
        'image_path',
        'is_active',
        'first_item_id',
        'second_item_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function firstItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'first_item_id');
    }

    public function secondItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'second_item_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public function includedItems(): Collection
    {
        return collect([$this->firstItem, $this->secondItem])->filter();
    }

    public function originalPrice(): float
    {
        return round((float) $this->includedItems()->sum(fn (MenuItem $item) => (float) $item->price), 2);
    }

    public function savings(): float
    {
        return max(0, round($this->originalPrice() - (float) $this->price, 2));
    }

    public function itemsLabel(): string
    {
        return $this->includedItems()->pluck('name')->filter()->implode(' + ');
    }

    public function cartName(): string
    {
        $name = trim((string) $this->name);

        return $name !== '' ? $name : 'عرض: '.$this->itemsLabel();
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path) ?: $this->firstItem?->imageUrl();
    }

    public function isOrderable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $items = $this->includedItems();

        return $items->isNotEmpty() && $items->every(fn (MenuItem $item) => $item->isOrderable());
    }

    public function assertCanSell(int $qty): void
    {
        if ($qty < 1) {
            return;
        }

        if (! $this->is_active) {
            throw new RuntimeException('هذا العرض غير متوفر حالياً.');
        }

        foreach ($this->includedItems() as $item) {
            $item->assertCanSell($qty);
        }
    }

    public function consumeStock(int $qty): void
    {
        foreach ($this->includedItems() as $item) {
            $item->consumeStock($qty);
        }
    }

    /**
     * @return list<array{group: string, name: string, price: float, menu_item_id: int}>
     */
    public function orderOptions(): array
    {
        return $this->includedItems()->map(fn (MenuItem $item) => [
            'group' => 'يشمل العرض',
            'name' => $item->name,
            'price' => 0,
            'menu_item_id' => (int) $item->id,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function inputRules(Restaurant $restaurant): array
    {
        $belongs = Rule::exists('menu_items', 'id')->where('restaurant_id', $restaurant->id);

        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0.5', 'max:9999'],
            'first_item_id' => ['required', 'integer', $belongs],
            'second_item_id' => ['nullable', 'integer', 'different:first_item_id', $belongs],
            'is_active' => ['nullable'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
