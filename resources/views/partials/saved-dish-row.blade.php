@php
    $restaurant = $dish->restaurant;
    $photo = $dish->imageUrl() ?: $restaurant?->coverUrl();
@endphp
<article class="flex items-center gap-3 rounded-2xl bg-white border border-stone-200 p-3 shadow-xs">
    <a href="{{ $restaurant ? route('restaurants.show', $restaurant).'#dish-'.$dish->id : route('home') }}" class="w-16 h-16 rounded-xl overflow-hidden bg-stone-100 shrink-0">
        @if($photo)
            <img src="{{ $photo }}" alt="{{ $dish->name }}" class="w-full h-full object-cover">
        @endif
    </a>
    <div class="min-w-0 flex-1">
        <a href="{{ $restaurant ? route('restaurants.show', $restaurant).'#dish-'.$dish->id : route('home') }}" class="block font-extrabold text-stone-900 truncate">{{ $dish->name }}</a>
        <p class="text-xs text-stone-500 truncate">{{ $restaurant?->name }}</p>
        <p class="text-sm font-black text-primary mt-0.5">{{ number_format((float) $dish->price, 0) }} <span class="ils">₪</span></p>
    </div>
    <div class="flex flex-col items-center gap-2 shrink-0">
        @include('partials.favorite-button', ['type' => 'menu_item', 'id' => $dish->id, 'class' => 'js-fav-toggle w-9 h-9 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center'])
        @if(($canShop ?? true) && $dish->is_available && $restaurant)
            <form method="POST" action="{{ route('cart.add', $dish) }}">
                @csrf
                <button type="submit" class="px-2.5 py-1 rounded-lg bg-primary text-white text-[11px] font-bold">اطلب</button>
            </form>
        @endif
    </div>
</article>
