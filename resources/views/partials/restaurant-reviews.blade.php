@php
    $reviewsList = $approvedReviews ?? $restaurant->approvedReviews()->with('user')->take(20)->get();
    $breakdown = $ratingBreakdown ?? $restaurant->ratingBreakdown();
    $avgRating = $rating ?? number_format($restaurant->averageRating(), 1);
    $totalCount = $reviews ?? $restaurant->reviewsCount();
@endphp

<section id="reviews" class="rounded-3xl bg-surface-container-lowest border border-slate-100 p-6 sm:p-8 shadow-xs space-y-8 my-8">
    {{-- Section Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-5">
        <div>
            <div class="inline-flex items-center gap-1.5 text-amber-700 text-xs font-bold mb-1">
                <span class="material-symbols-outlined text-[16px] text-amber-500 fill-1">star</span>
                <span>آراء موثقة</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-stone-900">تقييمات وآراء الزبائن</h2>
            <p class="text-xs sm:text-sm text-stone-500 mt-1">تجارب حقيقية لزبائن طلبوا من {{ $restaurant->name }} عبر سفرة غزة</p>
        </div>

        <div class="flex items-center gap-3 bg-amber-50/80 border border-amber-200/60 rounded-2xl px-5 py-3 shrink-0">
            <div class="text-center">
                <span class="text-3xl sm:text-4xl font-black text-amber-950 font-mono block leading-none">{{ $avgRating }}</span>
                <span class="text-[10px] text-amber-800/80 font-semibold block mt-1">من 5 نجوم</span>
            </div>
            <div class="border-r border-amber-200/80 pr-3 flex flex-col justify-center">
                <div class="flex items-center gap-0.5 text-amber-500">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="material-symbols-outlined text-[18px] {{ $i <= round((float) $avgRating) ? 'fill-1' : 'opacity-30' }}">star</span>
                    @endfor
                </div>
                <span class="text-xs font-bold text-amber-900 mt-1">{{ number_format($totalCount) }} تقييم معتمد</span>
            </div>
        </div>
    </div>

    {{-- Rating Breakdown Grid --}}
    <div class="grid gap-6 md:grid-cols-2 items-center bg-stone-50/60 rounded-2xl p-5 border border-slate-100">
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-stone-700 mb-2">توزيع التقييمات</h3>
            @foreach([5, 4, 3, 2, 1] as $star)
                @php
                    $starData = $breakdown[$star] ?? ['count' => 0, 'percentage' => 0];
                @endphp
                <div class="flex items-center gap-2.5 text-xs text-stone-600">
                    <span class="w-12 font-bold shrink-0 flex items-center gap-1 justify-end">
                        <span>{{ $star }}</span>
                        <span class="material-symbols-outlined text-[14px] text-amber-500 fill-1">star</span>
                    </span>
                    <div class="flex-1 h-2 rounded-full bg-slate-200/80 overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: {{ $starData['percentage'] }}%"></div>
                    </div>
                    <span class="w-10 text-[11px] font-mono text-stone-400 text-left shrink-0">({{ $starData['count'] }})</span>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col justify-center border-t md:border-t-0 md:border-r border-slate-200/70 pt-4 md:pt-0 md:pr-6 space-y-2 text-xs text-stone-600">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
                <span class="font-bold text-stone-800">تقييمات شفافة 100%</span>
            </div>
            <p class="leading-relaxed text-stone-500 text-[11px]">
                نضمن مصداقية كافة الآراء والتقييمات المعروضة من خلال ربطها بالطلبات الحقيقية المنفذة داخل قطاع غزة.
            </p>
        </div>
    </div>

    {{-- Add Review Form --}}
    <div class="rounded-2xl border border-slate-200 bg-surface-container-low/50 p-5 space-y-4">
        <div class="flex items-center gap-2 text-stone-900 font-bold text-sm">
            <span class="material-symbols-outlined text-primary text-[20px]">rate_review</span>
            <span>شاركنا تجربتك وتقييمك</span>
        </div>

        @auth
            <form method="POST" action="{{ route('restaurants.reviews.store', $restaurant) }}" class="space-y-4">
                @csrf

                {{-- Interactive Star Selector --}}
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1.5">حدد تقييمك من 1 إلى 5 نجوم</label>
                    <div class="flex items-center gap-1 text-amber-400" id="star-rating-picker">
                        @for($s = 1; $s <= 5; $s++)
                            <button type="button" 
                                    class="star-btn p-1 transition-transform hover:scale-110 focus:outline-hidden"
                                    data-value="{{ $s }}"
                                    onclick="selectRating({{ $s }})"
                                    onmouseenter="hoverRating({{ $s }})"
                                    onmouseleave="resetHoverRating()">
                                <span class="material-symbols-outlined text-[28px] star-icon text-slate-300">star</span>
                            </button>
                        @endfor
                        <span id="star-rating-label" class="text-xs font-bold text-stone-600 mr-2">اضغط على النجوم للتقييم</span>
                    </div>
                    <input type="hidden" id="selected-rating-input" name="rating" value="5" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">تعليقك ورأيك (اختياري)</label>
                    <textarea name="comment" rows="3" placeholder="ما رأيك بمذاق الوجبة، سرعة التوصيل، والتغليف؟" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-white text-stone-900 focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] text-stone-400">سيتم نشر تقييمك باسم حسابك الحالي ({{ auth()->user()->name }})</p>
                    <button type="submit" class="rounded-xl bg-primary hover:bg-primary-container text-white px-5 py-2.5 font-bold text-xs shadow-xs transition-colors">
                        إرسال التقييم
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 rounded-xl bg-white border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-stone-600">
                    سجّل دخولك بحسابك لتتمكن من إضافة تقييم ومشاركة رأيك وتجربتك مع المطعم.
                </div>
                <a href="{{ route('login') }}" class="rounded-xl bg-primary text-white hover:bg-primary-container px-4 py-2 font-bold text-xs transition-colors">
                    تسجيل الدخول للتقييم
                </a>
            </div>
        @endauth
    </div>

    {{-- Reviews List --}}
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-stone-900">أحدث الآراء المكتوبة</h3>

        <div class="space-y-3">
            @forelse($reviewsList as $rev)
                <article class="p-4 rounded-2xl bg-white border border-slate-100 shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center shrink-0">
                                {{ mb_substr($rev->user->name ?? 'ز', 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-xs text-stone-900 flex items-center gap-1.5">
                                    <span>{{ $rev->user->name ?? 'زبون سفرة' }}</span>
                                    @if($rev->isVerifiedPurchase())
                                        <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.2 rounded-full border border-emerald-200/50">
                                            <span class="material-symbols-outlined text-[12px]">verified</span>
                                            <span>طلب مؤكد</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-stone-400">{{ $rev->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-0.5 text-amber-500">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="material-symbols-outlined text-[15px] {{ $i <= $rev->rating ? 'fill-1' : 'opacity-25' }}">star</span>
                            @endfor
                        </div>
                    </div>

                    @if($rev->comment)
                        <p class="text-xs text-stone-700 leading-relaxed pr-10">
                            {{ $rev->comment }}
                        </p>
                    @endif
                </article>
            @empty
                <div class="p-8 text-center rounded-2xl border border-dashed border-slate-200 text-stone-400">
                    <span class="material-symbols-outlined text-4xl block mb-1 opacity-40">rate_review</span>
                    <p class="text-xs">لا توجد تقييمات مكتوبة بعد. اطلب الآن وكن أول من يشارك رأيه!</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<script>
let currentSelectedRating = 5;
const ratingLabels = {
    1: 'نجمة واحدة (سيئ)',
    2: 'نجمتان (مقبول)',
    3: '3 نجوم (جيد)',
    4: '4 نجوم (جيد جداً)',
    5: '5 نجوم (ممتاز ورائع)'
};

function selectRating(val) {
    currentSelectedRating = val;
    document.getElementById('selected-rating-input').value = val;
    updateStarDisplay(val);
    document.getElementById('star-rating-label').textContent = ratingLabels[val] || '';
}

function hoverRating(val) {
    updateStarDisplay(val);
    document.getElementById('star-rating-label').textContent = ratingLabels[val] || '';
}

function resetHoverRating() {
    updateStarDisplay(currentSelectedRating);
    document.getElementById('star-rating-label').textContent = ratingLabels[currentSelectedRating] || '';
}

function updateStarDisplay(val) {
    const btns = document.querySelectorAll('#star-rating-picker .star-btn');
    btns.forEach((btn, idx) => {
        const starIcon = btn.querySelector('.star-icon');
        if (idx < val) {
            starIcon.classList.remove('text-slate-300');
            starIcon.classList.add('text-amber-500', 'fill-1');
        } else {
            starIcon.classList.add('text-slate-300');
            starIcon.classList.remove('text-amber-500', 'fill-1');
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    selectRating(5);
});
</script>
