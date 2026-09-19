@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $vip = (bool) ($membership?->discount_percent);
    $deliveryLabel = $vip || $restaurant->type !== 'cafe' ? 'مجاناً' : '5 <span class="ils">₪</span>';
    $itemCount = $menuSections->sum(fn ($section) => $section['items']->count());
    $pointsBalance = $pointsBalance ?? (auth()->user()->points_balance ?? 0);
@endphp

<div class="restaurant-mobile lg:hidden">
<header class="fixed top-0 w-full z-50 pt-safe bg-surface/85 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="h-16 px-margin flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
            <a href="{{ route('restaurants.index') }}" aria-label="رجوع" class="w-11 h-11 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container">
                <span class="material-symbols-outlined text-[24px]">arrow_forward</span>
            </a>
            <img alt="سفرة غزة" class="h-8 w-auto max-w-[8.5rem] object-contain" src="{{ $logoSrc }}">
            <h1 class="font-headline-sm text-[16px] text-on-surface font-bold tracking-tight truncate max-w-[160px]">{{ $restaurant->name }}</h1>
        </div>
        <div class="flex items-center gap-0.5">
            <button type="button" aria-label="مشاركة" class="share-page w-11 h-11 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container">
                <span class="material-symbols-outlined text-[22px]">share</span>
            </button>
            <button type="button" aria-label="المفضلة" class="fav-page w-11 h-11 rounded-full flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container">
                <span class="material-symbols-outlined text-[22px]">favorite_border</span>
            </button>
        </div>
    </div>
</header>

