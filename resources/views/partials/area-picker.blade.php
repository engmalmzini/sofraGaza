@php
    $current = $deliveryArea ?? (config('brand.areas')[0] ?? ['key' => 'الرمال', 'label' => 'غزة • حي الرمال']);
    $areas = $deliveryAreas ?? config('brand.areas', []);
    $fullLabel = $current['label'] ?? 'حي الرمال';
    $shortLabel = str_contains($fullLabel, '•') ? trim(explode('•', $fullLabel)[1] ?? $fullLabel) : $fullLabel;
@endphp
<details class="area-picker relative">
    <summary class="header-location" aria-label="التوصيل إلى {{ $fullLabel }}">
        <span class="material-symbols-outlined header-location__pin">location_on</span>
        <span class="header-location__value">{{ $shortLabel }}</span>
        <span class="material-symbols-outlined header-location__chevron">expand_more</span>
    </summary>
    <div class="area-picker__menu">
        <p class="area-picker__title">اختر منطقة التوصيل</p>
        @foreach($areas as $area)
            <form method="POST" action="{{ route('delivery-area.update') }}">
                @csrf
                <input type="hidden" name="area" value="{{ $area['key'] }}">
                <button type="submit" class="area-picker__option {{ ($current['key'] ?? '') === $area['key'] ? 'is-active' : '' }}">
                    <span class="material-symbols-outlined">location_on</span>
                    <span>{{ $area['label'] }}</span>
                    @if(($current['key'] ?? '') === $area['key'])
                        <span class="material-symbols-outlined area-picker__check">check</span>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</details>
