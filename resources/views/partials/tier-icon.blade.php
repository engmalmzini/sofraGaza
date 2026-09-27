@php
    $tierKey = is_array($tier ?? null) ? ($tier['key'] ?? 'starter') : ($tier ?? 'starter');
    $tierKey = strtolower((string) $tierKey);
    $sizeClass = $class ?? 'w-8 h-8';
@endphp

@if($tierKey === 'starter')
    {{-- المستوى المبتدئ: درع ونجمة باللونين الأسود والبرتقالي الغامق فقط --}}
    <svg class="{{ $sizeClass }} shrink-0 inline-block align-middle select-none" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="المستوى المبتدئ">
        <circle cx="32" cy="32" r="28" fill="#191C1E" stroke="#A33900" stroke-width="2.5"/>
        <circle cx="32" cy="32" r="23" fill="#111827" stroke="#A33900" stroke-width="1" stroke-dasharray="2 2" opacity="0.6"/>
        {{-- 8-Point Compass Star in Dark Orange & White --}}
        <g>
            <polygon points="32,32 32,15 29,29" fill="#FFFFFF"/>
            <polygon points="32,32 32,15 35,29" fill="#A33900"/>
            <polygon points="32,32 49,32 35,29" fill="#FFFFFF"/>
            <polygon points="32,32 49,32 35,35" fill="#A33900"/>
            <polygon points="32,32 32,49 35,35" fill="#FFFFFF"/>
            <polygon points="32,32 32,49 29,35" fill="#A33900"/>
            <polygon points="32,32 15,32 29,35" fill="#FFFFFF"/>
            <polygon points="32,32 15,32 29,29" fill="#A33900"/>
            {{-- Diagonal Points --}}
            <polygon points="32,32 44,20 35,29" fill="#A33900"/>
            <polygon points="32,32 20,20 29,29" fill="#A33900"/>
            <polygon points="32,32 44,44 35,35" fill="#A33900"/>
            <polygon points="32,32 20,44 29,35" fill="#A33900"/>
            <circle cx="32" cy="32" r="3.5" fill="#FFFFFF"/>
            <circle cx="32" cy="32" r="2" fill="#A33900"/>
        </g>
    </svg>

@elseif($tierKey === 'bronze')
    {{-- المستوى البرونزي: درع وكأس التتويج باللونين الأسود والبرتقالي الغامق فقط --}}
    <svg class="{{ $sizeClass }} shrink-0 inline-block align-middle select-none" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="المستوى البرونزي">
        <circle cx="32" cy="32" r="28" fill="#191C1E" stroke="#A33900" stroke-width="2.5"/>
        <circle cx="32" cy="32" r="23" fill="#111827"/>
        {{-- Laurel Leaves --}}
        <g fill="#A33900" opacity="0.9">
            <path d="M19 35 C17 31 18 26 22 22 C22 26 20 31 19 35 Z"/>
            <path d="M20 27 C17 24 18 20 23 18 C23 22 21 25 20 27 Z"/>
            <path d="M45 35 C47 31 46 26 42 22 C42 26 44 31 45 35 Z"/>
            <path d="M44 27 C47 24 46 20 41 18 C41 22 43 25 44 27 Z"/>
        </g>
        {{-- Trophy Cup --}}
        <g>
            <path d="M24 22 L40 22 C40 31 35 34 32 34 C29 34 24 31 24 22 Z" fill="#A33900" stroke="#FFFFFF" stroke-width="1"/>
            <path d="M20 24 C20 28 24 29 24 29 C24 27 21 26 21 24 Z" fill="#FFFFFF"/>
            <path d="M44 24 C44 28 40 29 40 29 C40 27 43 26 43 24 Z" fill="#FFFFFF"/>
            <rect x="30" y="34" width="4" height="6" fill="#A33900"/>
            <rect x="25" y="40" width="14" height="4" rx="2" fill="#FFFFFF"/>
            <circle cx="32" cy="27" r="2" fill="#FFFFFF"/>
        </g>
    </svg>

