@php
    $item = $menuItem ?? null;
    $categories = $categories ?? ($restaurant ?? null)?->menuCategories() ?? ['وجبات', 'مشروبات', 'مقبلات', 'حلويات'];
    $selectedCategory = old('category', $item->category ?? request('category') ?? ($categories[0] ?? 'وجبات'));
    $isCustom = $selectedCategory === '__custom__' || ($selectedCategory && ! in_array($selectedCategory, $categories, true));
@endphp
<div>
    <label class="mb-1 block text-sm font-bold">اسم الصنف</label>
    <input name="name" value="{{ old('name', $item->name ?? '') }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">التصنيف</label>
    <select name="category" class="w-full rounded-xl border px-3 py-3" data-category-select>
        @foreach($categories as $cat)
            <option value="{{ $cat }}" @selected(! $isCustom && $selectedCategory === $cat)>{{ $cat }}</option>
        @endforeach
        <option value="__custom__" @selected($isCustom)>تصنيف آخر…</option>
    </select>
</div>
<div data-category-custom @if(! $isCustom) hidden @endif>
    <label class="mb-1 block text-sm font-bold">اسم التصنيف الجديد</label>
    <input name="category_custom" value="{{ old('category_custom', $isCustom && $selectedCategory !== '__custom__' ? $selectedCategory : '') }}" class="w-full rounded-xl border px-3 py-3" placeholder="مثال: فطور أو مشروبات ساخنة">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">الوصف</label>
    <textarea name="description" rows="3" class="w-full rounded-xl border px-3 py-3">{{ old('description', $item->description ?? '') }}</textarea>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">السعر (<span class="ils">₪</span>)</label>
    <input type="number" step="0.01" name="price" value="{{ old('price', $item->price ?? '') }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">صورة</label>
    <input type="file" name="image" accept="image/*">
</div>
<label class="flex items-center gap-2"><input type="checkbox" name="is_available" value="1" @checked(old('is_available', $item->is_available ?? true))> متوفر</label>
<script>
    document.querySelectorAll('[data-category-select]').forEach((select) => {
        const wrap = select.form?.querySelector('[data-category-custom]');
        if (!wrap) return;
        const toggle = () => { wrap.hidden = select.value !== '__custom__'; };
        select.addEventListener('change', toggle);
        toggle();
    });
</script>
