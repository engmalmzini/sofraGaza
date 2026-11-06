@php
    $workingDaysField = $workingDaysField ?? 'working_days';
    $defaultDays = $defaultDays ?? \App\Support\OperatingHours::allDayNumbers();
    $selectedDays = old($workingDaysField, $defaultDays);
    $selectedDays = array_map('intval', is_array($selectedDays) ? $selectedDays : []);
    $defaultOpens = old('opens_at', $defaultOpens ?? '09:00');
    $defaultCloses = old('closes_at', $defaultCloses ?? '23:00');
    $savedDayHours = $savedDayHours ?? [];
    $customEnabled = (bool) old('custom_day_hours', $customEnabled ?? false);
    $opensRequired = $opensRequired ?? true;
    $variant = $variant ?? 'admin';
    $hintClass = $variant === 'auth' ? 'mt-1 text-[11px] text-on-surface-variant' : 'mt-1 text-[11px] text-on-surface-variant';
@endphp
<div class="{{ $wrapperClass ?? 'md:col-span-2 space-y-3' }}" data-hours-editor>
    @include('partials.working-days-picker', [
        'workingDaysField' => $workingDaysField,
        'defaultDays' => $selectedDays,
        'wrapperClass' => '',
        'labelClass' => $labelClass ?? 'mb-2 block text-sm font-bold',
        'errorClass' => $errorClass ?? null,
        'dayCheckboxClass' => 'js-working-day',
    ])

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-bold">يفتح الساعة</label>
            <input type="time" name="opens_at" value="{{ $defaultOpens }}" @required($opensRequired) aria-label="يفتح الساعة" data-hours-default-open class="w-full">
            <p class="{{ $hintClass }}">هذا الوقت لكل الأيام المختارة، إلا إذا حددت يوماً بيومه.</p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">يغلق الساعة</label>
            <input type="time" name="closes_at" value="{{ $defaultCloses }}" @required($opensRequired) aria-label="يغلق الساعة" data-hours-default-close class="w-full">
            <p class="{{ $hintClass }}">إذا كان الإغلاق بعد منتصف الليل (مثلاً 01:00) اختر الساعة كما هي.</p>
        </div>
    </div>
    @foreach(['opens_at', 'closes_at'] as $timeField)
        @error($timeField)
            <p class="{{ $errorClass ?? 'mt-1 text-sm text-rose-600' }}">{{ $message }}</p>
        @enderror
    @endforeach

    <label class="flex items-start gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 cursor-pointer">
        <input type="checkbox" name="custom_day_hours" value="1" class="mt-0.5 rounded text-primary focus:ring-primary" data-custom-day-hours @checked($customEnabled)>
        <span>
            <strong class="block text-sm font-bold">تحديد ساعات مختلفة حسب اليوم</strong>
            <span class="block text-[11px] text-on-surface-variant mt-0.5">مثلاً الجمعة من 8 صباحاً إلى 3 عصراً، وباقي الأيام نفس الوقت أعلاه.</span>
        </span>
    </label>

    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 space-y-2" data-day-hours-grid @if(! $customEnabled) hidden @endif>
        <p class="text-[11px] font-bold text-stone-700">ساعات كل يوم</p>
        @foreach(\App\Support\OperatingHours::DAYS as $num => $label)
            @php
                $row = $savedDayHours[$num] ?? $savedDayHours[(string) $num] ?? [];
                $rowOpen = old("day_hours.$num.opens_at", $row['opens_at'] ?? $defaultOpens);
                $rowClose = old("day_hours.$num.closes_at", $row['closes_at'] ?? $defaultCloses);
                $dayOn = in_array($num, $selectedDays, true);
            @endphp
            <div class="grid grid-cols-[5.5rem_1fr_1fr] sm:grid-cols-[7rem_1fr_1fr] items-center gap-2" data-day-hours-row="{{ $num }}" @if(! $dayOn) hidden @endif>
                <span class="text-xs font-bold text-stone-800">{{ $label }}</span>
                <input type="time" name="day_hours[{{ $num }}][opens_at]" value="{{ $rowOpen }}" aria-label="يفتح {{ $label }}" class="w-full !h-10 text-xs" @disabled(! $customEnabled || ! $dayOn)>
                <input type="time" name="day_hours[{{ $num }}][closes_at]" value="{{ $rowClose }}" aria-label="يغلق {{ $label }}" class="w-full !h-10 text-xs" @disabled(! $customEnabled || ! $dayOn)>
            </div>
            @error("day_hours.$num.opens_at")
                <p class="{{ $errorClass ?? 'mt-1 text-sm text-rose-600' }}">{{ $message }}</p>
            @enderror
            @error("day_hours.$num.closes_at")
                <p class="{{ $errorClass ?? 'mt-1 text-sm text-rose-600' }}">{{ $message }}</p>
            @enderror
        @endforeach
    </div>
</div>
@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-hours-editor]').forEach((root) => {
            const toggle = root.querySelector('[data-custom-day-hours]');
            const grid = root.querySelector('[data-day-hours-grid]');
            const defaultOpen = root.querySelector('[data-hours-default-open]');
            const defaultClose = root.querySelector('[data-hours-default-close]');
            const dayBoxes = [...root.querySelectorAll('.js-working-day')];

            const sync = () => {
                const custom = !!(toggle && toggle.checked);
                if (grid) grid.hidden = !custom;
                dayBoxes.forEach((box) => {
                    const row = root.querySelector('[data-day-hours-row="' + box.value + '"]');
                    if (!row) return;
                    const on = box.checked;
                    row.hidden = !on;
                    row.querySelectorAll('input[type="time"]').forEach((input) => {
                        input.disabled = !custom || !on;
                        if (custom && on) {
                            if (!input.value && input.name.includes('opens_at') && defaultOpen) input.value = defaultOpen.value;
                            if (!input.value && input.name.includes('closes_at') && defaultClose) input.value = defaultClose.value;
                        }
                    });
                });
            };

            toggle?.addEventListener('change', () => {
                if (toggle.checked) {
                    root.querySelectorAll('[data-day-hours-row] input[type="time"]').forEach((input) => {
                        if (input.value) return;
                        if (input.name.includes('opens_at') && defaultOpen) input.value = defaultOpen.value;
                        if (input.name.includes('closes_at') && defaultClose) input.value = defaultClose.value;
                    });
                }
                sync();
            });
            dayBoxes.forEach((box) => box.addEventListener('change', sync));
            sync();
        });
    });
</script>
@endonce
