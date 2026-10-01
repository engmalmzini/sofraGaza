@if(($reorderDishes ?? collect())->isNotEmpty() || ($reorderRestaurants ?? collect())->isNotEmpty())
<section class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full" id="reorder-picks">
    <div class="flex items-end justify-between gap-3 mb-4">
        <div>
            <span class="text-xs font-black text-amber-700 uppercase tracking-wider block mb-1">لك أنت</span>
            <h2 class="text-2xl sm:text-3xl font-black text-stone-900">بناءً على طلباتك السابقة، جرب هذا</h2>
            <p class="text-sm text-stone-500 mt-1 font-semibold">أصناف ومطاعم رجعت تطلبها — ضغطة وبترجع للسلة بدون ما تدور من الصفر.</p>
        </div>
    </div>

    @if($reorderDishes->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
            @foreach($reorderDishes as $dish)
                @include('partials.saved-dish-row', ['dish' => $dish])
            @endforeach
        </div>
    @endif

    @if($reorderRestaurants->isNotEmpty())
        <div class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1">
            @foreach($reorderRestaurants as $place)
                <a href="{{ route('restaurants.show', $place) }}" class="min-w-[220px] max-w-[240px] shrink-0 rounded-2xl bg-white border border-stone-200 p-3 hover:border-amber-300 transition-colors">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $place->coverUrl() }}" alt="" class="w-12 h-12 rounded-xl object-cover">
                        <div class="min-w-0">
                            <strong class="block text-sm font-extrabold text-stone-900 truncate">{{ $place->name }}</strong>
                            <span class="text-[11px] text-stone-500">اطلب منه مرة ثانية</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endif

@if(($homeFavoriteRestaurants ?? collect())->isNotEmpty() || ($homeFavoriteDishes ?? collect())->isNotEmpty())
<section class="py-2 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full" id="home-favorites">
    <div class="flex items-end justify-between gap-3 mb-4">
        <div>
            <span class="text-xs font-black text-rose-600 uppercase tracking-wider block mb-1">مفضلتك</span>
            <h2 class="text-2xl font-black text-stone-900">وصول سريع لما بتحبه</h2>
        </div>
        <a href="{{ route('account.favorites') }}" class="text-xs font-extrabold text-stone-600 hover:text-primary">عرض الكل</a>
    </div>
    @if($homeFavoriteDishes->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
            @foreach($homeFavoriteDishes as $dish)
                @include('partials.saved-dish-row', ['dish' => $dish])
            @endforeach
        </div>
    @endif
    @if($homeFavoriteRestaurants->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($homeFavoriteRestaurants as $restaurant)
                @include('partials.saved-restaurant-row', ['restaurant' => $restaurant])
            @endforeach
        </div>
    @endif
</section>
@endif
