<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Restaurant extends Model
{
    public const VERIFICATION_PENDING = 'pending';
    public const VERIFICATION_APPROVED = 'approved';
    public const VERIFICATION_REJECTED = 'rejected';

    public const VERIFICATION_STATUSES = [
        self::VERIFICATION_PENDING => 'جاري التحقق',
        self::VERIFICATION_APPROVED => 'موثّق',
        self::VERIFICATION_REJECTED => 'مرفوض',
    ];

    protected $fillable = [
        'owner_id',
        'name',
        'type',
        'cuisine',
        'description',
        'phone',
        'owner_national_id',
        'license_number',
        'address',
        'area',
        'opens_at',
        'closes_at',
        'image_path',
        'starts_at',
        'expires_at',
        'is_active',
        'is_featured',
        'points_per_amount',
        'points_redeem_per_amount',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'expires_at' => 'date',
            'verified_at' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'points_per_amount' => 'decimal:2',
            'points_redeem_per_amount' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where('verification_status', self::VERIFICATION_APPROVED)
            ->whereDate('starts_at', '<=', $today)
            ->whereDate('expires_at', '>=', $today);
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('verification_status', self::VERIFICATION_PENDING);
    }

    public function scopeMatchingCuisine(Builder $query, string $cuisine): Builder
    {
        $keywords = array_values(array_filter(config('brand.cuisine_keywords.'.$cuisine, [])));

        return $query->where(function (Builder $inner) use ($cuisine, $keywords) {
            $inner->where('cuisine', $cuisine);

            if ($cuisine === 'cafe') {
                $inner->orWhere('type', 'cafe');
            }

            foreach ($keywords as $keyword) {
                $like = '%'.$keyword.'%';
                $inner->orWhere('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('menuItems', function (Builder $items) use ($like) {
                        $items->where(function (Builder $item) use ($like) {
                            $item->where('name', 'like', $like)
                                ->orWhere('category', 'like', $like)
                                ->orWhere('description', 'like', $like);
                        });
                    });
            }
        });
    }

    public static function homeCategories(): array
    {
        $base = static::query()->visible();

        return array_map(function (array $category) use ($base) {
            $count = (clone $base)->matchingCuisine($category['key'])->count();
            $category['places'] = $count;
            $category['count'] = static::formatPlaceCount($count, $category['key']);

            return $category;
        }, config('brand.categories', []));
    }

    public static function formatPlaceCount(int $count, string $categoryKey): string
    {
        $kind = match ($categoryKey) {
            'cafe' => 'cafe',
            'sweets' => 'shop',
            default => 'restaurant',
        };

        $forms = match ($kind) {
            'cafe' => ['zero' => 'لا كافيهات', 'one' => 'كافيه واحد', 'dual' => 'كافيهان', 'few' => 'كافيهات', 'many' => 'كافيه'],
            'shop' => ['zero' => 'لا متاجر', 'one' => 'متجر واحد', 'dual' => 'متجران', 'few' => 'متاجر', 'many' => 'متجراً'],
            default => ['zero' => 'لا مطاعم', 'one' => 'مطعم واحد', 'dual' => 'مطعمان', 'few' => 'مطاعم', 'many' => 'مطعماً'],
        };

        return match (true) {
            $count <= 0 => $forms['zero'],
            $count === 1 => $forms['one'],
            $count === 2 => $forms['dual'],
            $count <= 10 => $count.' '.$forms['few'],
            default => $count.' '.$forms['many'],
        };
    }

    public function scopeInDeliveryArea(Builder $query): Builder
    {
        $areaKey = session('delivery_area.key');

        if (! $areaKey) {
            return $query;
        }

        $matches = (clone $query)->where(function (Builder $inner) use ($areaKey) {
            $inner->where('area', $areaKey)
                ->orWhere('address', 'like', '%'.$areaKey.'%');
        })->exists();

        if ($matches) {
            $query->where(function (Builder $inner) use ($areaKey) {
                $inner->where('area', $areaKey)
                    ->orWhere('address', 'like', '%'.$areaKey.'%');
            });
        }

        return $query;
    }

    public function isVisible(): bool
    {
        $today = now()->startOfDay();

        return $this->isApproved()
            && $this->is_active
            && $this->starts_at
            && $this->expires_at
            && $this->starts_at->lte($today)
            && $this->expires_at->gte($today);
    }

    public function isPending(): bool
    {
        return $this->verification_status === self::VERIFICATION_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->verification_status === self::VERIFICATION_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->verification_status === self::VERIFICATION_REJECTED;
    }

    public function verificationLabel(): string
    {
        return self::VERIFICATION_STATUSES[$this->verification_status] ?? $this->verification_status;
    }

    public function hoursLabel(): string
    {
        if (! $this->opens_at || ! $this->closes_at) {
            return 'ساعات العمل غير محددة';
        }

        return substr((string) $this->opens_at, 0, 5).' — '.substr((string) $this->closes_at, 0, 5);
    }

    public function areaLabel(): string
    {
        $areas = collect(config('brand.areas', []))->keyBy('key');

        return $areas[$this->area]['label'] ?? ($this->area ?: ($this->address ?: 'غزة'));
    }

    public function setupChecklist(): array
    {
        $menuCount = $this->relationLoaded('menuItems')
            ? $this->menuItems->count()
            : (int) ($this->menu_items_count ?? $this->menuItems()->count());

        return [
            [
                'key' => 'profile',
                'label' => 'بيانات '.$this->venueNoun().' الأساسية',
                'done' => filled($this->description) && filled($this->address) && filled($this->phone),
            ],
            [
                'key' => 'hours',
                'label' => 'ساعات العمل',
                'done' => filled($this->opens_at) && filled($this->closes_at),
            ],
            [
                'key' => 'cover',
                'label' => 'صورة الغلاف',
                'done' => filled($this->image_path),
            ],
            [
                'key' => 'menu',
                'label' => 'إضافة أصناف للمنيو',
                'done' => $menuCount > 0,
            ],
        ];
    }

    public function daysRemaining(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->expires_at, false));
    }

    public function isExpiringSoon(int $warningDays = 7): bool
    {
        $days = $this->daysRemaining();

        return $this->isVisible() && $days <= $warningDays;
    }

    public function typeLabel(): string
    {
        return $this->type === 'cafe' ? 'كافي' : 'مطعم';
    }

    public function venueNoun(): string
    {
        return $this->type === 'cafe' ? 'الكافي' : 'المطعم';
    }

    public function venueNounYours(): string
    {
        return $this->type === 'cafe' ? 'كافيك' : 'مطعمك';
    }

    public function panelTitle(): string
    {
        return $this->type === 'cafe' ? 'لوحة الكافي' : 'لوحة المطعم';
    }

    public function defaultMenuCategories(): array
    {
        return $this->type === 'cafe'
            ? ['مشروبات ساخنة', 'مشروبات باردة', 'حلويات', 'معجنات', 'وجبات خفيفة']
            : ['وجبات', 'مشروبات', 'مقبلات', 'حلويات'];
    }

    public function menuCategories(): array
    {
        $used = $this->menuItems()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->all();

        return array_values(array_unique([...$this->defaultMenuCategories(), ...$used]));
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function coverUrl(): string
    {
        if ($this->imageUrl()) {
            return $this->imageUrl();
        }

        $covers = config('brand.covers', []);

        return $covers[$this->name]
            ?? ($this->type === 'cafe' ? config('brand.cafe_cover') : config('brand.restaurant_cover'));
    }

    public function cuisineLabel(): string
    {
        $cuisines = config('brand.cuisines', []);

        if ($this->cuisine && isset($cuisines[$this->cuisine])) {
            return $cuisines[$this->cuisine];
        }

        return $this->type === 'cafe' ? 'قهوة ومشروبات' : 'مأكولات غزية';
    }

    public function badgeLabel(): string
    {
        if ($this->type === 'cafe') {
            return 'مشروب هدية';
        }

        if ($this->is_featured) {
            return 'خصم 10% للأعضاء';
        }

        return 'نقاط مضاعفة';
    }

    public function expiryStatus(): string
    {
        if ($this->expires_at->lt(now()->startOfDay())) {
            return 'expired';
        }

        if ($this->isExpiringSoon((int) Setting::value('restaurant_expiry_warning_days', 7))) {
            return 'soon';
        }

        return 'active';
    }
}
