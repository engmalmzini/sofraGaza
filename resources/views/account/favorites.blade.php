@extends('layouts.public')

@section('title', 'المفضلة')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 py-6 sm:py-8 pb-24 space-y-6">
    <div class="flex items-end justify-between gap-3 flex-wrap">
        <div>
            <p class="text-xs font-extrabold text-amber-700">وصول سريع</p>
            <h1 class="text-2xl font-black text-stone-900">مفضلتك</h1>
            <p class="text-sm text-stone-500 mt-1">المطاعم والأصناف اللي حفظتهم — بدون ما تدور من الصفر.</p>
        </div>
        <a href="{{ route('restaurants.index') }}" class="text-sm font-bold text-primary hover:underline">تصفح المطاعم</a>
    </div>

    <section class="space-y-3">
        <h2 class="text-base font-extrabold text-stone-900">مطاعم محفوظة</h2>
        @if($favoriteRestaurants->isEmpty())
            <p class="text-sm text-stone-500 bg-white rounded-2xl border border-stone-200 px-4 py-6">ما في مطاعم محفوظة بعد. اضغط القلب على أي مطعم.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($favoriteRestaurants as $restaurant)
                    @include('partials.saved-restaurant-row', ['restaurant' => $restaurant])
                @endforeach
            </div>
        @endif
    </section>

    <section class="space-y-3">
        <h2 class="text-base font-extrabold text-stone-900">أصناف محفوظة</h2>
        @if($favoriteDishes->isEmpty())
            <p class="text-sm text-stone-500 bg-white rounded-2xl border border-stone-200 px-4 py-6">ما في أصناف محفوظة بعد. اضغط القلب بجانب الطبق.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($favoriteDishes as $dish)
                    @include('partials.saved-dish-row', ['dish' => $dish])
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
