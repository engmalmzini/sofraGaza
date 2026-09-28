@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : config('brand.logo');
    $vip = (bool) ($membership?->discount_percent);
    $itemCount = $menuSections->sum(fn ($section) => $section['items']->count());
    $pointsBalance = $pointsBalance ?? (auth()->user()->points_balance ?? 0);
@endphp

<div class="restaurant-mobile lg:hidden">
<div class="flex flex-col w-full pt-0 pb-28">
    {{-- 1. Hero Cover Image with Floating Back Button & Status Badge --}}
    <div class="relative w-full h-52 sm:h-60 overflow-hidden bg-stone-900">
        <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/15 to-black/40"></div>

        {{-- Floating Top Bar: Back button on top right, Open/Closed badge on top left (RTL) --}}
        <div class="absolute top-3.5 inset-x-3.5 flex items-center justify-between z-10 pt-safe">
            {{-- زر رجوع (Back button) --}}
            <button type="button" 
                    onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('restaurants.index') }}'; }" 
                    aria-label="رجوع" 
                    class="w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 active:scale-90 text-white backdrop-blur-md flex items-center justify-center shadow-md transition-all cursor-pointer border-0">
                <span class="material-symbols-outlined text-[24px]">arrow_forward</span>
            </button>

            {{-- كلمة مفتوح أو مغلق (Open/Closed Badge) --}}
            @if($restaurant->isOpen())
                <div class="px-3 py-1.5 rounded-full bg-emerald-500/90 backdrop-blur-md text-white font-extrabold text-xs shadow-md flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <span>مفتوح</span>
                </div>
            @else
                <div class="px-3 py-1.5 rounded-full bg-rose-500/90 backdrop-blur-md text-white font-extrabold text-xs shadow-md flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    <span>مغلق</span>
                </div>
            @endif
        </div>
    </div>

    {{-- 2. Restaurant Info Card --}}
    <div class="px-margin -mt-4 relative z-10">
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100">
            <div class="flex items-start gap-3">
                <div class="relative -mt-7 shrink-0">
                    <div class="w-14 h-14 rounded-2xl bg-white p-1 shadow-md overflow-hidden border border-slate-100">
                        <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover rounded-xl">
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    {{-- Restaurant Name (Half size) + Rating Badge (Half size) + Reviews/Dishes Toggle Button --}}
                    <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                        <h1 class="text-xs sm:text-sm font-extrabold text-stone-900 leading-tight truncate">
                            {{ $restaurant->name }}
                        </h1>
                        
                        {{-- Small Rating Badge (Half size) --}}
                        <div class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-amber-50 border border-amber-200/60 text-amber-800 text-[10px] font-bold shrink-0 select-none">
                            <span class="material-symbols-outlined text-amber-500 text-[12px] fill-1">star</span>
                            <span class="font-mono">{{ number_format((float) $rating, 1) }}</span>
                        </div>

                        {{-- Small Reviews Toggle Button (Changes to 'الأطباق' only when viewing reviews) --}}
                        <button type="button" 
                                id="mobile-view-toggle-btn" 
                                onclick="toggleMobileRestaurantView()" 
                                class="inline-flex items-center gap-0.5 text-[11px] font-extrabold text-stone-500 hover:text-amber-600 active:scale-95 transition-all cursor-pointer bg-transparent border-0 p-0 select-none shrink-0" 
                                title="عرض الآراء">
                            <span id="mobile-toggle-icon" class="material-symbols-outlined text-[14px] text-amber-600">rate_review</span>
                            <span id="mobile-toggle-text">الآراء{{ $reviews > 0 ? ' (' . $reviews . ')' : '' }}</span>
                        </button>
                    </div>

                    {{-- Description / Cuisine (Address omitted per user request) --}}
                    @if($restaurant->description || $restaurant->cuisineLabel())
                        <p class="text-[11px] text-stone-400 mt-1 truncate">
                            {{ $restaurant->description ?: $restaurant->cuisineLabel() }}
                        </p>
                    @endif

                    {{-- Delivery ETA --}}
                    <div class="flex items-center gap-1 text-[10px] font-bold text-stone-400 mt-1">
                        <span class="material-symbols-outlined text-stone-400 text-[13px]">schedule</span>
                        <span dir="ltr">{{ $eta[0] }} - {{ $eta[1] }}</span>
                        <span>دقيقة</span>
                    </div>
                </div>
            </div>

            @if($membership?->discount_percent)
                <div class="mt-2.5 bg-primary/10 border border-primary/20 px-2.5 py-1.5 rounded-xl flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="material-symbols-outlined text-primary text-[15px]">local_activity</span>
                        <span class="text-[11px] text-primary font-bold">خصم {{ $membership->discount_percent }}% فوري للأعضاء</span>
                    </div>
                    <span class="text-[10px] text-primary font-extrabold shrink-0">{{ $vip ? 'مُفعل تلقائياً' : 'للأعضاء' }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- 3. Sticky Categories Bar (Shown only when viewing dishes or rewards) --}}
    <div id="mobile-categories-bar" class="sticky top-0 z-30 bg-white/95 backdrop-blur-md pt-2 pb-1 px-margin mt-2 shadow-xs">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <button type="button" class="category-pill is-active px-3.5 py-1.5 rounded-full text-xs font-extrabold whitespace-nowrap bg-primary text-white shadow-xs" data-filter="all">الكل ({{ $itemCount }})</button>
            @foreach($menuSections as $section)
                <button type="button" class="category-pill px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap bg-stone-100 text-stone-700 hover:bg-stone-200 transition-colors" data-filter="{{ $section['key'] }}">{{ $section['name'] }}</button>
            @endforeach
        </div>

        {{-- Sub-bar below categories: Left corner for 'استبدال النقاط' with very small font --}}
        <div class="flex items-center justify-end pt-1 pb-0.5">
            @if(count($rewards))
                <button type="button" 
                        onclick="toggleMobileRewardsView()" 
                        id="mobile-redeem-subbar-btn"
                        class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200/60 px-2 py-0.5 rounded-lg active:scale-95 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[13px] text-amber-600 fill-1">stars</span>
                    <span id="mobile-redeem-subbar-text">استبدال النقاط</span>
                </button>
            @else
                <a href="{{ route('redeem.create') }}" 
                   class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200/60 px-2 py-0.5 rounded-lg active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-[13px] text-amber-600 fill-1">stars</span>
                    <span>استبدال النقاط</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 4. Dishes List (عرض الوجبات) --}}
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
    </div>

    {{-- 5. Points Redemption Section (مخفي افتراضياً ويظهر عند اختيار تبويب استبدال النقاط) --}}
    @if(count($rewards))
    <div id="mobile-rewards-section" class="hidden px-margin mt-3 mb-24 space-y-3">
        {{-- Rewards Header Banner --}}
        <div class="bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-transparent border border-amber-200/80 rounded-2xl p-3.5 flex items-center justify-between gap-2 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px] fill-1 text-amber-600">stars</span>
                </div>
                <div class="min-w-0">
                    <h3 class="font-extrabold text-sm text-stone-900 leading-tight">مكافآت {{ $restaurant->name }}</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">استبدل أطباقك المفضلة مجاناً بنقاطك</p>
                </div>
            </div>
            <div class="shrink-0 bg-white border border-amber-200 px-3 py-1.5 rounded-xl text-center shadow-2xs">
                <span class="text-[10px] text-stone-400 block font-bold">رصيدك</span>
                <span class="text-xs font-black text-amber-700 font-mono">{{ $pointsBalance }} <span class="text-[10px] font-bold">نقطة</span></span>
            </div>
        </div>

        {{-- Rewards Items List (Sleek Horizontal Cards) --}}
        <div class="space-y-2.5">
            @foreach($rewards as $reward)
                @php
                    $canAfford = $pointsBalance >= $reward['points'];
                @endphp
                <div class="bg-white rounded-2xl p-3 border border-stone-200/80 shadow-xs flex items-center justify-between gap-3">
                    {{-- 1. Right: Dish Details & Action Button --}}
                    <div class="flex-1 min-w-0 pr-1">
                        <h4 class="font-extrabold text-sm text-stone-900 truncate">{{ $reward['name'] }}</h4>
                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200/80 px-2 py-0.5 rounded-lg text-xs font-extrabold">
                                <span class="material-symbols-outlined text-amber-500 text-[14px] fill-1">stars</span>
                                <span>{{ $reward['points'] }} نقطة</span>
                            </span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-1.5 py-0.5 rounded-md">مجاناً</span>
                        </div>

                        <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                            <a href="{{ $reward['url'] ?? route('redeem.create') }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl {{ $canAfford ? 'bg-primary text-white hover:bg-primary-container shadow-2xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 border border-stone-200/70' }} font-extrabold text-xs transition-all active:scale-95 shrink-0">
                                <span>استبدال</span>
                                <span class="material-symbols-outlined text-[13px]">arrow_back</span>
                            </a>
                            @if(! $canAfford)
                                <span class="text-[10px] text-stone-400 font-medium">متبقي {{ $reward['points'] - $pointsBalance }} نقطة للاستبدال</span>
                            @else
                                <span class="text-[10px] text-emerald-600 font-bold">جاهز للاستبدال</span>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Left: Dish Image --}}
                    <div class="relative w-22 h-22 rounded-2xl overflow-hidden shrink-0 bg-stone-100 border border-stone-200/70 shadow-2xs">
                        <img src="{{ $reward['image'] }}" alt="{{ $reward['name'] }}" class="w-full h-full object-cover">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 6. Customer Reviews (عرض الآراء والتقييم - مخفي افتراضياً ويظهر عند اختيار تبويب الآراء) --}}
    <div id="mobile-reviews-section" class="hidden px-margin mt-3 mb-24">
        @include('partials.restaurant-reviews')
    </div>
