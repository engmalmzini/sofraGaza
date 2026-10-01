@php
    $canShopHere = $canShop ?? true;
    $activeGroup = $activeGroupOrder ?? null;
    $sameRestaurant = $activeGroup && (int) $activeGroup->restaurant_id === (int) $restaurant->id;
@endphp
@if($canShopHere)
    @if($sameRestaurant)
        <a href="{{ route('group-orders.show', $activeGroup) }}"
           class="{{ $entryClass ?? 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-primary/30 bg-primary/10 text-primary font-extrabold text-xs px-3 py-2 hover:bg-primary/15 transition-colors' }}">
            <span class="material-symbols-outlined text-[18px]">groups</span>
            <span>متابعة الطلب الجماعي</span>
        </a>
    @elseif(! $activeGroup)
        <a href="{{ auth()->check() ? route('group-orders.create', ['restaurant' => $restaurant->id]) : route('login') }}"
           class="{{ $entryClass ?? 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-stone-200 bg-white text-stone-800 font-extrabold text-xs px-3 py-2 hover:border-primary/40 hover:text-primary transition-colors' }}">
            <span class="material-symbols-outlined text-[18px]">groups</span>
            <span>طلب جماعي</span>
        </a>
    @endif
@endif
