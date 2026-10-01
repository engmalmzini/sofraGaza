@php
    $popular = $popular ?? false;
    $featured = $featured ?? false;
    $photo = $item->imageUrl() ?: $restaurant->coverUrl();
@endphp
<article
    class="dish-item rd-dish{{ $featured ? ' rd-dish--wide' : '' }}{{ $item->is_available ? '' : ' is-off' }}"
    data-name="{{ $item->name }}"
    data-category="{{ $sectionKey }}"
    data-dish-id="{{ $item->id }}"
>
    <div class="rd-dish__media">
        @if($photo)
            <img alt="{{ $item->name }}" src="{{ $photo }}" loading="lazy">
        @else
            <span class="rd-dish__fallback material-symbols-outlined">restaurant</span>
        @endif
        @if($popular)
            <span class="rd-dish__badge">الأكثر طلباً</span>
        @endif
        @unless($item->is_available)
            <span class="rd-dish__badge is-muted">غير متوفر</span>
        @endunless
        @include('partials.favorite-button', [
            'type' => 'menu_item',
            'id' => $item->id,
            'class' => 'absolute top-2 left-2 w-8 h-8 rounded-full bg-white/92 text-stone-600 flex items-center justify-center shadow-sm z-10',
        ])
    </div>
    <div class="rd-dish__body">
        <h3 class="rd-dish__title">{{ $item->name }}</h3>
        @if($item->description)
            <p class="rd-dish__desc">{{ $item->description }}</p>
        @endif
        <div class="rd-dish__foot">
            <span class="rd-dish__price">{{ number_format((float) $item->price, 0) }} <span class="ils">₪</span></span>
            @if($item->is_available && ($canShop ?? true))
                <div class="rd-dish__actions">
                    <button
                        type="button"
                        class="rd-dish__tune"
                        data-open-customizer
                        data-item-id="{{ $item->id }}"
                        data-item-name="{{ $item->name }}"
                        data-item-price="{{ (float) $item->price }}"
                        data-item-desc="{{ $item->description }}"
                        data-add-url="{{ route('cart.add', $item) }}"
                    >
                        تخصيص
                    </button>
                    <form method="POST" action="{{ route('cart.add', $item) }}">
                        @csrf
                        <button type="submit" class="rd-dish__plus" aria-label="إضافة سريعة">+</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</article>