</div>

<script>
function switchMobileRestaurantTab(tab) {
    const categoriesBar = document.getElementById('mobile-categories-bar');
    const dishList = document.getElementById('dishList');
    const rewardsSection = document.getElementById('mobile-rewards-section');
    const reviewsSection = document.getElementById('mobile-reviews-section');

    const toggleBtn = document.getElementById('mobile-view-toggle-btn');
    const toggleIcon = document.getElementById('mobile-toggle-icon');
    const toggleText = document.getElementById('mobile-toggle-text');
    const reviewsCount = '{{ $reviews > 0 ? " (" . $reviews . ")" : "" }}';

    const redeemBtnText = document.getElementById('mobile-redeem-subbar-text');

    // Hide all views
    if (dishList) dishList.classList.add('hidden');
    if (rewardsSection) rewardsSection.classList.add('hidden');
    if (reviewsSection) reviewsSection.classList.add('hidden');

    if (tab === 'reviews') {
        if (categoriesBar) categoriesBar.classList.add('hidden');
        if (reviewsSection) reviewsSection.classList.remove('hidden');

        // Button changes to "الأطباق"
        if (toggleIcon) toggleIcon.textContent = 'restaurant_menu';
        if (toggleText) toggleText.textContent = 'الأطباق';
        if (toggleBtn) {
            toggleBtn.title = 'العودة لقائمة الأطباق';
            toggleBtn.classList.remove('text-stone-500');
            toggleBtn.classList.add('text-primary');
        }
    } else if (tab === 'rewards') {
        if (categoriesBar) categoriesBar.classList.remove('hidden');
        if (rewardsSection) rewardsSection.classList.remove('hidden');

        // Toggle button stays/returns to "الآراء"
        if (toggleIcon) toggleIcon.textContent = 'rate_review';
        if (toggleText) toggleText.textContent = 'الآراء' + reviewsCount;
        if (toggleBtn) {
            toggleBtn.title = 'عرض الآراء';
            toggleBtn.classList.remove('text-primary');
            toggleBtn.classList.add('text-stone-500');
        }

        if (redeemBtnText) redeemBtnText.textContent = 'عرض الأطباق';
    } else {
        // default: dishes
        if (categoriesBar) categoriesBar.classList.remove('hidden');
        if (dishList) dishList.classList.remove('hidden');

        // Toggle button in card shows "الآراء"
        if (toggleIcon) toggleIcon.textContent = 'rate_review';
        if (toggleText) toggleText.textContent = 'الآراء' + reviewsCount;
        if (toggleBtn) {
            toggleBtn.title = 'عرض الآراء';
            toggleBtn.classList.remove('text-primary');
            toggleBtn.classList.add('text-stone-500');
        }

        if (redeemBtnText) redeemBtnText.textContent = 'استبدال النقاط';
    }
}

function toggleMobileRestaurantView() {
    const reviewsSection = document.getElementById('mobile-reviews-section');
    if (reviewsSection && !reviewsSection.classList.contains('hidden')) {
        switchMobileRestaurantTab('dishes');
    } else {
        switchMobileRestaurantTab('reviews');
    }
}

function toggleMobileRewardsView() {
    const rewardsSection = document.getElementById('mobile-rewards-section');
    if (rewardsSection && !rewardsSection.classList.contains('hidden')) {
        switchMobileRestaurantTab('dishes');
    } else {
        switchMobileRestaurantTab('rewards');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#mobile-categories-bar .category-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            const dishList = document.getElementById('dishList');
            if (dishList && dishList.classList.contains('hidden')) {
                switchMobileRestaurantTab('dishes');
            }
        });
    });
});
</script>

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
