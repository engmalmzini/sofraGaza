@extends('layouts.public')

@section('title', 'المطاعم والكافيهات')

@php
    $selectedArea = request('area', $deliveryArea['key'] ?? '');
    $selectedType = request('type');
    $selectedCuisine = request('cuisine');
    $activeFilterCount = collect([$selectedType, $selectedCuisine, request()->filled('area') ? request('area') : null])->filter()->count();
    $chipQuery = array_filter([
        'q' => request('q'),
        'cuisine' => $selectedCuisine,
        'area' => request('area'),
    ], fn ($value) => $value !== null && $value !== '');
@endphp

@section('content')
<div class="mx-auto max-w-7xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <div class="flex flex-col gap-4 mb-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 text-primary text-[12px] font-bold tracking-wider mb-1">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    <span>مختارات قطاع الضيافة المعتمدة</span>
                </div>
                <h1 class="font-headline-md text-2xl lg:text-[28px] font-bold text-stone-900 tracking-tight">المطاعم والكافيهات المختارة</h1>
            </div>
        </div>

        <form class="flex items-center gap-2" action="{{ route('restaurants.index') }}" method="get">
            @if($selectedType)
                <input type="hidden" name="type" value="{{ $selectedType }}">
            @endif
            @if($selectedCuisine)
                <input type="hidden" name="cuisine" value="{{ $selectedCuisine }}">
            @endif
            @if(request()->filled('area'))
                <input type="hidden" name="area" value="{{ request('area') }}">
            @endif
            <div class="relative flex-1 flex items-center bg-surface-container-lowest rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.03)]">
                <span class="material-symbols-outlined text-primary text-[20px] absolute right-3.5 pointer-events-none">search</span>
                <input name="q" value="{{ request('q') }}" class="w-full h-11 bg-transparent pr-10 pl-4 text-on-surface font-body-sm placeholder:text-on-surface-variant/60 focus:outline-none" placeholder="ابحث عن مطعم، طبق، أو كافيه...">
            </div>
            <button type="button" class="sg-filter-btn{{ $activeFilterCount ? ' is-active' : '' }}" data-filter-open aria-haspopup="dialog" aria-controls="listing-filter" aria-label="تصفية النتائج">
                <span class="material-symbols-outlined text-[20px]">tune</span>
                @if($activeFilterCount)
                    <span class="sg-filter-btn__count">{{ $activeFilterCount }}</span>
                @endif
            </button>
        </form>

        <div class="flex gap-1.5 overflow-x-auto scrollbar-none">
            <a href="{{ route('restaurants.index', $chipQuery) }}" class="px-3.5 py-1.5 rounded-full text-label-sm font-label-sm whitespace-nowrap {{ !$selectedType ? 'bg-primary text-on-primary font-bold shadow-sm' : 'bg-surface-container-lowest text-on-surface-variant shadow-[0_1px_4px_rgba(0,0,0,0.03)]' }}">الكل</a>
            <a href="{{ route('restaurants.index', array_merge($chipQuery, ['type' => 'restaurant'])) }}" class="px-3.5 py-1.5 rounded-full text-label-sm font-label-sm whitespace-nowrap {{ $selectedType === 'restaurant' ? 'bg-primary text-on-primary font-bold shadow-sm' : 'bg-surface-container-lowest text-on-surface-variant shadow-[0_1px_4px_rgba(0,0,0,0.03)]' }}">مطاعم</a>
            <a href="{{ route('restaurants.index', array_merge($chipQuery, ['type' => 'cafe'])) }}" class="px-3.5 py-1.5 rounded-full text-label-sm font-label-sm whitespace-nowrap {{ $selectedType === 'cafe' ? 'bg-primary text-on-primary font-bold shadow-sm' : 'bg-surface-container-lowest text-on-surface-variant shadow-[0_1px_4px_rgba(0,0,0,0.03)]' }}">كافيهات</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
        @forelse($restaurants as $restaurant)
            @include('partials.restaurant-card', ['restaurant' => $restaurant])
        @empty
            <p class="col-span-full rounded-2xl bg-surface-container-lowest border border-stone-100 p-8 text-on-surface-variant">لا توجد نتائج مطابقة لبحثك.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $restaurants->links() }}</div>
</div>

<div class="sg-filter-overlay" id="listing-filter" data-filter-overlay hidden>
    <div class="sg-filter-dialog" role="dialog" aria-modal="true" aria-labelledby="listing-filter-title">
        <div class="sg-filter-dialog__head">
            <div>
                <p class="sg-filter-dialog__kicker">نتائج أدق</p>
                <h2 id="listing-filter-title">تصفية الأماكن</h2>
            </div>
            <button type="button" class="sg-filter-dialog__close" data-filter-close aria-label="إغلاق">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form class="sg-filter-dialog__form" action="{{ route('restaurants.index') }}" method="get">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <div class="sg-filter-dialog__body">
            <section class="sg-filter-dialog__section">
                <h3>نوع المكان</h3>
                <div class="sg-filter-dialog__chips">
                    <label class="sg-filter-chip">
                        <input type="radio" name="type" value="" @checked(!$selectedType)>
                        <span>الكل</span>
                    </label>
                    <label class="sg-filter-chip">
                        <input type="radio" name="type" value="restaurant" @checked($selectedType === 'restaurant')>
                        <span>مطاعم</span>
                    </label>
                    <label class="sg-filter-chip">
                        <input type="radio" name="type" value="cafe" @checked($selectedType === 'cafe')>
                        <span>كافيهات</span>
                    </label>
                </div>
            </section>

            <section class="sg-filter-dialog__section">
                <h3>المنطقة</h3>
                <div class="sg-filter-dialog__chips">
                    <label class="sg-filter-chip">
                        <input type="radio" name="area" value="" @checked(!request()->filled('area'))>
                        <span>كل غزة</span>
                    </label>
                    @foreach(config('brand.areas', []) as $area)
                        @php
                            $short = str_contains($area['label'], '•') ? trim(explode('•', $area['label'])[1]) : $area['label'];
                        @endphp
                        <label class="sg-filter-chip">
                            <input type="radio" name="area" value="{{ $area['key'] }}" @checked(request('area') === $area['key'])>
                            <span>{{ $short }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="sg-filter-dialog__section">
                <h3>نوع الوجبة</h3>
                <div class="sg-filter-dialog__chips">
                    <label class="sg-filter-chip">
                        <input type="radio" name="cuisine" value="" @checked(!$selectedCuisine)>
                        <span>الكل</span>
                    </label>
                    @foreach(config('brand.cuisines', []) as $cuisineKey => $cuisineLabel)
                        <label class="sg-filter-chip">
                            <input type="radio" name="cuisine" value="{{ $cuisineKey }}" @checked($selectedCuisine === $cuisineKey)>
                            <span>{{ $cuisineLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
            </div>

            <div class="sg-filter-dialog__actions">
                <a class="sg-filter-dialog__ghost" href="{{ route('restaurants.index', array_filter(['q' => request('q')])) }}">مسح</a>
                <button type="submit" class="sg-filter-dialog__apply">تطبيق</button>
            </div>
        </form>
    </div>
</div>
@endsection
