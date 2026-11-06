@php
    $printUrl = $printUrl ?? null;
    $label = $label ?? 'طباعة الطلب';
    $class = $class ?? 'admin-btn admin-btn--secondary text-xs';
@endphp
@if($printUrl)
    <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="{{ $class }}" title="طباعة الطلب على الطابعة">
        <span class="material-symbols-outlined text-[16px]">print</span>
        <span>{{ $label }}</span>
    </a>
@endif
