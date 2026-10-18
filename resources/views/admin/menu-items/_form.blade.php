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
@if(request()->routeIs('admin.*'))
<div class="rounded-2xl border border-dashed border-olive/20 p-4 space-y-3">
    <h3 class="text-sm font-extrabold">تخصيص النقاط لهذا الصنف</h3>
    <p class="text-xs leading-6 text-on-surface-variant">فارغ = حسب سعر الصنف وسعر المطعم أو الإعداد العام. رقم ثابت يتجاوز السعر.</p>
    <div class="grid gap-3 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-bold">نقاط مكتسبة لكل وحدة</label>
            <input type="number" min="0" step="1" name="earn_points" value="{{ old('earn_points', $item->earn_points ?? '') }}" placeholder="حسب السعر" class="w-full rounded-xl border px-3 py-3">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">تكلفة الاستبدال بالنقاط</label>
            <input type="number" min="0" step="1" name="redeem_points" value="{{ old('redeem_points', $item->redeem_points ?? '') }}" placeholder="حسب السعر" class="w-full rounded-xl border px-3 py-3">
        </div>
    </div>
</div>
@endif
<div>
    <label class="mb-1 block text-sm font-bold">صورة</label>
    <input type="file" name="image" accept="image/*">
</div>
@php
    $stockMode = old('stock_mode', ($item && ! ($item->stock_unlimited ?? true)) ? 'limited' : 'unlimited');
    $stockQty = old('stock_qty', ($item && ! ($item->stock_unlimited ?? true)) ? $item->stock_qty : '');
@endphp
<fieldset class="rounded-2xl border border-slate-200 p-4 space-y-3">
    <legend class="px-1 text-sm font-extrabold">الكمية</legend>
    <p class="text-xs leading-6 text-on-surface-variant">حدد كم قطعة متوفرة من هذا الصنف، أو اختر أنها لا تنتهي.</p>
    <label class="flex items-start gap-2 text-sm font-bold">
        <input type="radio" name="stock_mode" value="unlimited" class="mt-1" data-stock-mode @checked($stockMode === 'unlimited') required>
        <span>
            لا نهائية (لا تنتهي)
            <small class="block font-medium text-on-surface-variant">الزبون يطلب بلا حد للكمية</small>
        </span>
    </label>
    <label class="flex items-start gap-2 text-sm font-bold">
        <input type="radio" name="stock_mode" value="limited" class="mt-1" data-stock-mode @checked($stockMode === 'limited')>
        <span>
            كمية محددة
            <small class="block font-medium text-on-surface-variant">تنقص مع كل طلب، وتعود إذا أُلغي أو رُفض</small>
        </span>
    </label>
    <div data-stock-qty-wrap @if($stockMode !== 'limited') hidden @endif>
        <label class="mb-1 block text-sm font-bold">العدد المتوفر الآن</label>
        <input type="number" min="0" step="1" name="stock_qty" value="{{ $stockQty }}" class="w-full rounded-xl border px-3 py-3" placeholder="مثال: 20">
        @error('stock_qty')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</fieldset>
<label class="flex items-center gap-2"><input type="checkbox" name="is_available" value="1" @checked(old('is_available', $item->is_available ?? true))> متوفر للطلب</label>
<script>
    document.querySelectorAll('[data-category-select]').forEach((select) => {
        const wrap = select.form?.querySelector('[data-category-custom]');
        if (!wrap) return;
        const toggle = () => { wrap.hidden = select.value !== '__custom__'; };
        select.addEventListener('change', toggle);
        toggle();
    });

    const qtyWrap = document.querySelector('[data-stock-qty-wrap]');
    const qtyInput = qtyWrap?.querySelector('input[name="stock_qty"]');
    const syncStock = () => {
        if (!qtyWrap || !qtyInput) return;
        const limited = document.querySelector('[data-stock-mode][value="limited"]')?.checked;
        qtyWrap.hidden = !limited;
        qtyInput.required = Boolean(limited);
    };
    document.querySelectorAll('[data-stock-mode]').forEach((radio) => {
        radio.addEventListener('change', syncStock);
    });
    syncStock();
</script>
