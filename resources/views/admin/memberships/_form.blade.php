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
    <label class="mb-1 block text-sm font-bold">نسبة الخصم %</label>
    <input type="number" name="discount_percent" value="{{ old('discount_percent', $m->discount_percent ?? 0) }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">مضاعف النقاط</label>
    <input type="number" step="0.01" name="points_multiplier" value="{{ old('points_multiplier', $m->points_multiplier ?? 1) }}" class="w-full rounded-xl border px-3 py-3">
    <p class="mt-1 text-xs text-olive/60">1 = نقطة لكل شيكل، 1.25 للعضوية الأساسية، 1.5 للمميزة.</p>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">الوصف</label>
    <textarea name="description" class="w-full rounded-xl border px-3 py-3">{{ old('description', $m->description ?? '') }}</textarea>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">الترتيب</label>
    <input type="number" name="sort_order" value="{{ old('sort_order', $m->sort_order ?? 0) }}" class="w-full rounded-xl border px-3 py-3">
</div>
<label class="flex items-center gap-2"><input type="checkbox" name="free_delivery" value="1" @checked(old('free_delivery', $m->free_delivery ?? false))> توصيل مجاني</label>
<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m->is_active ?? true))> نشطة</label>
