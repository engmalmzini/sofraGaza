@php
    $photo = $offer->imageUrl() ?: $restaurant->coverUrl();
@endphp
<article class="dish-item rd-dish rd-offer{{ $offer->isOrderable() ? '' : ' is-off' }}" data-name="{{ $offer->cartName() }} {{ $offer->itemsLabel() }}" data-category="offers" data-offer-id="{{ $offer->id }}">
    <div class="rd-dish__media">
        @if($photo)
            <img alt="" src="{{ $photo }}" loading="lazy" decoding="async">
        @else
            <span class="rd-dish__fallback material-symbols-outlined">sell</span>
        @endif
        <span class="rd-dish__badge">عرض</span>
        @unless($offer->isOrderable())
            <span class="rd-dish__badge is-muted">غير متوفر</span>
        @endunless
    </div>
    <div class="rd-dish__body">
        <h3 class="rd-dish__title">{{ $offer->cartName() }}</h3>
        <p class="rd-dish__desc">{{ $offer->description ?: $offer->itemsLabel() }}</p>
        @if($offer->savings() > 0)
            <p class="text-xs font-extrabold text-amber-700 mt-1">وفّر {{ number_format($offer->savings(), 0) }} ₪ عن السعر العادي</p>
        @endif
        <div class="rd-dish__foot">
            <span class="rd-dish__price">
                {{ number_format($offer->price, 0) }} <span class="ils">₪</span>
                @if($offer->originalPrice() > (float) $offer->price)
                    <small class="text-stone-400 line-through font-bold ms-1">{{ number_format($offer->originalPrice(), 0) }} ₪</small>
                @endif
            </span>
            @if($offer->isOrderable() && ($canShop ?? true) && ($canOrder ?? true))
                <form method="POST" action="{{ route('cart.add-offer', $offer) }}">
                    @csrf
                    <button type="submit" class="rd-dish__plus" aria-label="إضافة العرض">+</button>
                </form>
            @endif
        </div>
    </div>
</article>
