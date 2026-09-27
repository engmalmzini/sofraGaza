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
                    <span class="area-picker__fee" style="margin-inline-start: auto; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <span style="font-size: 11px; padding: 0.15rem 0.45rem; border-radius: 9999px; background: rgba(0,0,0,0.06); font-weight: 600;">
                            {{ number_format($area['delivery_fee'] ?? \App\Models\Setting::deliveryFeeForArea($area['key']), 0) }} ₪
                        </span>
                        @if(($current['key'] ?? '') === $area['key'])
                            <span class="material-symbols-outlined area-picker__check">check</span>
                        @endif
                    </span>
                </button>
            </form>
        @endforeach
    </div>
</details>
