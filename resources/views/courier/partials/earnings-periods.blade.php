@php
    $periodRoute = $periodRoute ?? 'courier.wallet';
    $periods = [
        'today' => 'اليوم',
        'yesterday' => 'أمس',
        'week' => 'الأسبوع',
        'month' => 'الشهر',
        'all' => 'الكل',
    ];
@endphp
<nav class="cw-periods" aria-label="فترة الأرباح">
    @foreach($periods as $pKey => $pLabel)
        <a href="{{ route($periodRoute, ['period' => $pKey]) }}" class="cw-periods__btn {{ $period === $pKey ? 'is-active' : '' }}">
            {{ $pLabel }}
        </a>
    @endforeach
</nav>
