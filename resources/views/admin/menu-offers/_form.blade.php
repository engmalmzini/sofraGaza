@php
    $offer = $offer ?? null;
    $items = $items ?? collect();
@endphp
@if($items->isEmpty())
    <p class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-2 text-sm text-amber-800">أضف صنفاً واحداً على الأقل للمنيو قبل إنشاء عرض.</p>
@else
<div>
    <label class="mb-1 block text-sm font-bold">اسم العرض</label>
    <input name="name" value="{{ old('name', $offer->name ?? '') }}" class="w-full rounded-xl border px-3 py-3" placeholder="مثال: عرض الصباح أو قهوة + كيك" required>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">وصف قصير (اختياري)</label>
    <textarea name="description" rows="2" class="w-full rounded-xl border px-3 py-3" placeholder="يظهر للزبون فوق الأصناف">{{ old('description', $offer->description ?? '') }}</textarea>
</div>
<div class="grid gap-3 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-bold">الصنف الأول</label>
        <select name="first_item_id" class="w-full rounded-xl border px-3 py-3" required>
            <option value="">اختر صنفاً</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" @selected((string) old('first_item_id', $offer->first_item_id ?? '') === (string) $item->id)>
                    {{ $item->name }} — {{ number_format($item->price, 2) }} ₪
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">الصنف الثاني (اختياري)</label>
        <select name="second_item_id" class="w-full rounded-xl border px-3 py-3">
            <option value="">عرض على صنف واحد</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" @selected((string) old('second_item_id', $offer->second_item_id ?? '') === (string) $item->id)>
                    {{ $item->name }} — {{ number_format($item->price, 2) }} ₪
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-on-surface-variant">اتركه فارغاً إذا العرض على صنف واحد بسعر مخفّض.</p>
    </div>
</div>
<div>
    <label class="mb-1 block text-sm font-bold">سعر العرض (<span class="ils">₪</span>)</label>
    <input type="number" step="0.5" min="0.5" name="price" value="{{ old('price', $offer->price ?? '') }}" class="w-full rounded-xl border px-3 py-3" required>
    <p class="mt-1 text-[11px] text-on-surface-variant">لازم يكون أقل من مجموع أسعار الأصناف المختارة.</p>
    @error('price')
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
<div>
    <label class="mb-1 block text-sm font-bold">صورة العرض (اختياري)</label>
    @if($offer?->imageUrl())
        <img src="{{ $offer->imageUrl() }}" alt="" class="mb-2 h-24 w-24 rounded-xl object-cover border border-slate-200">
    @endif
    <input type="file" name="image" accept="image/*">
</div>
<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer->is_active ?? true))> ظاهر للزبائن</label>
@endif
