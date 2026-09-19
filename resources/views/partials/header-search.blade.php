<div class="header-search-wrap {{ $class ?? '' }}" data-suggest-url="{{ route('search.suggest') }}">
    <form action="{{ route('restaurants.index') }}" class="header-search" role="search">
        <input
            name="q"
            value="{{ request('q') }}"
            class="header-search__input"
            placeholder="ابحث عن مطعم أو طبق..."
            type="search"
            autocomplete="off"
            aria-label="بحث"
            aria-autocomplete="list"
        >
        <button type="submit" class="header-search__icon" aria-label="عرض كل النتائج" title="عرض كل النتائج">
            <span class="material-symbols-outlined">search</span>
        </button>
    </form>
    <div class="header-suggest" hidden></div>
</div>
