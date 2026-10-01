@if(($activeGroupOrder ?? null) && ($canShop ?? true) && ! request()->routeIs('group-orders.*'))
    <div class="mx-auto max-w-2xl lg:max-w-5xl px-3 sm:px-4 pt-3">
        <a href="{{ $groupCheckoutUrl ?? route('group-orders.show', $activeGroupOrder) }}"
           class="flex items-center justify-between gap-3 rounded-2xl border border-primary/25 bg-primary/8 px-3.5 py-2.5 text-primary shadow-xs">
            <div class="flex items-center gap-2 min-w-0">
                <span class="material-symbols-outlined text-[22px] shrink-0">groups</span>
                <div class="min-w-0">
                    <p class="text-xs font-extrabold truncate">طلب جماعي من {{ $activeGroupOrder->restaurant->name }}</p>
                    <p class="text-[11px] font-semibold text-primary/80 truncate">كل واحد يدفع نصيبه — توصيل واحد وفاتورة واحدة</p>
                </div>
            </div>
            <span class="text-[11px] font-extrabold shrink-0">افتح</span>
        </a>
    </div>
@endif
