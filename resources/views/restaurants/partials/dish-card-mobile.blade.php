@php
    $points = max(1, (int) floor((float) $item->price / 10));
    $popular = $popular ?? false;
@endphp
<article id="dish-{{ $item->id }}" class="dish-item {{ $sectionKey }} bg-surface-container-lowest p-space-sm rounded-xl flex items-center justify-between gap-space-sm shadow-sm hover:shadow-md transition-shadow scroll-mt-32" data-name="{{ $item->name }}" data-category="{{ $sectionKey }}" data-dish-id="{{ $item->id }}">
    <div class="flex-1 min-w-0 pr-1">
        <div class="flex items-center gap-1.5 mb-1">
            @if($popular)
                <span class="bg-primary-fixed text-on-primary-fixed px-1.5 py-0.5 rounded text-[10px] font-bold">الأكثر طلباً</span>
            @endif
            <span class="font-label-sm text-[11px] text-secondary font-medium">+{{ $points }} {{ $points === 1 ? 'نقطة ولاء' : 'نقاط ولاء' }}</span>
        </div>
        <h3 class="font-label-lg text-[15px] text-on-surface font-bold truncate">{{ $item->name }}</h3>
        @if($item->description)
            <p class="font-body-sm text-[13px] text-on-surface-variant line-clamp-1 mt-0.5">{{ $item->description }}</p>
        @endif
        <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-1">
                <span class="font-headline-sm text-[20px] text-primary font-bold">{{ number_format((float) $item->price, 0) }}</span>
                <span class="ils text-primary">₪</span>
            </div>
            @if($item->is_available)
                <button type="button" class="font-label-sm text-[12px] text-on-surface-variant hover:text-primary underline" data-open-customizer data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}" data-item-price="{{ (float) $item->price }}" data-item-desc="{{ $item->description }}" data-item-points="{{ $points }}" data-add-url="{{ route('cart.add', $item) }}">تخصيص</button>
            @endif
        </div>
    </div>
    <div class="relative w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container">
        @if($item->imageUrl())
            <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-primary-fixed/40 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">restaurant</span>
            </div>
        @endif
        @if($item->is_available)
            <form method="POST" action="{{ route('cart.add', $item) }}">
                @csrf
                <button type="submit" aria-label="إضافة للسلة" class="absolute bottom-1.5 left-1.5 w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md active:scale-95 transition-transform">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                </button>
            </form>
        @endif
    </div>
</article>