<div class="flex flex-col w-full pt-16 pb-28">
    <div class="relative w-full h-44 overflow-hidden">
        <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-on-surface/80 via-on-surface/20 to-transparent"></div>
        <div class="absolute bottom-3 right-4 left-4 flex items-end justify-between text-white">
            <div class="flex items-center gap-1 min-w-0">
                <span class="material-symbols-outlined text-[18px] text-tertiary-fixed">location_on</span>
                <span class="font-label-sm text-[11px] drop-shadow-sm truncate">{{ $restaurant->address }}</span>
            </div>
            <div class="flex items-center gap-1 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full text-secondary shadow-sm">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                <span class="font-label-sm text-[11px] font-semibold">مفتوح يستقبل الطلبات</span>
            </div>
        </div>
    </div>

    <div class="px-margin -mt-5 relative z-10">
        <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="relative -mt-8 flex-shrink-0">
                    <div class="w-16 h-16 rounded-xl bg-surface-container-lowest p-1 shadow-md overflow-hidden">
                        <img src="{{ $restaurant->coverUrl() }}" alt="" class="w-full h-full object-cover rounded-lg">
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-headline-sm text-[20px] text-on-surface font-bold truncate">{{ $restaurant->name }}</h2>
                    <p class="font-body-sm text-[14px] text-on-surface-variant truncate">{{ $restaurant->description ?: $restaurant->cuisineLabel() }}</p>
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <div class="flex-1 flex items-center gap-1.5 bg-surface-container-low px-2 py-1.5 rounded-lg justify-center">
                    <span class="material-symbols-outlined text-tertiary text-[18px] fill-1">star</span>
                    <div class="flex flex-col leading-none">
                        <span class="font-label-md text-[13px] text-on-surface font-bold">{{ $rating }}</span>
                        <span class="font-label-sm text-[11px] text-on-surface-variant" dir="ltr">({{ number_format($reviews) }}+)</span>
                    </div>
                </div>
                <div class="flex-1 flex items-center gap-1.5 bg-surface-container-low px-2 py-1.5 rounded-lg justify-center">
                    <span class="material-symbols-outlined text-primary text-[18px]">schedule</span>
                    <div class="flex flex-col leading-none">
                        <span class="font-label-md text-[13px] text-on-surface font-bold" dir="ltr">{{ $eta[0] }} - {{ $eta[1] }}</span>
                        <span class="font-label-sm text-[11px] text-on-surface-variant">دقيقة</span>
                    </div>
                </div>
                <div class="flex-1 flex items-center gap-1.5 bg-surface-container-low px-2 py-1.5 rounded-lg justify-center">
                    <span class="material-symbols-outlined text-secondary text-[18px]">moped</span>
                    <div class="flex flex-col leading-none">
                        <span class="font-label-md text-[13px] text-secondary font-bold">{!! $deliveryLabel !!}</span>
                        <span class="font-label-sm text-[11px] text-on-surface-variant">{{ $vip ? 'لأعضاء VIP' : 'توصيل' }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-3 bg-primary-fixed/40 p-2.5 rounded-lg flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="material-symbols-outlined text-primary text-[20px]">local_activity</span>
                    <span class="font-label-sm text-[11px] text-on-primary-fixed font-semibold">خصم {{ $membership->discount_percent ?? 10 }}% فوري | +1 نقطة لكل 10 شواكل</span>
                </div>
                <span class="font-label-sm text-[11px] text-primary font-bold shrink-0">{{ $vip ? 'مُفعل تلقائياً' : 'للأعضاء' }}</span>
            </div>
        </div>
    </div>

    <div class="sticky top-16 z-30 bg-surface/95 backdrop-blur-md pt-3 pb-1 px-margin mt-3 shadow-sm">
        <div class="relative w-full mb-3">
            <input class="menu-search w-full bg-surface-container-lowest rounded-full py-2 pr-10 pl-4 font-body-sm text-[14px] text-on-surface placeholder:text-on-surface-variant focus:outline-none shadow-sm" placeholder="ابحث داخل قائمة {{ $restaurant->name }}..." type="search">
            <span class="material-symbols-outlined text-on-surface-variant absolute right-3.5 top-2.5 text-[20px]">search</span>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <button type="button" class="category-pill is-active px-4 py-1.5 rounded-full font-label-md text-[13px] whitespace-nowrap bg-surface-container text-on-surface" data-filter="all">الكل ({{ $itemCount }})</button>
            @foreach($menuSections as $section)
                <button type="button" class="category-pill px-4 py-1.5 rounded-full font-label-md text-[13px] whitespace-nowrap bg-surface-container text-on-surface" data-filter="{{ $section['key'] }}">{{ $section['name'] }}</button>
            @endforeach
            <button type="button" class="category-pill px-4 py-1.5 rounded-full font-label-md text-[13px] whitespace-nowrap bg-surface-container text-on-surface" data-filter="rewards">استبدال النقاط</button>
        </div>
    </div>

    <div class="px-margin flex flex-col gap-2 mt-3" id="dishList">
        @forelse($menuSections as $section)
            @foreach($section['items'] as $item)
                @include('restaurants.partials.dish-card-mobile', [
                    'item' => $item,
                    'sectionKey' => $section['key'],
                    'popular' => $loop->parent->first && $loop->first,
                ])
            @endforeach
        @empty
            <p class="rounded-xl bg-surface-container-lowest p-8 text-on-surface-variant text-center">لا توجد أصناف في القائمة حالياً.</p>
        @endforelse

        <div class="dish-item rewards bg-gradient-to-b from-tertiary-fixed/30 to-surface-container-low p-4 rounded-xl mt-2" data-name="مكافآت" data-category="rewards">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px] fill-1">stars</span>
                    <h3 class="font-headline-sm text-[18px] text-on-surface font-bold">استبدال النقاط المجانية</h3>
                </div>
                <div class="bg-primary text-on-primary px-2.5 py-1 rounded-full font-label-md text-[13px] font-bold">رصيدك: {{ $pointsBalance }} نقطة</div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @foreach($rewards as $reward)
                    <div class="bg-surface-container-lowest p-2.5 rounded-lg flex flex-col shadow-xs">
                        <div class="w-full h-20 rounded-md overflow-hidden mb-2">
                            <img src="{{ $reward['image'] }}" alt="{{ $reward['name'] }}" class="w-full h-full object-cover">
                        </div>
                        <span class="font-label-md text-[13px] text-on-surface font-bold truncate">{{ $reward['name'] }}</span>
                        <span class="font-label-sm text-[11px] text-secondary font-semibold mt-0.5">مجاناً بـ {{ $reward['points'] }} نقطة</span>
                        <a href="{{ route('redeem.create') }}" class="mt-2 w-full py-1 rounded bg-secondary text-on-secondary font-label-sm text-[11px] font-semibold text-center">استبدال الآن</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

    <div id="mobile-cart-pill" class="fixed bottom-20 inset-x-4 z-40 {{ $restaurantCart ? '' : 'hidden' }}">
        <button type="button" class="js-open-cart w-full bg-inverse-surface text-inverse-on-surface rounded-full px-4 py-3 flex items-center justify-between shadow-2xl">
            <div class="flex items-center gap-3">
                <div class="relative flex items-center justify-center w-10 h-10 rounded-full bg-primary text-on-primary">
                    <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                    <span data-cart-count class="absolute -top-1 -right-1 bg-secondary text-on-secondary text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center">{{ $cartCount }}</span>
                </div>
                <div class="flex flex-col text-right">
                    <div class="flex items-center gap-1">
                        <span data-cart-total class="font-headline-sm text-[20px] font-bold">{{ number_format($restaurantCart['total'] ?? 0, 1) }}</span>
                        <span class="ils">₪</span>
                    </div>
                    <span data-cart-vip class="font-label-sm text-[11px] text-secondary-fixed {{ ($restaurantCart['discount_percent'] ?? 0) ? '' : 'hidden' }}">
                        مشمول خصم الـ VIP ({{ $restaurantCart['discount_percent'] ?? 0 }}%)
                    </span>
                </div>
            </div>
            <span class="flex items-center gap-1.5 bg-primary px-4 py-2 rounded-full text-on-primary">
                <span class="font-label-md text-[13px] font-bold">متابعة الطلب</span>
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            </span>
        </button>
    </div>
</div>
