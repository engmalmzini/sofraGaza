@php
    $points = auth()->user()->points_balance ?? 0;
@endphp
<div class="flex flex-col w-full pb-6 space-y-5">
    <section class="px-margin">
        <div class="flex items-center justify-between">
            <p class="text-[13px] text-on-surface-variant">مرحباً بك في سفرة غزة</p>
            <div class="flex items-center gap-1.5 text-secondary font-label-sm text-label-sm font-bold">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                <span>توصيل نشط</span>
            </div>
        </div>
    </section>

    <section class="px-margin">
        <div class="sg-brand-hero sg-brand-hero--mobile">
            <img src="{{ asset('images/brand-mark.jpg') }}" alt="شعار سفرة غزة — كل احتياجاتك في غزة في مكان واحد">
        </div>
    </section>

    <section class="flex flex-col gap-2.5">
        <div class="px-margin flex items-center justify-between">
            <h3 class="text-headline-sm font-headline-sm text-on-surface">التصنيفات الشهية</h3>
            <span class="text-label-sm font-label-sm text-on-surface-variant font-medium">اسحب للمزيد</span>
        </div>
        <div class="flex gap-3 overflow-x-auto px-margin scrollbar-none py-1 -my-1">
            @foreach($categories as $category)
                <a href="{{ route('restaurants.index', ['q' => $category['name']]) }}" class="group flex flex-col items-center gap-1.5 shrink-0 min-w-[70px]">
                    <div class="w-16 h-16 rounded-full {{ $loop->first ? 'bg-primary-fixed' : 'bg-surface-container' }} p-1 shadow-sm flex items-center justify-center overflow-hidden">
                        <img class="w-full h-full object-cover rounded-full" alt="{{ $category['name'] }}" src="{{ $category['image'] }}">
                    </div>
                    <span class="text-label-sm font-label-sm {{ $loop->first ? 'font-bold text-primary' : 'font-medium text-on-surface' }}">{{ $category['name'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="flex flex-col gap-2">
        <div class="px-margin">
            <span class="text-[12px] font-bold text-on-surface-variant">المنطقة</span>
        </div>
        <div class="flex gap-1.5 overflow-x-auto px-margin scrollbar-none" data-home-browse>
            <button class="sg-browse__chip is-active" data-area="" type="button">الكل</button>
            @foreach(config('brand.areas', []) as $area)
                @php
                    $short = str_contains($area['label'], '•') ? trim(explode('•', $area['label'])[1]) : $area['label'];
                @endphp
                <button class="sg-browse__chip" data-area="{{ $area['key'] }}" type="button">{{ $short }}</button>
            @endforeach
        </div>
    </section>

    <section class="px-margin flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 min-w-0">
                <span class="material-symbols-outlined text-primary text-[20px]">local_fire_department</span>
                <h3 class="text-headline-sm font-headline-sm text-on-surface truncate">المختارات</h3>
            </div>
            <a class="text-label-md font-label-md font-bold text-primary shrink-0" href="{{ route('restaurants.index') }}">عرض الكل</a>
        </div>
        <div class="sg-browse__types" role="tablist" aria-label="نوع المكان">
            <button class="sg-browse__chip is-active" data-type="" type="button">الكل</button>
            <button class="sg-browse__chip" data-type="restaurant" type="button">مطاعم</button>
            <button class="sg-browse__chip" data-type="cafe" type="button">كافيهات</button>
        </div>
        <div class="flex flex-col gap-3.5">
            @forelse($restaurants as $restaurant)
                @php
                    $rating = number_format(4.6 + ($restaurant->id % 4) * 0.1, 1);
                    $reviews = 180 + ($restaurant->id * 47);
                    $eta = $restaurant->type === 'cafe' ? '15-25 دقيقة' : '20-30 دقيقة';
                    $pointsGain = $restaurant->type === 'cafe' ? 10 : ($restaurant->is_featured ? 15 : 25);
                @endphp
                <a href="{{ route('restaurants.show', $restaurant) }}" class="place-card group bg-surface-container-lowest rounded-xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] overflow-hidden flex flex-col" data-area="{{ $restaurant->area }}" data-type="{{ $restaurant->type }}">
                    <div class="relative w-full h-40 overflow-hidden bg-surface-container">
                        <img class="w-full h-full object-cover" alt="{{ $restaurant->name }}" src="{{ $restaurant->coverUrl() }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        <div class="absolute top-2.5 right-2.5 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-secondary"></span>
                            <span class="text-label-sm font-label-sm font-bold text-on-surface">مفتوح الآن</span>
                        </div>
                        @if($restaurant->badgeLabel())
                            <div class="absolute top-2.5 left-2.5 bg-primary text-on-primary text-label-sm font-label-sm font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $restaurant->badgeLabel() }}</div>
                        @endif
                        <span class="absolute bottom-2.5 left-2.5 w-8 h-8 rounded-full bg-surface-container-lowest/80 backdrop-blur-md flex items-center justify-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                        </span>
                    </div>
                    <div class="p-3.5 flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <h4 class="text-body-md font-body-md font-bold text-on-surface truncate">{{ $restaurant->name }}</h4>
                                <span class="material-symbols-outlined text-secondary text-[16px] shrink-0">verified</span>
                            </div>
                            <div class="flex items-center gap-1 bg-surface-container-low px-2 py-0.5 rounded-md shrink-0">
                                <span class="material-symbols-outlined text-tertiary text-[14px]">star</span>
                                <span class="text-label-sm font-label-sm font-bold text-on-surface">{{ $rating }}</span>
                                <span class="text-label-sm font-label-sm text-on-surface-variant">({{ $reviews }}+)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant">
                            <span>{{ $restaurant->cuisineLabel() }}</span>
                            <span>•</span>
                            <span class="truncate">{{ $restaurant->address }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 text-label-sm font-label-sm">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1 text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[15px]">schedule</span>
                                    <span>{{ $eta }}</span>
                                </span>
                                <span class="flex items-center gap-1 {{ $restaurant->type === 'cafe' ? 'text-on-surface-variant' : 'text-secondary font-bold' }}">
                                    <span class="material-symbols-outlined text-[15px]">{{ $restaurant->type === 'cafe' ? 'moped' : 'two_wheeler' }}</span>
                                    <span>{{ $restaurant->type === 'cafe' ? 'توصيل 5 ' : 'توصيل مجاني' }}@if($restaurant->type === 'cafe')<span class="ils">₪</span>@endif</span>
                                </span>
                            </div>
                            <span class="text-primary font-bold text-label-sm bg-primary-fixed/40 px-2 py-0.5 rounded">+{{ $pointsGain }} نقطة</span>
                        </div>
                    </div>
                </a>
            @empty
                <p class="rounded-xl bg-white p-8 text-on-surface-variant">لا توجد مطاعم ظاهرة حالياً.</p>
            @endforelse
        </div>
    </section>

    <section class="bg-surface-container-low/60 py-4 flex flex-col gap-3">
        <div class="px-margin flex items-center justify-between">
            <div class="flex flex-col">
                <div class="flex items-center gap-1 text-primary">
                    <span class="material-symbols-outlined text-[18px]">military_tech</span>
                    <h3 class="text-headline-sm font-headline-sm text-on-surface">مكافآت نقاطك الغذائية</h3>
                </div>
                <span class="text-label-sm font-label-sm text-on-surface-variant">استبدل نقاطك فوراً بوجبات مجانية</span>
            </div>
            <div class="flex items-center gap-1 bg-secondary-fixed text-on-secondary-fixed px-3 py-1 rounded-full shadow-sm">
                <span class="material-symbols-outlined text-[15px]">stars</span>
                <span class="text-label-sm font-label-sm font-bold">{{ $points }} نقطة</span>
            </div>
        </div>
        <div class="flex gap-3 overflow-x-auto px-margin scrollbar-none py-1">
            @foreach($rewards as $reward)
                <article class="bg-surface-container-lowest rounded-xl p-3 shadow-[0_2px_8px_rgba(0,0,0,0.04)] shrink-0 w-56 flex flex-col gap-2.5 justify-between {{ !empty($reward['locked']) ? 'opacity-85' : '' }}">
                    <div class="relative w-full h-28 rounded-lg overflow-hidden bg-surface-container">
                        <img class="w-full h-full object-cover" alt="{{ $reward['name'] }}" src="{{ $reward['image'] }}">
                        <div class="absolute top-2 right-2 {{ !empty($reward['locked']) ? 'bg-on-surface-variant text-surface' : 'bg-secondary text-on-secondary' }} text-label-sm font-label-sm font-bold px-2 py-0.5 rounded-full shadow-sm">
                            {{ $reward['points'] }} نقطة
                        </div>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <h4 class="text-label-lg font-label-lg text-on-surface font-bold truncate">{{ $reward['name'] }}</h4>
                        <span class="text-label-sm font-label-sm text-on-surface-variant truncate">{{ $reward['subtitle'] ?? $reward['place'] }}</span>
                    </div>
                    @if(!empty($reward['locked']))
                        <div class="w-full py-2 rounded-full bg-surface-container text-on-surface-variant font-label-sm font-medium text-center flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">lock</span>
                            <span>متبقي {{ $reward['need'] }} نقطة</span>
                        </div>
                    @else
                        <a href="{{ route('redeem.create') }}" class="w-full py-2 rounded-full bg-secondary text-on-secondary font-label-sm font-bold text-center flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">redeem</span>
                            <span>استبدال مجاناً</span>
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <section class="px-margin">
        <a href="{{ route('partner.register') }}" class="flex items-center justify-between gap-3 rounded-xl bg-primary text-on-primary p-4 shadow-[0_6px_20px_rgba(163,57,0,0.18)]">
            <div>
                <p class="text-label-sm font-bold">صاحب مطعم؟</p>
                <p class="text-[12px] text-on-primary/85 leading-snug">سجّل الآن وجهّز منيوك قبل النشر</p>
            </div>
            <span class="material-symbols-outlined">storefront</span>
        </a>
    </section>

    <section class="px-margin pt-1">
        <div class="bg-surface-container-lowest rounded-xl p-3.5 shadow-[0_1px_8px_rgba(0,0,0,0.03)] grid grid-cols-3 gap-2 text-center">
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                </div>
                <span class="text-label-sm font-label-sm font-bold text-on-surface">توصيل ساخن</span>
                <span class="text-[10px] text-on-surface-variant leading-tight">حقائب حرارية معتمدة</span>
            </div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                </div>
                <span class="text-label-sm font-label-sm font-bold text-on-surface">دفع آمن ومرن</span>
                <span class="text-[10px] text-on-surface-variant leading-tight">نقداً أو إلكترونياً</span>
            </div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-full bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                </div>
                <span class="text-label-sm font-label-sm font-bold text-on-surface">دعم على الأرض</span>
                <span class="text-[10px] text-on-surface-variant leading-tight">فريق متواجد بغزة 24/7</span>
            </div>
        </div>
    </section>
</div>
