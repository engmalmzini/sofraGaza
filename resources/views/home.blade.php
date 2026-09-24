@extends('layouts.public')

@section('title', 'الرئيسية')

@section('content')
<div class="lg:hidden">
    @include('partials.home-mobile')
</div>
<div class="hidden lg:flex flex-col w-full">
    <section class="relative w-full overflow-hidden bg-gradient-to-b from-stone-50 via-surface to-surface border-b border-stone-200/50 py-12 lg:py-20">
        <div class="absolute -top-40 right-1/4 w-[500px] h-[500px] rounded-full bg-orange-100/40 blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -left-32 w-[380px] h-[380px] rounded-full bg-emerald-50/50 blur-3xl pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                <div class="lg:col-span-7 flex flex-col items-start gap-6 animate-fade-in-up">
                    <div class="sg-hero-kicker">
                        <span class="sg-hero-kicker__mark" aria-hidden="true">
                            <span class="material-symbols-outlined">verified</span>
                        </span>
                        <span class="sg-hero-kicker__copy">
                            <strong>المنصة المعتمدة</strong>
                            <em>للضيافة والمطاعم في قطاع غزة</em>
                        </span>
                    </div>
                    <div class="flex flex-col gap-3">
                        <h1 class="font-headline-xl text-3xl sm:text-4xl lg:text-[44px] lg:leading-[54px] font-bold text-stone-900 tracking-tight">
                            نكهات غزة الأصيلة،
                            <span class="text-primary font-bold block mt-1">تصلك بعناية كما تحب.</span>
                        </h1>
                        <p class="font-body-md text-stone-600 text-base sm:text-lg max-w-xl leading-relaxed">
                            كل احتياجاتك في غزة .. في مكان واحد. استكشف المطاعم والكافيهات، اطلب بسهولة، واكسب نقاطاً مع كل وجبة.
                        </p>
                    </div>
                    <form action="{{ route('restaurants.index') }}" class="w-full max-w-xl bg-surface-container-lowest rounded-2xl p-2 sm:p-2.5 shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-stone-200/80 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="flex-1 flex items-center gap-2.5 px-3 py-2 rounded-xl bg-stone-50/70">
                            <span class="material-symbols-outlined text-primary text-[20px]">location_on</span>
                            <div class="flex flex-col text-right flex-1">
                                <span class="text-[10px] text-stone-400 font-medium">المنطقة</span>
                                <select name="area" class="bg-transparent text-stone-800 font-label-md text-[13px] font-semibold outline-none border-none p-0 cursor-pointer w-full">
                                    @foreach(config('brand.areas', []) as $area)
                                        @php
                                            $short = str_contains($area['label'], '•') ? trim(explode('•', $area['label'])[1]) : $area['label'];
                                        @endphp
                                        <option value="{{ $area['key'] }}" @selected(($deliveryArea['key'] ?? '') === $area['key'])>{{ $short }} - غزة</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="hidden sm:block w-[1px] h-8 bg-stone-200"></div>
                        <div class="flex-1 flex items-center gap-2.5 px-3 py-2 rounded-xl bg-stone-50/70">
                            <span class="material-symbols-outlined text-stone-500 text-[20px]">restaurant</span>
                            <div class="flex flex-col text-right flex-1">
                                <span class="text-[10px] text-stone-400 font-medium">نوع الوجبة</span>
                                <select name="cuisine" class="bg-transparent text-stone-800 font-label-md text-[13px] font-semibold outline-none border-none p-0 cursor-pointer w-full">
                                    <option value="">جميع الأطباق</option>
                                    @foreach(config('brand.cuisines', []) as $cuisineKey => $cuisineLabel)
                                        <option value="{{ $cuisineKey }}">{{ $cuisineLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-[14px] font-semibold shadow-sm transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] shrink-0">
                            <span>استكشف</span>
                            <span class="material-symbols-outlined text-[18px]">search</span>
                        </button>
                    </form>
                    <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-stone-600 font-label-sm text-[13px] pt-1">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">storefront</span>
                            <span class="font-medium"><strong class="font-bold text-stone-900">{{ $restaurantCount }}</strong> مطعم وكافيه مختار</span>
                        </div>
                        <span class="text-stone-300">•</span>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-secondary text-[18px]">moped</span>
                            <span class="font-medium">متوسط توصيل <strong class="font-bold text-stone-900">25 دقيقة</strong></span>
                        </div>
                        <span class="text-stone-300">•</span>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-amber-600 text-[18px]">stars</span>
                            <span class="font-medium">نقاط ولاء ومكافآت فورية</span>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-5 relative flex justify-center items-center animate-fade-in-up-delay-1">
                    <div class="sg-brand-hero">
                        <img src="{{ asset('images/brand-mark.jpg') }}" alt="شعار سفرة غزة — كل احتياجاتك في غزة في مكان واحد">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="w-full bg-surface-container-low py-space-lg shadow-sm">
        <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop">
            <div class="flex items-center justify-between mb-space-md">
                <div>
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">التصنيفات السريعة للوجبات</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">اختر صنفك المفضل وتصفح أفضل الطهاة في منطقتك</p>
                </div>
                <div class="flex items-center gap-space-xs">
                    <button aria-label="السابق" class="w-9 h-9 rounded-full bg-surface-container-lowest flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-colors shadow-sm" id="cat-prev-btn" type="button">
                        <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                    </button>
                    <button aria-label="التالي" class="w-9 h-9 rounded-full bg-surface-container-lowest flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-colors shadow-sm" id="cat-next-btn" type="button">
                        <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-space-md overflow-x-auto no-scrollbar scroll-smooth pb-space-xs" id="categories-scroll">
                @foreach($categories as $category)
                    <a href="{{ route('restaurants.index', ['cuisine' => $category['key'], 'area' => '']) }}" class="group flex flex-col items-center gap-space-xs shrink-0 p-space-sm rounded-xl bg-surface-container-lowest hover:bg-primary-fixed/30 transition-all w-28 text-center shadow-sm">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-surface-container-high flex items-center justify-center">
                            <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="{{ $category['name'] }}" src="{{ $category['image'] }}">
                        </div>
                        <span class="font-label-md text-label-md font-bold text-on-surface group-hover:text-primary">{{ $category['name'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $category['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="w-full max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop pt-8 pb-2">
        <div class="sg-browse" data-home-browse>
            <div class="sg-browse__row">
                <span class="sg-browse__label">المنطقة</span>
                <div class="sg-browse__chips" id="area-filters">
                    <button class="sg-browse__chip is-active" data-area="" type="button">الكل</button>
                    @foreach(config('brand.areas', []) as $area)
                        @php
                            $short = str_contains($area['label'], '•') ? trim(explode('•', $area['label'])[1]) : $area['label'];
                        @endphp
                        <button class="sg-browse__chip" data-area="{{ $area['key'] }}" type="button">{{ $short }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="w-full max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop py-space-md" id="restaurants-grid">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <div class="inline-flex items-center gap-1.5 text-primary text-[12px] font-bold tracking-wider mb-1.5">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    <span>مختارات قطاع الضيافة المعتمدة</span>
                </div>
                <h2 class="font-headline-md text-2xl lg:text-[28px] font-bold text-stone-900 tracking-tight">أفضل المطاعم والكافيهات المختارة</h2>
                <p class="font-body-sm text-stone-500 text-sm mt-0.5"><span id="visible-count">{{ $restaurants->count() }}</span> مكان جاهز للتوصيل</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <div class="sg-browse__types" role="tablist" aria-label="نوع المكان">
                    <button class="sg-browse__chip is-active" data-type="" type="button">الكل</button>
                    <button class="sg-browse__chip" data-type="restaurant" type="button">مطاعم</button>
                    <button class="sg-browse__chip" data-type="cafe" type="button">كافيهات</button>
                </div>
                <a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-surface-container-lowest hover:bg-stone-100 border border-stone-200 text-stone-800 text-[13px] font-semibold shadow-xs" href="{{ route('restaurants.index') }}">
                    <span>عرض الكل</span>
                    <span class="material-symbols-outlined text-[16px] text-stone-500">arrow_back</span>
                </a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
            @forelse($restaurants as $restaurant)
                @php
                    $rating = number_format(4.6 + ($restaurant->id % 4) * 0.1, 1);
                    $eta = $restaurant->type === 'cafe' ? '20-30 دقيقة' : '25-35 دقيقة';
                @endphp
                <a href="{{ route('restaurants.show', $restaurant) }}" class="place-card group rounded-2xl border border-slate-100 bg-surface-container-lowest overflow-hidden shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col" data-area="{{ $restaurant->area }}" data-type="{{ $restaurant->type }}">
                    <div class="relative h-44 sm:h-48 w-full overflow-hidden bg-stone-100">
                        <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $restaurant->name }}" src="{{ $restaurant->coverUrl() }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent pointer-events-none"></div>
                        <span class="absolute top-3 left-3 w-8 h-8 rounded-full bg-white/90 text-stone-700 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                        </span>
                        <div class="absolute bottom-3 right-3">
                            <span class="px-2.5 py-1 rounded-lg bg-stone-900/85 backdrop-blur-sm text-white text-[11px] font-medium">{{ $restaurant->badgeLabel() }}</span>
                        </div>
                    </div>
                    <div class="p-3.5 sm:p-4 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1">
                                <h3 class="font-headline-sm text-base font-bold text-stone-900 group-hover:text-primary transition-colors">{{ $restaurant->name }}</h3>
                                <span class="material-symbols-outlined text-primary text-[17px]">verified</span>
                            </div>
                            <div class="flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/50 text-amber-900 text-xs font-bold shrink-0">
                                <span class="material-symbols-outlined text-[13px] text-amber-500">star</span>
                                <span>{{ $rating }}</span>
                            </div>
                        </div>
                        <p class="text-stone-500 text-[13px] font-normal">{{ $restaurant->cuisineLabel() }} • {{ $restaurant->address }}</p>
                        <div class="flex items-center gap-2 text-stone-600 text-[12px] pt-1">
                            <div class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-stone-400">schedule</span>
                                <span>{{ $eta }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <p class="col-span-full rounded-2xl bg-white p-8 text-stone-500">لا توجد مطاعم ظاهرة حالياً.</p>
            @endforelse
        </div>
    </section>

    <section class="w-full bg-stone-50/70 border-y border-stone-200/60 py-10 my-space-lg relative">
        <div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="inline-flex items-center gap-1.5 text-amber-700 text-[12px] font-bold tracking-wider mb-1">
                        <span class="material-symbols-outlined text-[16px]">stars</span>
                        <span>مكافآت برنامج الولاء</span>
                    </div>
                    <h2 class="font-headline-md text-xl sm:text-2xl font-bold text-stone-900 tracking-tight">أطباق بمكافآت النقاط</h2>
                    <p class="font-body-sm text-stone-500 text-[13px] mt-0.5">{{ $pointsEarnLabel }}، و{{ $pointsRedeemLabel }}</p>
                </div>
                <div class="flex items-center gap-2.5 self-start sm:self-auto">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200/70 text-amber-900">
                        <span class="font-label-sm text-[12px] text-stone-600">رصيدك:</span>
                        <span class="font-label-md text-[13px] font-bold text-amber-700">{{ auth()->user()->points_balance ?? 0 }} نقطة</span>
                    </div>
                    <a href="{{ route('account.points') }}" class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-surface-container-lowest hover:bg-stone-100 text-stone-600 border border-stone-200/80 text-[12px] font-medium">
                        <span class="material-symbols-outlined text-[15px]">history</span>
                        <span>السجل</span>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($rewards as $reward)
                    <article class="group rounded-2xl bg-surface-container-lowest p-2.5 border border-stone-200/80 hover:border-amber-200 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="relative h-36 w-full rounded-xl overflow-hidden bg-stone-100 mb-2.5">
                                <img alt="{{ $reward['name'] }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" src="{{ $reward['image'] }}">
                                <div class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-stone-900/80 backdrop-blur-sm text-white font-label-sm text-[11px] font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-amber-400 text-[13px]">stars</span>
                                    <span>{{ $reward['points'] }} نقطة</span>
                                </div>
                            </div>
                            <div class="px-1">
                                <h3 class="font-label-lg text-[14px] font-bold text-stone-900 leading-snug group-hover:text-primary transition-colors">{{ $reward['name'] }}</h3>
                                <p class="font-label-sm text-[12px] text-stone-500 mt-0.5">{{ $reward['place'] }}</p>
                            </div>
                        </div>
                        <div class="pt-3 px-1">
                            <a href="{{ $reward['url'] ?? route('redeem.create') }}" class="w-full py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200/70 font-label-md text-[12px] font-semibold transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-amber-700">redeem</span>
                                <span>استبدال مجاناً</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="col-span-full rounded-2xl bg-white p-8 text-stone-500">لا توجد أطباق متاحة للاستبدال حالياً.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="w-full max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop pb-space-lg">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-primary to-primary-container text-on-primary p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="max-w-xl">
                <p class="text-xs font-bold tracking-wide text-on-primary/80">لأصحاب المطاعم والكافيهات</p>
                <h2 class="mt-1 text-2xl font-bold">أضف مطعمك إلى سفرة غزة</h2>
                <p class="mt-2 text-sm text-on-primary/90 leading-relaxed">سجّل بياناتك، ابدأ بتجهيز المنيو من لوحتك فوراً، ويظهر مطعمك للزبائن بعد موافقة الإدارة.</p>
            </div>
            <a href="{{ route('partner.register') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-surface-container-lowest text-primary px-5 py-3 font-bold shrink-0">
                <span>تسجيل مطعم جديد</span>
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            </a>
        </div>
    </section>

    <section class="w-full max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop pb-space-xl">
        <div class="rounded-xl bg-surface-container-lowest p-space-lg shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg text-center md:text-right">
                <div class="flex items-start gap-space-sm">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-primary text-[28px]">moped</span>
                    </div>
                    <div>
                        <h4 class="font-label-lg text-label-lg font-bold text-on-surface mb-1">توصيل سريع وساخن</h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">حقائب حرارية عازلة تضمن وصول طعامك بدرجة حرارة الفرن وفي الوقت المحدد.</p>
                    </div>
                </div>
                <div class="flex items-start gap-space-sm">
                    <div class="w-12 h-12 rounded-full bg-secondary-fixed flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-secondary text-[28px]">payments</span>
                    </div>
                    <div>
                        <h4 class="font-label-lg text-label-lg font-bold text-on-surface mb-1">دفع مرن وآمن 100%</h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">ادفع نقداً عند استلام طلبك، أو عبر إشعار الحوالة المعتمد بدون أي عمولات خفية.</p>
                    </div>
                </div>
                <div class="flex items-start gap-space-sm">
                    <div class="w-12 h-12 rounded-full bg-tertiary-fixed flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-tertiary text-[28px]">support_agent</span>
                    </div>
                    <div>
                        <h4 class="font-label-lg text-label-lg font-bold text-on-surface mb-1">فريق دعم متواجد على الأرض</h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">فريق خدمة عملاء غزاوي يتابع معك حالة تحضير وتوصيل كل طلب خطوة بخطوة.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const scrollContainer = document.getElementById('categories-scroll');
        const prevBtn = document.getElementById('cat-prev-btn');
        const nextBtn = document.getElementById('cat-next-btn');
        if (scrollContainer && prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => scrollContainer.scrollBy({ left: 240, behavior: 'smooth' }));
            nextBtn.addEventListener('click', () => scrollContainer.scrollBy({ left: -240, behavior: 'smooth' }));
        }

        const cards = document.querySelectorAll('.place-card');
        const countEl = document.getElementById('visible-count');
        let areaFilter = '';
        let typeFilter = '';

        const syncAreaChips = (value) => {
            document.querySelectorAll('[data-home-browse] [data-area]').forEach((chip) => {
                chip.classList.toggle('is-active', (chip.dataset.area || '') === value);
            });
        };

        const syncTypeChips = (value) => {
            document.querySelectorAll('.sg-browse__types [data-type]').forEach((chip) => {
                chip.classList.toggle('is-active', (chip.dataset.type || '') === value);
            });
        };

        const applyFilters = () => {
            cards.forEach((card) => {
                const area = card.dataset.area || '';
                const type = card.dataset.type || '';
                const show = (!areaFilter || area === areaFilter) && (!typeFilter || type === typeFilter);
                card.classList.toggle('hidden', !show);
            });
            if (countEl) {
                countEl.textContent = String(
                    [...document.querySelectorAll('#restaurants-grid .place-card')].filter((card) => !card.classList.contains('hidden')).length
                );
            }
        };

        document.querySelectorAll('[data-home-browse] [data-area]').forEach((chip) => {
            chip.addEventListener('click', () => {
                areaFilter = chip.dataset.area || '';
                syncAreaChips(areaFilter);
                applyFilters();
            });
        });

        document.querySelectorAll('.sg-browse__types [data-type]').forEach((chip) => {
            chip.addEventListener('click', () => {
                typeFilter = chip.dataset.type || '';
                syncTypeChips(typeFilter);
                applyFilters();
            });
        });
    })();
</script>
@endsection
