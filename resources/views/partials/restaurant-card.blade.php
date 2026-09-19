@php
    $rating = number_format(4.6 + ($restaurant->id % 4) * 0.1, 1);
    $eta = $restaurant->type === 'cafe' ? '20-30 دقيقة' : '25-35 دقيقة';
@endphp
<a href="{{ route('restaurants.show', $restaurant) }}" class="group rounded-2xl border border-slate-100 bg-surface-container-lowest overflow-hidden shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col">
    <div class="relative h-40 sm:h-44 w-full overflow-hidden bg-stone-100">
        <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $restaurant->name }}" src="{{ $restaurant->coverUrl() }}">
        <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent pointer-events-none"></div>
        <div class="absolute top-2.5 right-2.5 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-secondary"></span>
            <span class="text-label-sm font-label-sm font-bold text-on-surface">مفتوح الآن</span>
        </div>
        <div class="absolute top-2.5 left-2.5 bg-primary text-on-primary text-label-sm font-label-sm font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $restaurant->badgeLabel() }}</div>
    </div>
    <div class="p-3.5 sm:p-4 flex flex-col gap-1.5">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1 min-w-0">
                <h3 class="font-headline-sm text-base font-bold text-stone-900 group-hover:text-primary transition-colors truncate">{{ $restaurant->name }}</h3>
                <span class="material-symbols-outlined text-primary text-[17px] shrink-0">verified</span>
            </div>
            <div class="flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/50 text-amber-900 text-xs font-bold shrink-0">
                <span class="material-symbols-outlined text-[13px] text-amber-500">star</span>
                <span>{{ $rating }}</span>
            </div>
        </div>
        <p class="text-stone-500 text-[13px] font-normal truncate">{{ $restaurant->cuisineLabel() }} • {{ $restaurant->address }}</p>
        <div class="flex items-center gap-2 text-stone-600 text-[12px] pt-1">
            <div class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px] text-stone-400">schedule</span>
                <span>{{ $eta }}</span>
            </div>
            <span class="text-stone-300">•</span>
            <span class="{{ $restaurant->type === 'cafe' ? 'text-stone-700 font-medium' : 'text-secondary font-semibold' }}">
                {{ $restaurant->type === 'cafe' ? 'توصيل 5 ' : 'توصيل مجاني' }}@if($restaurant->type === 'cafe')<span class="ils">₪</span>@endif
            </span>
        </div>
    </div>
</a>
