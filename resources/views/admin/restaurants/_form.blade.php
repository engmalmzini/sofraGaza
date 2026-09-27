@php $restaurant = $restaurant ?? null; @endphp
<div>
    <label class="mb-1 block text-sm font-bold">الاسم</label>
    <input name="name" value="{{ old('name', $restaurant->name ?? '') }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">النوع</label>
    <select name="type" class="w-full rounded-xl border px-3 py-3">
        <option value="restaurant" @selected(old('type', $restaurant->type ?? '') === 'restaurant')>مطعم</option>
        <option value="cafe" @selected(old('type', $restaurant->type ?? '') === 'cafe')>كافي</option>
    </select>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">الوصف</label>
    <textarea name="description" rows="3" class="w-full rounded-xl border px-3 py-3">{{ old('description', $restaurant->description ?? '') }}</textarea>
</div>
<div class="grid gap-3 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-bold">الهاتف</label>
        <input name="phone" value="{{ old('phone', $restaurant->phone ?? '') }}" class="w-full rounded-xl border px-3 py-3">
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">العنوان</label>
        <input name="address" value="{{ old('address', $restaurant->address ?? '') }}" class="w-full rounded-xl border px-3 py-3">
    </div>
</div>
<div class="grid gap-3 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-bold">بداية العرض</label>
        <input type="date" name="starts_at" value="{{ old('starts_at', isset($restaurant) ? $restaurant->starts_at->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full rounded-xl border px-3 py-3">
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">نهاية العرض</label>
        <input type="date" name="expires_at" value="{{ old('expires_at', isset($restaurant) ? $restaurant->expires_at->format('Y-m-d') : now()->addDays(30)->format('Y-m-d')) }}" class="w-full rounded-xl border px-3 py-3">
    </div>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">صورة</label>
    <input type="file" name="image" accept="image/*">
