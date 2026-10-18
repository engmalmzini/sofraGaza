@php
    $field = $field ?? 'working_days';
    $defaultDays = $defaultDays ?? \App\Support\OperatingHours::allDayNumbers();
    $selectedDays = old($field, $defaultDays);
    $selectedDays = array_map('intval', is_array($selectedDays) ? $selectedDays : []);
@endphp
<div class="{{ $wrapperClass ?? '' }}">
    <label class="{{ $labelClass ?? 'mb-2 block text-sm font-bold' }}">أيام العمل</label>
    <div class="flex flex-wrap gap-2">
        @foreach(\App\Support\OperatingHours::DAYS as $num => $label)
            <label class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-stone-800 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/10 has-[:checked]:text-primary">
                <input type="checkbox" name="{{ $field }}[]" value="{{ $num }}" class="rounded text-primary focus:ring-primary" @checked(in_array($num, $selectedDays, true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    @error($field)
        <p class="{{ $errorClass ?? 'mt-1 text-sm text-rose-600' }}">{{ $message }}</p>
    @enderror
</div>
