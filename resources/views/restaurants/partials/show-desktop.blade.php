@php
    $vip = (bool) ($membership?->discount_percent);
@endphp

<div class="restaurant-desktop hidden lg:flex flex-col w-full">
    <section class="relative w-full bg-surface-container-low overflow-hidden pb-6">
        <div class="relative h-64 md:h-80 w-full overflow-hidden">
            <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-surface via-surface/40 to-transparent"></div>
            <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop absolute inset-0 flex items-start justify-between pt-4 pointer-events-none">
                <a class="pointer-events-auto flex items-center gap-1 px-4 py-1.5 rounded-full bg-surface-container-lowest/90 backdrop-blur-md shadow-sm text-on-surface font-label-md text-[13px] hover:bg-surface-container-lowest" href="{{ route('restaurants.index') }}">
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    <span>الرجوع للمطاعم</span>
                </a>
                <div class="pointer-events-auto flex items-center gap-1">
                    <button type="button" class="share-page w-10 h-10 rounded-full bg-surface-container-lowest/90 backdrop-blur-md shadow-sm text-on-surface flex items-center justify-center hover:text-primary" title="مشاركة">
                        <span class="material-symbols-outlined text-[20px]">share</span>
                    </button>
                    <button type="button" class="fav-page w-10 h-10 rounded-full bg-surface-container-lowest/90 backdrop-blur-md shadow-sm text-on-surface flex items-center justify-center hover:text-error" title="إضافة للمفضلة">
                        <span class="material-symbols-outlined text-[20px]">favorite_border</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop -mt-20 relative z-10">
            <div class="bg-surface-container-lowest rounded-xl p-4 md:p-6 shadow-md flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-xl overflow-hidden shrink-0 bg-surface-container shadow-inner">
                        <img class="w-full h-full object-cover" alt="{{ $restaurant->name }}" src="{{ $restaurant->coverUrl() }}">
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="font-headline-md text-[24px] font-bold text-on-surface">{{ $restaurant->name }}</h1>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px]">
                                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                                مفتوح يستقبل الطلبات
                            </span>
                        </div>
                        <p class="font-body-sm text-[14px] text-on-surface-variant flex items-center gap-1 flex-wrap">
                            <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
                            <span>{{ $restaurant->address }}</span>
                            <span class="text-surface-container-highest">•</span>
                            <span>{{ $restaurant->description ?: $restaurant->cuisineLabel() }}</span>
                        </p>
                        <div class="flex flex-wrap items-center gap-4 pt-1">
                            <div class="flex items-center gap-1 bg-surface-container px-3 py-1 rounded-full">
                                <span class="material-symbols-outlined text-[18px] text-tertiary fill-1">star</span>
                                <span class="font-label-md text-[13px] font-bold text-on-surface">{{ $rating }}</span>
                                <span class="font-label-sm text-[11px] text-on-surface-variant">({{ number_format($reviews) }} تقييم معتمد)</span>
                            </div>
                            <div class="flex items-center gap-1 text-on-surface-variant font-label-sm text-[11px]">
                                <span class="material-symbols-outlined text-[18px] text-primary">schedule</span>
                                <span>{{ $eta[0] }} - {{ $eta[1] }} دقيقة</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-auto shrink-0 bg-gradient-to-l from-primary-fixed/60 via-primary-fixed/30 to-surface-container rounded-lg p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[22px]">workspace_premium</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-label-md text-[13px] font-bold text-on-primary-fixed">{{ $vip ? 'أنت مؤهل لخصم '.$membership->discount_percent.'% فوري' : 'اشترك واحصل على خصم 10% فوري' }}</span>
                        </div>
                    </div>
                    @if($vip)
                        <span class="inline-flex px-3 py-1 rounded-full bg-primary text-on-primary font-label-sm text-[11px] font-semibold">عضوية نشطة</span>
                    @else
                        <a href="{{ route('memberships.index') }}" class="inline-flex px-3 py-1 rounded-full bg-primary text-on-primary font-label-sm text-[11px] font-semibold">عضوية ذهبية</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="sticky top-[4.5rem] z-40 bg-surface-container-lowest/95 backdrop-blur-md border-b border-surface-container">
        <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop py-2.5 flex items-center justify-between gap-4 overflow-x-auto no-scrollbar">
            <div class="flex items-center gap-2 shrink-0">
                @foreach($menuSections as $section)
                    <button type="button" class="category-btn px-4 py-2 rounded-full font-label-md text-[13px] font-medium flex items-center gap-1.5 transition-all {{ $loop->first ? 'is-active' : '' }}" data-section="{{ $section['key'] }}">
                        <span class="material-symbols-outlined text-[18px]">{{ $section['icon'] }}</span>
                        <span>{{ $section['name'] }}</span>
                    </button>
                @endforeach
                @if(count($rewards))
                    <button type="button" class="category-btn px-4 py-2 rounded-full font-label-md text-[13px] font-medium flex items-center gap-1.5" data-section="rewards">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">stars</span>
                        <span>استبدال النقاط</span>
                    </button>
                @endif
            </div>
            <div class="hidden md:flex items-center bg-surface-container-low/90 rounded-full px-4 py-2 w-72 border border-surface-container focus-within:border-primary/50">
                <span class="material-symbols-outlined text-on-surface-variant text-[18px] ml-2">search</span>
                <input class="menu-search bg-transparent border-none outline-none text-[14px] text-on-surface w-full placeholder:text-on-surface-variant" placeholder="ابحث داخل قائمة {{ $restaurant->name }}..." type="search">
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop py-6 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 flex flex-col gap-8">
                @forelse($menuSections as $section)
                    <section class="menu-section flex flex-col gap-4 scroll-mt-36" id="section-{{ $section['key'] }}" data-section="{{ $section['key'] }}">
                        <div class="flex items-center justify-between pb-2 border-b border-surface-container">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-primary rounded-full"></span>
                                <h2 class="font-headline-sm text-lg font-bold text-on-surface">{{ $section['name'] }}</h2>
                            </div>
                            <span class="text-xs text-on-surface-variant bg-surface-container-low px-2.5 py-0.5 rounded-full">{{ $section['hint'] }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($section['items'] as $item)
                                @include('restaurants.partials.dish-card-desktop', [
                                    'item' => $item,
                                    'sectionKey' => $section['key'],
                                    'popular' => $loop->parent->first && $loop->first,
                                ])
                            @endforeach
                        </div>
                    </section>
                @empty
                    <p class="rounded-2xl bg-surface-container-lowest border border-stone-100 p-8 text-on-surface-variant">لا توجد أصناف في القائمة حالياً.</p>
                @endforelse

                @if(count($rewards))
                <section class="menu-section flex flex-col gap-3 bg-surface-container-low/70 p-5 rounded-2xl border border-tertiary/20 scroll-mt-36" id="section-rewards" data-section="rewards">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-tertiary text-[20px]">stars</span>
                            <h2 class="font-headline-sm text-base font-bold text-on-surface">استبدال النقاط من قائمة {{ $restaurant->name }}</h2>
                        </div>
                        <span class="text-xs bg-tertiary/10 text-tertiary px-3 py-1 rounded-full font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">verified</span>رصيدك: {{ $pointsBalance }} نقطة
                        </span>
                    </div>
                    <p class="text-xs text-on-surface-variant">هذه الأصناف من منيو هذا المطعم فقط. سعر الاستبدال = سعر الطبق بالنقاط.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        @foreach($rewards as $reward)
                            <div class="bg-surface-container-lowest rounded-xl p-3 border border-slate-200/60 shadow-xs flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img alt="{{ $reward['name'] }}" class="w-14 h-14 rounded-lg object-cover shrink-0" src="{{ $reward['image'] }}">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-semibold text-on-surface truncate">{{ $reward['name'] }}</span>
                                        <span class="text-xs text-tertiary font-medium">مجاناً بـ {{ $reward['points'] }} نقطة</span>
                                    </div>
                                </div>
                                <a href="{{ $reward['url'] ?? route('redeem.create') }}" class="px-3.5 py-1.5 rounded-full bg-tertiary/10 text-tertiary hover:bg-tertiary hover:text-white text-xs font-semibold shrink-0 transition-colors">استبدال</a>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            <aside class="lg:col-span-4 w-full">
                <div class="sticky top-36 flex flex-col gap-3.5">
                    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col">
                        <div class="px-4 py-3 bg-surface-container-low/50 flex items-center justify-between border-b border-surface-container">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-primary">shopping_bag</span>
                                <h2 class="text-sm font-bold text-on-surface">سلة طلبك</h2>
                                <span data-desktop-cart-count class="bg-primary/10 text-primary text-xs px-2 py-0.5 rounded-full font-bold">{{ $restaurantCart ? $cartCount : 0 }}</span>
                            </div>
                            <form method="POST" action="{{ route('cart.clear') }}" data-desktop-cart-clear class="{{ $restaurantCart ? '' : 'hidden' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-error text-xs font-medium flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[15px]">delete_outline</span>
                                    <span>تفريغ</span>
                                </button>
                            </form>
                        </div>

                        <div data-desktop-cart-lines class="p-3.5 flex flex-col gap-2.5 max-h-[320px] overflow-y-auto no-scrollbar">
                            @forelse($restaurantCart['lines'] ?? [] as $line)
                                <div class="pt-2 first:pt-0 flex flex-col gap-1.5 border-t border-slate-100 first:border-0" data-line-id="{{ $line['item']->id }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="text-[13px] font-semibold text-on-surface truncate">{{ $line['item']->name }}</span>
                                        <span class="text-[13px] font-bold text-on-surface shrink-0">{{ number_format($line['line_total'], 0) }} <span class="ils">₪</span></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2 bg-surface-container-low rounded-lg px-2 py-0.5 border border-slate-200/50">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="item_id" value="{{ $line['item']->id }}">
                                            <button type="submit" name="quantity" value="{{ $line['qty'] - 1 }}" class="w-4 h-4 flex items-center justify-center text-slate-500 text-xs font-bold">-</button>
                                            <span class="text-xs font-semibold px-1">{{ $line['qty'] }}</span>
                                            <button type="submit" name="quantity" value="{{ $line['qty'] + 1 }}" class="w-4 h-4 flex items-center justify-center text-slate-500 text-xs font-bold">+</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p data-cart-empty class="text-sm text-on-surface-variant text-center py-8">سلتك فارغة. أضف طبقاً للبدء.</p>
                            @endforelse
                        </div>

                        <div data-desktop-cart-summary class="{{ $restaurantCart ? '' : 'hidden' }}">
                            <div class="p-3.5 bg-surface-container-low/40 flex flex-col gap-2 border-t border-slate-200/60 text-xs">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>المجموع الفرعي</span>
                                    <span data-cart-subtotal class="font-semibold text-on-surface">{{ number_format($restaurantCart['subtotal'] ?? 0, 0) }} <span class="ils">₪</span></span>
                                </div>
                                <div data-cart-discount-row class="flex items-center justify-between text-secondary {{ ($restaurantCart['discount_percent'] ?? 0) ? '' : 'hidden' }}">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">verified</span>خصم VIP (<span data-cart-discount-percent>{{ $restaurantCart['discount_percent'] ?? 0 }}</span>%)
                                    </span>
                                    <span data-cart-discount-amount class="font-semibold">- {{ number_format($restaurantCart['discount_amount'] ?? 0, 1) }} <span class="ils">₪</span></span>
                                </div>
                                <div class="h-px bg-slate-200/60 my-0.5"></div>
                                <div class="flex items-center justify-between pt-0.5">
                                    <span class="text-sm font-bold text-on-surface">المجموع الإجمالي</span>
                                    <span data-cart-grand-total class="text-lg font-bold text-primary">{{ number_format($restaurantCart['items_total'] ?? $restaurantCart['subtotal'] ?? 0, 1) }} <span class="ils">₪</span></span>
                                </div>
                            </div>
                            <div class="p-3.5 pt-1 bg-surface-container-low/40">
                                <a href="{{ auth()->check() ? route('checkout.create') : route('login') }}" class="w-full py-2.5 rounded-xl bg-primary text-white hover:bg-primary-container font-label-md text-[13px] font-semibold shadow-xs flex items-center justify-center gap-2">
                                    <span>متابعة الدفع</span>
                                    <span class="material-symbols-outlined text-[17px]">arrow_back</span>
                                </a>
                                <div class="flex items-center justify-center gap-1 text-slate-400 text-[11px] mt-2.5">
                                    <span class="material-symbols-outlined text-[13px] text-secondary">verified_user</span>
                                    <span>دفع آمن بالاستلام أو إشعار الحوالة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-container-lowest rounded-xl p-3 border border-slate-200/70 shadow-xs flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-secondary-container/70 text-on-secondary-container flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-on-surface">ضمان وصول الوجبة ساخنة</span>
                            <span class="text-[11px] text-slate-400">حقائب حرارية ومندوبين بأعلى معايير الجودة</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
