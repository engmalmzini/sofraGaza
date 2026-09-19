@php $item = $menuItem ?? null; @endphp
<div>
    <label class="mb-1 block text-sm font-bold">اسم الصنف</label>
    <input name="name" value="{{ old('name', $item->name ?? '') }}" class="w-full rounded-xl border px-3 py-3">
</div>
<div>
    <label class="mb-1 block text-sm font-bold">التصنيف</label>
    <select name="category" class="w-full rounded-xl border px-3 py-3">
        @foreach(['وجبات','مشروبات','مقبلات','حلويات'] as $cat)
            <option value="{{ $cat }}" @selected(old('category', $item->category ?? 'وجبات') === $cat)>{{ $cat }}</option>
        @endforeach
    </select>
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
