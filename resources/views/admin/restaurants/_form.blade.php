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