@elseif($tierKey === 'silver')
    {{-- المستوى الفضي: نجمة التميز باللونين الأسود والبرتقالي الغامق فقط --}}
    <svg class="{{ $sizeClass }} shrink-0 inline-block align-middle select-none" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="المستوى الفضي">
        <circle cx="32" cy="32" r="28" fill="#191C1E" stroke="#A33900" stroke-width="2.5"/>
        <circle cx="32" cy="32" r="23" fill="#111827"/>
        <circle cx="32" cy="32" r="21" fill="none" stroke="#FFFFFF" stroke-width="0.8" opacity="0.4"/>
        {{-- 5-Point Star in Dark Orange & White --}}
        <g>
            <polygon points="32,17 35.5,27.5 46.5,27.5 37.5,34 41,45 32,38.5 23,45 26.5,34 17.5,27.5 28.5,27.5" fill="#A33900" stroke="#FFFFFF" stroke-width="1.2"/>
            <polygon points="32,17 32,38.5 41,45 37.5,34 46.5,27.5 35.5,27.5" fill="#FFFFFF" opacity="0.35"/>
            <circle cx="32" cy="32" r="3.5" fill="#FFFFFF"/>
        </g>
    </svg>

@elseif($tierKey === 'gold')
    {{-- المستوى الذهبي: التاج الملكي باللونين الأسود والبرتقالي الغامق فقط --}}
    <svg class="{{ $sizeClass }} shrink-0 inline-block align-middle select-none" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="المستوى الذهبي">
        <circle cx="32" cy="32" r="28" fill="#191C1E" stroke="#A33900" stroke-width="2.5"/>
        <circle cx="32" cy="32" r="23" fill="#111827"/>
        {{-- Imperial Crown in Dark Orange & White --}}
        <g>
            <path d="M19 40 L21 25 L27 32 L32 20 L37 32 L43 25 L45 40 Z" fill="#A33900" stroke="#FFFFFF" stroke-width="1.2"/>
            <circle cx="21" cy="24" r="2" fill="#FFFFFF"/>
            <circle cx="32" cy="19" r="2.5" fill="#FFFFFF"/>
            <circle cx="43" cy="24" r="2" fill="#FFFFFF"/>
            <rect x="20" y="39" width="24" height="4" rx="1.5" fill="#FFFFFF"/>
            <circle cx="32" cy="34" r="2" fill="#FFFFFF"/>
        </g>
    </svg>

@elseif($tierKey === 'platinum')
    {{-- المستوى البلاتيني: الجوهرة وشعار VIP باللونين الأسود والبرتقالي الغامق فقط --}}
    <svg class="{{ $sizeClass }} shrink-0 inline-block align-middle select-none" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="المستوى البلاتيني VIP">
        <circle cx="32" cy="32" r="28" fill="#191C1E" stroke="#A33900" stroke-width="2.5"/>
        <circle cx="32" cy="32" r="23" fill="#111827"/>
        {{-- Faceted Diamond --}}
        <g>
            <polygon points="26,19 38,19 44,25 32,39 20,25" fill="#A33900" stroke="#FFFFFF" stroke-width="1.2"/>
            <polygon points="26,19 38,19 32,25" fill="#FFFFFF" opacity="0.35"/>
            <polygon points="20,25 26,19 32,25" fill="#FFFFFF" opacity="0.2"/>
            <polygon points="38,19 44,25 32,25" fill="#FFFFFF" opacity="0.45"/>
            <polygon points="20,25 32,39 32,25" fill="#FFFFFF" opacity="0.15"/>
            <polygon points="44,25 32,39 32,25" fill="#FFFFFF" opacity="0.3"/>
        </g>
        {{-- VIP Pill --}}
        <g>
            <rect x="22" y="42" width="20" height="8" rx="4" fill="#A33900" stroke="#FFFFFF" stroke-width="0.8"/>
            <text x="32" y="48.5" text-anchor="middle" font-size="6" font-weight="900" fill="#FFFFFF" letter-spacing="1" font-family="system-ui, sans-serif">VIP</text>
        </g>
    </svg>

@else
    {{-- Fallback generic tier icon --}}
    <svg class="{{ $sizeClass }} shrink-0 inline-block align-middle" viewBox="0 0 24 24" fill="#A33900">
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
    </svg>
@endif
