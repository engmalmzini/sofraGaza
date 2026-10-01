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
        'panel_suspended',
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
            'panel_suspended' => 'boolean',
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

    public function listingSubscriptions(): HasMany
    {
        return $this->hasMany(RestaurantSubscription::class)->latest();
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(RestaurantSettlement::class)->latest('paid_at');
    }

    public function boosts(): HasMany
    {
        return $this->hasMany(RestaurantBoost::class)->latest('starts_on');
    }

    public function activeBoost(): ?RestaurantBoost
    {
        return $this->boosts()
            ->activeOn()
            ->orderByDesc('ends_on')
            ->first();
    }

    public function pendingBoost(): ?RestaurantBoost
    {
        return $this->boosts()->pending()->latest('id')->first();
    }

    public function isBoosted(): bool
    {
        if (array_key_exists('is_boosted', $this->attributes)) {
            return (bool) $this->getAttribute('is_boosted');
        }

        return $this->activeBoost() !== null;
    }

    public function scopeBoostedFirst(Builder $query): Builder
    {
        return $query
            ->withExists(['boosts as is_boosted' => fn (Builder $boosts) => $boosts->activeOn()])
            ->orderByDesc('is_boosted');
    }

    public function activeListing(): ?RestaurantSubscription
    {
        return $this->listingSubscriptions()
            ->with('plan')
            ->where('status', 'approved')
            ->where('ends_at', '>=', now())
            ->orderByDesc('ends_at')
            ->first();
    }

    public function pendingListing(): ?RestaurantSubscription
    {
        return $this->listingSubscriptions()
            ->with('plan')
            ->where('status', 'pending')
            ->first();
    }

    public function hasPaidAccess(): bool
    {
        return ! $this->panel_suspended;
    }

    public function panelLockMessage(): string
    {
        if ($this->panel_suspended) {
            return 'تم إيقاف لوحة '.$this->venueNounYours().'. بياناتك محفوظة ولن يظهر '.$this->venueNounYours().' على الموقع حتى تعيد الإدارة تفعيله.';
        }

        return '';
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('panel_suspended', false)
            ->where('verification_status', self::VERIFICATION_APPROVED);
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
        return $this->isApproved()
            && $this->is_active
            && ! $this->panel_suspended;
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

    public function isOpen(): bool
    {
        if (! $this->is_active || $this->panel_suspended) {
            return false;
        }

        if ($this->opens_at && $this->closes_at) {
            $now = now()->format('H:i:s');
            if ($this->opens_at <= $this->closes_at) {
                return $now >= $this->opens_at && $now <= $this->closes_at;
            }

            return $now >= $this->opens_at || $now <= $this->closes_at;
        }

        return true;
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
            [
                'key' => 'verification',
                'label' => 'موافقة الإدارة',
                'done' => $this->isApproved(),
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
        if (isset($covers[$this->name])) {
            return $covers[$this->name];
        }

        $categories = config('brand.categories', []);
        $catImages = [];
        foreach ($categories as $cat) {
            $catImages[$cat['key']] = $cat['image'];
        }

        if ($this->cuisine && isset($catImages[$this->cuisine])) {
            return $catImages[$this->cuisine];
        }

        // Match based on keywords in name
        $name = mb_strtolower($this->name);
        if (str_contains($name, 'شاورما') || str_contains($name, 'صاج')) {
            return $catImages['shawarma'] ?? config('brand.restaurant_cover');
        }
        if (str_contains($name, 'برجر') || str_contains($name, 'سناك') || str_contains($name, 'ساندويش')) {
            return $catImages['burger'] ?? config('brand.restaurant_cover');
        }
        if (str_contains($name, 'مشاوي') || str_contains($name, 'مشويات') || str_contains($name, 'طاجن')) {
            return $catImages['grill'] ?? config('brand.restaurant_cover');
        }
        if (str_contains($name, 'بيتزا') || str_contains($name, 'معجنات') || str_contains($name, 'فطائر')) {
            return $catImages['pizza'] ?? config('brand.restaurant_cover');
        }
        if (str_contains($name, 'سمك') || str_contains($name, 'بحري') || str_contains($name, 'جمبري')) {
            return $catImages['seafood'] ?? config('brand.restaurant_cover');
        }
        if (str_contains($name, 'حلو') || str_contains($name, 'كنافة') || str_contains($name, 'مجدوع') || str_contains($name, 'كيك')) {
            return $catImages['sweets'] ?? config('brand.restaurant_cover');
        }
        if (str_contains($name, 'فول') || str_contains($name, 'فطور') || str_contains($name, 'حمص')) {
            return $catImages['breakfast'] ?? config('brand.restaurant_cover');
        }

        return $this->type === 'cafe' ? config('brand.cafe_cover') : config('brand.restaurant_cover');
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
        if ($this->isBoosted()) {
            return 'إعلان';
        }

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

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function approvedReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function reviewsCount(): int
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->where('is_approved', true)->count();
        }

        return $this->approvedReviews()->count();
    }

    public function averageRating(): float
    {
        if ($this->relationLoaded('reviews')) {
            $approved = $this->reviews->where('is_approved', true);
            if ($approved->isEmpty()) {
                return 4.9;
            }
            return round((float) $approved->avg('rating'), 1);
        }

        $avg = $this->approvedReviews()->avg('rating');

        return $avg ? round((float) $avg, 1) : 4.9;
    }

    public function ratingBreakdown(): array
    {
        $reviews = $this->approvedReviews()->get();
        $total = $reviews->count();
        $counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        foreach ($reviews as $rev) {
            $star = (int) $rev->rating;
            if (isset($counts[$star])) {
                $counts[$star]++;
            }
        }

        $breakdown = [];
        foreach ($counts as $star => $count) {
            $breakdown[$star] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return $breakdown;
    }
}
