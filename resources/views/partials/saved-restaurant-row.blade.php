<article class="flex items-center gap-3 rounded-2xl bg-white border border-stone-200 p-3 shadow-xs">
    <a href="{{ route('restaurants.show', $restaurant) }}" class="w-16 h-16 rounded-xl overflow-hidden bg-stone-100 shrink-0">
        <img src="{{ $restaurant->coverUrl() }}" alt="{{ $restaurant->name }}" class="w-full h-full object-cover">
    </a>
    <a href="{{ route('restaurants.show', $restaurant) }}" class="min-w-0 flex-1">
        <strong class="block font-extrabold text-stone-900 truncate">{{ $restaurant->name }}</strong>
        <p class="text-xs text-stone-500 truncate">{{ $restaurant->cuisineLabel() }} • {{ $restaurant->areaLabel() }}</p>
    </a>
    @include('partials.favorite-button', [
        'type' => 'restaurant',
        'id' => $restaurant->id,
        'class' => 'js-fav-toggle w-9 h-9 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center shrink-0',
    ])
</article>
