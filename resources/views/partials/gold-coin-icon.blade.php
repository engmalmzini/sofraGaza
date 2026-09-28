@php
    $size = $class ?? 'w-6 h-6';
    $uid = $id ?? 'gc_' . \Illuminate\Support\Str::random(6);
@endphp
<svg class="{{ $size }} shrink-0 inline-block align-middle select-none transition-transform hover:scale-105" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="نقاط الولاء">
    <defs>
        <!-- Luminous Warm Gold Gradient -->
        <linearGradient id="{{ $uid }}_coin" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FBBF24"/>
            <stop offset="100%" stop-color="#D97706"/>
        </linearGradient>
    </defs>

    <!-- Outer Gold Disc -->
    <circle cx="12" cy="12" r="10" fill="url(#{{ $uid }}_coin)"/>

    <!-- Elegant Inner Border -->
    <circle cx="12" cy="12" r="8.2" stroke="#FEF3C7" stroke-width="0.9" stroke-opacity="0.7"/>

    <!-- Crisp Center Star -->
    <path d="M12 6.3L13.7 10.15L17.9 10.55L14.7 13.35L15.65 17.5L12 15.25L8.35 17.5L9.3 13.35L6.1 10.55L10.3 10.15L12 6.3Z" fill="#FFFFFF"/>
</svg>
