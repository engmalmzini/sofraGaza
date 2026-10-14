@php $m = $membership ?? null; @endphp
<div>
    <label class="mb-1 block text-sm font-bold">الاسم</label>
    <input name="name" value="{{ old('name', $m->name ?? '') }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">السعر الشهري</label>
    <input type="number" step="0.01" name="monthly_price" value="{{ old('monthly_price', $m->monthly_price ?? '') }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">نسبة الخصم على التوصيل %</label>
    <input type="number" min="0" max="100" name="discount_percent" id="membership-delivery-percent" value="{{ old('discount_percent', $m->discount_percent ?? 0) }}" class="w-full rounded-xl border px-3 py-3">
    <p class="mt-1 text-xs text-olive/60">الخصم من رسوم التوصيل فقط، وسعر وجبة المطعم يبقى كاملاً. 100 = توصيل مجاني.</p>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">مضاعف النقاط</label>
    <input type="number" step="0.01" name="points_multiplier" value="{{ old('points_multiplier', $m->points_multiplier ?? 1) }}" class="w-full rounded-xl border px-3 py-3">
    <p class="mt-1 text-xs text-olive/60">مثال: 1 نقطة لكل شيكل لغير الأعضاء، 1.25 للأساسية، 1.5 للمميزة. يظهر للزبون كنسبة نقاط لكل مبلغ اكتساب الذي يحدده الأدمن.</p>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">الوصف</label>
    <textarea name="description" class="w-full rounded-xl border px-3 py-3">{{ old('description', $m->description ?? '') }}</textarea>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">الترتيب</label>
    <input type="number" name="sort_order" value="{{ old('sort_order', $m->sort_order ?? 0) }}" class="w-full rounded-xl border px-3 py-3">
</div>
<label class="flex items-center gap-2">
    <input type="checkbox" name="free_delivery" id="membership-includes-delivery" value="1" @checked(old('free_delivery', $m->free_delivery ?? false))>
    <span>التوصيل ضمن العضوية</span>
</label>
<p class="text-xs text-olive/60 -mt-1">إذا فعّلتها، نسبة الخصم أعلاه تنطبق على رسوم التوصيل. إذا أطفأتها، الزبون يدفع التوصيل كاملاً.</p>
<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m->is_active ?? true))> نشطة</label>
<script>
    (function () {
        var box = document.getElementById('membership-includes-delivery');
        var percent = document.getElementById('membership-delivery-percent');
        if (!box || !percent) return;
        function sync() {
            percent.classList.toggle('opacity-50', !box.checked);
            percent.readOnly = !box.checked;
        }
        box.addEventListener('change', sync);
        sync();
    })();
</script>