</div>
<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $restaurant->is_active ?? true))> ظاهر على المنصة</label>
<label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $restaurant->is_featured ?? false))> مميز في الرئيسية</label>
<div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-4 sm:p-5 space-y-4">
    <div class="flex items-center justify-between border-b border-amber-200/60 pb-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-600 text-[24px] fill-1">stars</span>
            <div>
                <h3 class="text-sm font-extrabold text-stone-900">سعر ونظام النقاط لهذا المطعم (اختياري)</h3>
                <p class="text-xs text-on-surface-variant">اترك الحقول فارغة لاعتماد السعر العام للمنصة تلقائياً، أو حدد تسعيراً خاصاً بهذا المطعم.</p>
            </div>
        </div>
        <span class="text-xs font-bold text-amber-800 bg-amber-100/80 px-2.5 py-1 rounded-full">نظام الولاء والمكافآت</span>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        {{-- Earn points rate --}}
        <div class="rounded-xl bg-white p-3.5 border border-slate-200 space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-xs font-extrabold text-stone-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>1. اكتساب النقاط (عند الشراء)</span>
                </label>
                <span class="text-[11px] font-mono text-stone-400">شيكل / نقطة</span>
            </div>
            <div class="relative">
                <input type="number" step="0.01" min="0.01" id="input_points_earn" name="points_per_amount" value="{{ old('points_per_amount', $restaurant->points_per_amount ?? '') }}" placeholder="عام (افتراضي المنصة)" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-stone-900 focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                <span class="text-[10px] text-stone-500 font-bold">خيارات سريعة:</span>
                <button type="button" onclick="setEarnRate(10)" class="px-2 py-0.5 rounded-md bg-emerald-100 hover:bg-emerald-200 text-emerald-900 text-[10px] font-bold transition-colors">كل 10 ₪ = نقطة</button>
                <button type="button" onclick="setEarnRate(5)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors">كل 5 ₪ = نقطة</button>
                <button type="button" onclick="setEarnRate(1)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors">كل 1 ₪ = نقطة</button>
            </div>
            <div class="text-[11px] leading-relaxed text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                <strong class="text-emerald-700 block mb-0.5 font-bold">💡 كيف تُحسب النقاط للزبون؟</strong>
                المبلغ الذي ينفقه الزبون ليكسب <span class="font-bold text-stone-900">نقطة واحدة</span>.
                <br>
                مثلاً: إذا وضعت <span class="font-mono font-bold text-stone-900">10</span>، فكل <span class="font-bold">10 شيكل</span> مشتريات = <span class="font-bold">1 نقطة</span> (طلب بقيمة 100 ₪ يكسب الزبون 10 نقاط).
            </div>
        </div>

        {{-- Redeem points rate --}}
        <div class="rounded-xl bg-white p-3.5 border border-slate-200 space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-xs font-extrabold text-stone-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>2. استبدال النقاط (وجبة مجانية)</span>
                </label>
                <span class="text-[11px] font-mono text-stone-400">شيكل / نقطة</span>
            </div>
            <div class="relative">
                <input type="number" step="0.01" min="0.01" id="input_points_redeem" name="points_redeem_per_amount" value="{{ old('points_redeem_per_amount', $restaurant->points_redeem_per_amount ?? '') }}" placeholder="عام (افتراضي المنصة)" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-stone-900 focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                <span class="text-[10px] text-stone-500 font-bold">خيارات سريعة:</span>
                <button type="button" onclick="setRedeemRate(0.10)" class="px-2 py-0.5 rounded-md bg-amber-100 hover:bg-amber-200 text-amber-900 text-[10px] font-bold transition-colors">كل 1 ₪ = 10 نقاط</button>
                <button type="button" onclick="setRedeemRate(0.20)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors">كل 1 ₪ = 5 نقاط</button>
                <button type="button" onclick="setRedeemRate(1)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-[10px] font-bold transition-colors">كل 1 ₪ = نقطة</button>
            </div>
            <div class="text-[11px] leading-relaxed text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                <strong class="text-amber-800 block mb-0.5 font-bold">💡 كم نقطة تلزم لشراء وجبة؟</strong>
                قيمة النقطة بالشيكل عند استبدالها بوجبة.
                <br>
                • إذا وضعت <span class="font-mono font-bold text-amber-700">0.10</span> (أو ضغطت الزر أعلاه): كل 1 ₪ يحتاج <span class="font-bold">10 نقاط</span> (وجبة بـ 30 ₪ تتطلب 300 نقطة).
                <br>
                • إذا وضعت <span class="font-mono font-bold text-stone-900">1</span>: كل 1 ₪ يحتاج 1 نقطة (وجبة بـ 30 ₪ تتطلب 30 نقطة).
            </div>
        </div>
    </div>

    {{-- Live Interactive Preview Calculator --}}
    <div class="rounded-xl bg-surface-container-low border border-slate-200/80 p-3.5 text-xs">
        <div class="font-bold text-stone-900 flex items-center gap-1.5 mb-1.5">
            <span class="material-symbols-outlined text-primary text-[18px]">calculate</span>
            <span>معاينة حية ومباشرة للحسبة حسب الأرقام المدخلة:</span>
        </div>
        <div class="grid sm:grid-cols-2 gap-3 text-stone-700 mt-2">
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <div class="text-slate-500 text-[11px]">طلب بقيمة 100 ₪ من هذا المطعم:</div>
                <div class="font-bold text-emerald-800 mt-1" id="preview_earn_text">
                    يكسب الزبون: <strong>100 نقطة</strong> (حسب المعدل الافتراضي)
                </div>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <div class="text-slate-500 text-[11px]">وجبة من المنيو سعرها 30 ₪:</div>
                <div class="font-bold text-amber-900 mt-1" id="preview_redeem_text">
                    تتطلب للاستبدال: <strong>30 نقطة</strong> (حسب المعدل الافتراضي)
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const earnInput = document.getElementById('input_points_earn');
        const redeemInput = document.getElementById('input_points_redeem');
        const earnText = document.getElementById('preview_earn_text');
        const redeemText = document.getElementById('preview_redeem_text');

        function updatePreviews() {
            const earnRate = parseFloat(earnInput ? earnInput.value : '') || 1;
            const redeemRate = parseFloat(redeemInput ? redeemInput.value : '') || 1;

            const sampleOrder = 100;
            const earnedPoints = Math.floor(sampleOrder / earnRate);
            if (earnText) {
                earnText.innerHTML = `يكسب الزبون: <span class="text-stone-900 font-mono text-sm">${earnedPoints} نقطة</span> (كل ${earnRate} ₪ = 1 نقطة)`;
            }

            const sampleItemPrice = 30;
            const redeemPoints = Math.round(sampleItemPrice / redeemRate);
            const ptsPerShekel = (1 / redeemRate).toFixed(1).replace(/\.0$/, '');
            if (redeemText) {
                redeemText.innerHTML = `تتطلب للاستبدال: <span class="text-stone-900 font-mono text-sm">${redeemPoints} نقطة</span> (كل 1 ₪ = ${ptsPerShekel} نقطة)`;
            }
        }

        window.setEarnRate = function(val) {
            if (earnInput) {
                earnInput.value = val;
                updatePreviews();
            }
        };

        window.setRedeemRate = function(val) {
            if (redeemInput) {
                redeemInput.value = val;
                updatePreviews();
            }
        };

        if (earnInput && redeemInput) {
            earnInput.addEventListener('input', updatePreviews);
            redeemInput.addEventListener('input', updatePreviews);
            updatePreviews();
        }
    })();
</script>
