@extends('layouts.public')

@section('title', 'المطاعم والكافيهات')
@section('body_class', 'sg-listing-page')

@php
    $selectedType = request('type');
    $selectedCuisine = request('cuisine');
    $selectedSort = request('sort', 'popular');
    $activeArea = request()->exists('area') ? request('area') : ($deliveryArea['key'] ?? null);
    $cuisines = config('brand.cuisines', []);
    $areas = config('brand.areas', []);
    $pageTitle = ($selectedCuisine && isset($cuisines[$selectedCuisine]))
        ? $cuisines[$selectedCuisine]
        : 'كل المطاعم';
    $total = $restaurants->total();
    $placesCount = match (true) {
        $total === 0 => 'لا أماكن',
        $total === 1 => 'مكان واحد',
        $total === 2 => 'مكانان',
        $total >= 3 && $total <= 10 => $total.' أماكن',
        default => $total.' مكاناً',
    };
    $placesLead = match (true) {
        $total === 0 => 'لا أماكن جاهزة للتوصيل في غزة حالياً',
        $total === 1 => 'مكان واحد جاهز للتوصيل في غزة',
        $total === 2 => 'مكانان جاهزان للتوصيل في غزة',
        $total >= 3 && $total <= 10 => $total.' أماكن جاهزة للتوصيل في غزة',
        default => $total.' مكاناً جاهزاً للتوصيل في غزة',
    };
    $activeFilterCount = collect([$selectedType, $selectedCuisine, request()->filled('area') ? request('area') : null])->filter()->count();

    $filterUrl = function (array $overrides = []) {
        $query = [
            'q' => request('q'),
            'type' => request('type'),
            'cuisine' => request('cuisine'),
            'sort' => request('sort'),
        ];
        if (request()->exists('area')) {
            $query['area'] = request('area');
        }
        $query = array_merge($query, $overrides);
        $keepAllAreas = (array_key_exists('area', $overrides) && $overrides['area'] === '')
            || (! array_key_exists('area', $overrides) && request()->exists('area') && request('area') === '');
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');
        if ($keepAllAreas) {
            $query['area'] = '';
        }

        return route('restaurants.index', $query);
    };
@endphp

