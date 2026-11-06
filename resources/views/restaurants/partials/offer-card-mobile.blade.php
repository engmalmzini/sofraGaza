@php
    $photo = $offer->imageUrl() ?: $restaurant->coverUrl();
@endphp
<article class="dish-item offer-item bg-amber-50/80 border border-amber-200/80 p-space-sm rounded-xl flex items-center justify-between gap-space-sm shadow-sm" data-name="{{ $offer->cartName() }} {{ $offer->itemsLabel() }}" data-category="offers" data-offer-id="{{ $offer->id }}">
    <div class="flex-1 min-w-0 pr-1">
        <div class="flex items-center gap-1.5 mb-1">
            <span class="bg-amber-500 text-white px-1.5 py-0.5 rounded text-[10px] font-extrabold">عرض</span>
            @if($offer->savings() > 0)
                <span class="text-[10px] font-bold text-amber-800">وفّر {{ number_format($offer->savings(), 0) }} ₪</span>
            @endif
        </div>
        <h3 class="font-label-lg text-[15px] text-on-surface font-bold truncate">{{ $offer->cartName() }}</h3>
        <p class="font-body-sm text-[13px] text-on-surface-variant line-clamp-1 mt-0.5">{{ $offer->description ?: $offer->itemsLabel() }}</p>
        <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-1.5">
                <span class="font-headline-sm text-[20px] text-primary font-bold">{{ number_format($offer->price, 0) }}</span>
                <span class="ils text-primary">₪</span>
                @if($offer->originalPrice() > (float) $offer->price)
                    <span class="text-[11px] text-stone-400 line-through font-bold">{{ number_format($offer->originalPrice(), 0) }} ₪</span>
                @endif
            </div>
        </div>
    </div>
    <div class="relative w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container">
        @if($photo)
            <img src="{{ $photo }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
        @else
            <div class="w-full h-full bg-amber-100 text-amber-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">sell</span>
            </div>
        @endif
        @if($offer->isOrderable() && ($canShop ?? true) && ($canOrder ?? true))
            <form method="POST" action="{{ route('cart.add-offer', $offer) }}">
                @csrf
                <button type="submit" aria-label="إضافة العرض للسلة" class="absolute bottom-1.5 left-1.5 w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md active:scale-95 transition-transform">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                </button>
            </form>
        @elseif(! $offer->isOrderable())
            <span class="absolute bottom-1.5 left-1.5 text-[10px] font-bold text-rose-700 bg-white/90 rounded-md px-1">غير متوفر</span>
        @endif
    </div>
</article>
