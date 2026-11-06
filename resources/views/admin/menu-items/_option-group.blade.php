@php
    $gi = $gi ?? 0;
    $group = $group ?? ['name' => '', 'required' => true, 'type' => 'single', 'choices' => []];
    $choices = $group['choices'] ?? [['name' => '', 'price' => 0]];
    if ($choices === []) {
        $choices = [['name' => '', 'price' => 0]];
    }
@endphp
<div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 space-y-2" data-option-group>
    <div class="grid gap-2 sm:grid-cols-[1fr_8rem_auto] items-end">
        <div>
            <label class="mb-1 block text-xs font-bold">اسم القسم</label>
            <input name="option_groups[{{ $gi }}][name]" value="{{ $group['name'] ?? '' }}" placeholder="مثلاً: النوع" class="w-full rounded-xl border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold">طريقة الاختيار</label>
            <select name="option_groups[{{ $gi }}][type]" class="w-full rounded-xl border px-3 py-2 text-xs">
                <option value="single" @selected(($group['type'] ?? 'single') === 'single')>خيار واحد</option>
                <option value="multi" @selected(($group['type'] ?? '') === 'multi')>أكثر من خيار</option>
            </select>
        </div>
        <label class="flex items-center gap-1.5 text-xs font-bold pb-2">
            <input type="hidden" name="option_groups[{{ $gi }}][required]" value="0">
            <input type="checkbox" name="option_groups[{{ $gi }}][required]" value="1" @checked($group['required'] ?? true)>
            إلزامي
        </label>
    </div>
    <div class="space-y-2" data-option-choice-list>
        @foreach($choices as $ci => $choice)
            <div class="grid grid-cols-[1fr_6.5rem_auto] gap-2" data-option-choice>
                <input name="option_groups[{{ $gi }}][choices][{{ $ci }}][name]" value="{{ $choice['name'] ?? '' }}" placeholder="اسم الخيار مثل: كامل باكج" class="w-full rounded-xl border px-3 py-2 text-xs">
                <input type="number" step="0.5" min="0" name="option_groups[{{ $gi }}][choices][{{ $ci }}][price]" value="{{ $choice['price'] ?? 0 }}" class="w-full rounded-xl border px-3 py-2 text-xs font-mono" placeholder="السعر +">
                <button type="button" class="text-rose-600 text-xs font-bold" data-remove-choice>حذف</button>
            </div>
        @endforeach
    </div>
    <div class="flex items-center justify-between">
        <button type="button" class="text-xs font-bold text-primary" data-add-choice>+ خيار</button>
        <button type="button" class="text-xs font-bold text-rose-700" data-remove-group>حذف القسم</button>
    </div>
</div>