@section('content')
<style>
    body.sg-listing-page,
    body.sg-listing-page .site-main {
        background: #FAF6F0 !important;
    }
    .sg-listing {
        max-width: 80rem;
        margin: 0 auto;
        padding: 1.25rem 1.5rem 3rem;
    }
    .sg-listing__crumb {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #9a8b80;
        margin-bottom: 0.85rem;
    }
    .sg-listing__crumb a { color: #9a8b80; text-decoration: none; }
    .sg-listing__crumb a:hover { color: #a33900; }
    .sg-listing__crumb span.is-current { color: #1a130f; }
    .sg-listing__hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }
    .sg-listing__title {
        margin: 0;
        font-size: 2.15rem;
        font-weight: 900;
        color: #1a130f;
        letter-spacing: -0.03em;
        line-height: 1.15;
    }
    .sg-listing__lead {
        margin: 0.35rem 0 0;
        font-size: 0.88rem;
        font-weight: 600;
        color: #8a7a6e;
    }
    .sg-listing__search {
        display: flex;
        align-items: center;
        background: #ffffff;
        border-radius: 9999px;
        padding: 5px 5px 5px 6px;
        box-shadow: 0 6px 18px rgba(80, 40, 10, 0.06);
        min-width: min(100%, 380px);
        flex: 1;
        max-width: 440px;
    }
    .sg-listing__search .material-symbols-outlined {
        color: #b0a398;
        font-size: 20px;
        margin-inline: 10px 4px;
    }
    .sg-listing__search input {
        flex: 1;
        border: 0;
        outline: none;
        background: transparent;
        height: 38px;
        font-size: 0.86rem;
        font-weight: 600;
        color: #1a130f;
    }
    .sg-listing__search input::placeholder { color: #b0a398; }
    .sg-listing__search button {
        background: #c84500;
        color: #fff;
        border: 0;
        border-radius: 9999px;
        padding: 0.55rem 1.2rem;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
    }
    .sg-listing__search button:hover { background: #b03d00; }
    .sg-listing__layout {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        gap: 1.5rem;
        align-items: start;
    }
    .sg-listing__filters {
        background: #ffffff;
        border-radius: 22px;
        padding: 1.15rem 1.15rem 1.35rem;
        box-shadow: 0 4px 16px rgba(80, 40, 10, 0.04);
        position: sticky;
        top: 1rem;
    }
    .sg-listing__filters-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #f0e7dc;
    }
    .sg-listing__filters-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #1a130f;
    }
    .sg-listing__clear {
        font-size: 0.75rem;
        font-weight: 700;
        color: #9a8b80;
        text-decoration: none;
    }
    .sg-listing__clear:hover { color: #a33900; }
    .sg-listing__group { margin-top: 1.15rem; }
    .sg-listing__group h3 {
        margin: 0 0 0.7rem;
        font-size: 0.78rem;
        font-weight: 800;
        color: #1a130f;
    }
    .sg-listing__checks {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }
    .sg-listing__check {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #5c5148;
        text-decoration: none;
        border-radius: 8px;
        padding: 0.15rem 0;
    }
    .sg-listing__check:hover { color: #a33900; }
    .sg-listing__box {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 1.5px solid #d7cbbf;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #fff;
    }
    .sg-listing__check.is-on { color: #1a130f; }
    .sg-listing__check.is-on .sg-listing__box {
        background: #c84500;
        border-color: #c84500;
        color: #fff;
    }
    .sg-listing__check.is-on .sg-listing__box::after {
        content: '';
        width: 4px;
        height: 7px;
        border: 2px solid #fff;
        border-top: 0;
        border-left: 0;
        transform: rotate(45deg) translateY(-1px);
    }
    .sg-listing__pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .sg-listing__pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.38rem 0.85rem;
        border-radius: 9999px;
        background: #fff;
        border: 1px solid #eadfd3;
        color: #5c5148;
        font-size: 0.75rem;
        font-weight: 800;
        text-decoration: none;
    }
    .sg-listing__pill.is-on {
        background: #c84500;
        border-color: #c84500;
        color: #fff;
    }
    .sg-listing__toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .sg-listing__count {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #1a130f;
    }
    .sg-listing__sort {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        background: #fff;
        border-radius: 9999px;
        padding: 0.15rem 0.85rem 0.15rem 0.35rem;
        box-shadow: 0 2px 10px rgba(80, 40, 10, 0.05);
        font-size: 0.78rem;
        font-weight: 700;
        color: #5c5148;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .sg-listing__sort select {
        border: 0;
        background: transparent;
        font-weight: 800;
        color: #1a130f;
        padding: 0.45rem 0.2rem;
        outline: none;
        cursor: pointer;
    }
    .sg-listing__grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .sg-listing__empty {
        grid-column: 1 / -1;
        background: #fff;
        border-radius: 20px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #8a7a6e;
        font-weight: 700;
    }
    .sg-listing__mobile-bar {
        display: none;
        gap: 0.6rem;
        margin-bottom: 1rem;
    }

    /* Foodly-style restaurant cards */
    .sg-dish-card {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 18px;
        padding: 0.7rem 0.7rem 0.85rem;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 4px 14px rgba(80, 40, 10, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .sg-dish-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(80, 40, 10, 0.1);
    }
    .sg-dish-card__media {
        position: relative;
        aspect-ratio: 1 / 0.92;
        border-radius: 14px;
        overflow: hidden;
        background: #f6f1ea;
        margin-bottom: 0.7rem;
    }
    .sg-dish-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .sg-dish-card:hover .sg-dish-card__media img { transform: scale(1.04); }
    .sg-dish-card__badge {
        position: absolute;
        top: 8px;
        inset-inline-start: 8px;
        background: #1a130f;
        color: #fff;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        padding: 0.22rem 0.5rem;
        border-radius: 8px;
        z-index: 2;
    }
    .sg-dish-card__badge--ad { background: #ea580c; }
    .sg-dish-card--ad { outline: 2px solid #fdba74; }
    .sg-dish-card__fav {
        position: absolute;
        top: 8px;
        inset-inline-end: 8px;
        width: 28px;
        height: 28px;
        border: 0;
        background: transparent;
        color: #c4b8ae;
        cursor: pointer;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .sg-dish-card__fav .material-symbols-outlined { font-size: 20px; }
    .sg-dish-card__fav.is-on,
    .sg-dish-card__fav.is-on .material-symbols-outlined {
        color: #e11d48;
        font-variation-settings: 'FILL' 1;
    }
    .sg-dish-card__title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: #1a130f;
        line-height: 1.3;
    }
    .sg-dish-card__sub {
        margin: 0.2rem 0 0.45rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #9a8b80;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sg-dish-card__meta {
        display: flex;
        align-items: center;
        gap: 0.28rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #8a7a6e;
        margin-bottom: 0.65rem;
    }
    .sg-dish-card__star { width: 12px; height: 12px; color: #d65e15; flex-shrink: 0; }
    .sg-dish-card__rating { color: #1a130f; font-weight: 800; }
    .sg-dish-card__reviews { color: #b0a398; }
    .sg-dish-card__dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #d7cbbf;
        margin: 0 0.15rem;
    }
    .sg-dish-card__clock { font-size: 13px !important; color: #b0a398; }
    .sg-dish-card__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
    }
    .sg-dish-card__price {
        font-size: 1.05rem;
        font-weight: 900;
        color: #1a130f;
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
    }
    .sg-dish-card__plus {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #c84500;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 6px 12px rgba(200, 69, 0, 0.28);
    }

    @media (max-width: 1199px) {
        .sg-listing__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 1023px) {
        .sg-listing { padding: 1rem 1rem 2rem; }
        .sg-listing__layout { grid-template-columns: 1fr; }
        .sg-listing__filters { display: none; }
        .sg-listing__mobile-bar { display: flex; }
        .sg-listing__title { font-size: 1.65rem; }
        .sg-listing__search { max-width: none; min-width: 0; width: 100%; }
        .sg-listing__hero { align-items: stretch; }
        .sg-listing__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 639px) {
        .sg-listing__grid { grid-template-columns: 1fr; }
    }
</style>

<div class="sg-listing">
    <nav class="sg-listing__crumb hidden lg:flex" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <span>/</span>
        <span class="is-current">{{ $pageTitle }}</span>
    </nav>

    <div class="sg-listing__hero">
        <div>
            <h1 class="sg-listing__title">{{ $pageTitle }}</h1>
            <p class="sg-listing__lead">{{ $placesLead }}</p>
        </div>
        <form class="sg-listing__search" action="{{ route('restaurants.index') }}" method="get">
            @if($selectedType)<input type="hidden" name="type" value="{{ $selectedType }}">@endif
            @if($selectedCuisine)<input type="hidden" name="cuisine" value="{{ $selectedCuisine }}">@endif
            @if(request()->exists('area'))<input type="hidden" name="area" value="{{ request('area') }}">@endif
            @if($selectedSort && $selectedSort !== 'popular')<input type="hidden" name="sort" value="{{ $selectedSort }}">@endif
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <input name="q" value="{{ request('q') }}" placeholder="ابحث عن مطعم، شاورما، بيتزا...">
            <button type="submit">بحث</button>
        </form>
    </div>

    <div class="sg-listing__mobile-bar">
        <button type="button" class="sg-filter-btn{{ $activeFilterCount ? ' is-active' : '' }}" data-filter-open aria-haspopup="dialog" aria-controls="listing-filter" aria-label="تصفية النتائج">
            <span class="material-symbols-outlined text-[20px]">tune</span>
            @if($activeFilterCount)
                <span class="sg-filter-btn__count">{{ $activeFilterCount }}</span>
            @endif
        </button>
    </div>

    <div class="sg-listing__layout">
        <aside class="sg-listing__filters" aria-label="تصفية النتائج">
            <div class="sg-listing__filters-head">
                <h2>تصفية</h2>
                <a class="sg-listing__clear" href="{{ route('restaurants.index', array_filter(['q' => request('q')])) }}">مسح الكل</a>
            </div>

            <div class="sg-listing__group">
                <h3>التصنيف</h3>
                <div class="sg-listing__checks">
                    <a class="sg-listing__check{{ !$selectedCuisine ? ' is-on' : '' }}" href="{{ $filterUrl(['cuisine' => null]) }}">
                        <span class="sg-listing__box"></span>
                        <span>الكل</span>
                    </a>
                    @foreach($cuisines as $cuisineKey => $cuisineLabel)
                        <a class="sg-listing__check{{ $selectedCuisine === $cuisineKey ? ' is-on' : '' }}" href="{{ $filterUrl(['cuisine' => $cuisineKey]) }}">
                            <span class="sg-listing__box"></span>
                            <span>{{ $cuisineLabel }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="sg-listing__group">
                <h3>نوع المكان</h3>
                <div class="sg-listing__pills">
                    <a class="sg-listing__pill{{ !$selectedType ? ' is-on' : '' }}" href="{{ $filterUrl(['type' => null]) }}">الكل</a>
                    <a class="sg-listing__pill{{ $selectedType === 'restaurant' ? ' is-on' : '' }}" href="{{ $filterUrl(['type' => 'restaurant']) }}">مطعم</a>
                    <a class="sg-listing__pill{{ $selectedType === 'cafe' ? ' is-on' : '' }}" href="{{ $filterUrl(['type' => 'cafe']) }}">كافيه</a>
                </div>
            </div>

            <div class="sg-listing__group">
                <h3>المنطقة</h3>
                <div class="sg-listing__checks">
                    <a class="sg-listing__check{{ $activeArea === '' ? ' is-on' : '' }}" href="{{ $filterUrl(['area' => '']) }}">
                        <span class="sg-listing__box"></span>
                        <span>كل غزة</span>
                    </a>
                    @foreach($areas as $area)
                        @php
                            $short = str_contains($area['label'], '•') ? trim(explode('•', $area['label'])[1]) : $area['label'];
                        @endphp
                        <a class="sg-listing__check{{ $activeArea === $area['key'] ? ' is-on' : '' }}" href="{{ $filterUrl(['area' => $area['key']]) }}">
                            <span class="sg-listing__box"></span>
                            <span>{{ $short }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <div>
            <div class="sg-listing__toolbar">
                <p class="sg-listing__count">{{ $placesCount }}</p>
                <form class="sg-listing__sort" action="{{ route('restaurants.index') }}" method="get">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    @if($selectedType)<input type="hidden" name="type" value="{{ $selectedType }}">@endif
                    @if($selectedCuisine)<input type="hidden" name="cuisine" value="{{ $selectedCuisine }}">@endif
                    @if(request()->exists('area'))<input type="hidden" name="area" value="{{ request('area') }}">@endif
                    <span>ترتيب حسب:</span>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="popular" @selected($selectedSort === 'popular' || $selectedSort === '')>الأشهر</option>
                        <option value="rating" @selected($selectedSort === 'rating')>التقييم</option>
                        <option value="newest" @selected($selectedSort === 'newest')>الأحدث</option>
                    </select>
                </form>
            </div>

            <div class="sg-listing__grid">
                @forelse($restaurants as $restaurant)
                    @include('partials.restaurant-card', ['restaurant' => $restaurant])
                @empty
                    <p class="sg-listing__empty">لا توجد نتائج مطابقة لبحثك في غزة.</p>
                @endforelse
            </div>
            <div class="mt-8">{{ $restaurants->links() }}</div>
        </div>
    </div>
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
            @if($selectedSort && $selectedSort !== 'popular')
                <input type="hidden" name="sort" value="{{ $selectedSort }}">
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
                        <input type="radio" name="area" value="" @checked(request()->exists('area') && request('area') === '')>
                        <span>كل غزة</span>
                    </label>
                    @foreach($areas as $area)
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
                    @foreach($cuisines as $cuisineKey => $cuisineLabel)
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
