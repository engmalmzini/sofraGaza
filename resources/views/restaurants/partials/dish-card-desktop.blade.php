@php
    $points = max(1, (int) floor((float) $item->price / 10));
    $popular = $popular ?? false;
@endphp
<article class="dish-item group bg-surface-container-lowest rounded-2xl p-3.5 border border-slate-200/70 hover:border-slate-300 hover:shadow-md transition-all duration-200 flex items-stretch justify-between gap-3 scroll-mt-36" data-name="{{ $item->name }}" data-category="{{ $sectionKey }}" data-dish-id="{{ $item->id }}">
    <div class="flex flex-col justify-between flex-1 min-w-0 pr-1 py-0.5">
        <div class="flex flex-col">
            <h3 class="font-label-lg text-[15px] font-semibold text-on-surface group-hover:text-primary transition-colors truncate">{{ $item->name }}</h3>
            @if($item->description)
                <p class="text-xs text-slate-500 line-clamp-1 mt-1 font-normal leading-relaxed">{{ $item->description }}</p>
            @endif
        </div>
        <div class="flex items-center justify-between pt-3">
            <div class="flex items-center gap-1.5">
                <span class="text-base font-bold text-on-surface">{{ number_format((float) $item->price, 0) }} <span class="ils">₪</span></span>
                <span class="text-[11px] text-tertiary font-medium bg-tertiary-fixed/30 px-1.5 py-0.5 rounded flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[12px]">stars</span>+{{ $points }} نقطة
                </span>
            </div>
            @if($item->is_available)
                <button type="button" class="text-xs text-slate-500 hover:text-primary font-medium flex items-center gap-0.5 transition-colors" data-open-customizer data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}" data-item-price="{{ (float) $item->price }}" data-item-desc="{{ $item->description }}" data-item-points="{{ $points }}" data-add-url="{{ route('cart.add', $item) }}">
                    <span>تخصيص</span>
                    <span class="material-symbols-outlined text-[14px]">tune</span>
                </button>
            @endif
        </div>
    </div>
    <div class="relative w-28 h-28 shrink-0 rounded-xl overflow-hidden bg-surface-container">
        @if($item->imageUrl())
            <img alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="{{ $item->imageUrl() }}">
        @else
            <div class="w-full h-full bg-primary-fixed/40 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-[32px]">restaurant</span>
            </div>
        @endif
        @if($item->is_available)
            <form method="POST" action="{{ route('cart.add', $item) }}">
                @csrf
                <button type="submit" aria-label="إضافة سريعة" class="absolute bottom-2 left-2 w-8 h-8 rounded-full bg-white/95 text-primary shadow-sm hover:bg-primary hover:text-white flex items-center justify-center transition-all duration-200">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
            </form>
        @endif
        @if($popular)
            <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md bg-white/90 backdrop-blur-sm text-[10px] text-primary font-bold shadow-xs">الأكثر طلباً</span>
        @endif
    </div>
</article>
