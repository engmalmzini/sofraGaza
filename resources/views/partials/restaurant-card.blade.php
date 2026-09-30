@php
    $rating = number_format((float) ($restaurant->avg_rating ?? $restaurant->averageRating()), 1);
    $reviewsCount = (int) ($restaurant->approved_reviews_count ?? $restaurant->reviewsCount());
    $eta = $restaurant->type === 'cafe' ? '20-25 د' : '25-35 د';
    $minPrice = $restaurant->menu_items_min_price;
    $badge = match ($restaurant->badgeLabel()) {
        'خصم 10% للأعضاء' => 'خصم 10%',
        'نقاط مضاعفة' => 'نقاط ×2',
        'مشروب هدية' => 'هدية',
        default => $restaurant->badgeLabel(),
    };
@endphp
<a href="{{ route('restaurants.show', $restaurant) }}" class="sg-dish-card group">
    <div class="sg-dish-card__media">
        <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" loading="lazy">
        <span class="sg-dish-card__badge">{{ $badge }}</span>
        <button type="button" class="sg-dish-card__fav" aria-label="أضف للمفضلة" onclick="event.preventDefault(); event.stopPropagation(); this.classList.toggle('is-on');">
            <span class="material-symbols-outlined">favorite</span>
        </button>
    </div>
    <div class="sg-dish-card__body">
        <h3 class="sg-dish-card__title">{{ $restaurant->name }}</h3>
        <p class="sg-dish-card__sub">{{ $restaurant->cuisineLabel() }} • {{ $restaurant->areaLabel() }}</p>
        <div class="sg-dish-card__meta">
            @include('partials.star-icon', ['class' => 'sg-dish-card__star'])
            <span class="sg-dish-card__rating">{{ $rating }}</span>
            @if($reviewsCount > 0)
                <span class="sg-dish-card__reviews">({{ $reviewsCount }})</span>
            @endif
            <span class="sg-dish-card__dot"></span>
            <span class="material-symbols-outlined sg-dish-card__clock">schedule</span>
            <span>{{ $eta }}</span>
        </div>
        <div class="sg-dish-card__foot">
            @if($minPrice)
                <span class="sg-dish-card__price">{{ number_format((float) $minPrice, 2) }} <span class="ils">₪</span></span>
            @else
                <span class="sg-dish-card__price">اطلب الآن</span>
            @endif
            <span class="sg-dish-card__plus" aria-hidden="true">+</span>
        </div>
    </div>
</a>
