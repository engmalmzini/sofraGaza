@php
    $compare = $compare ?? null;
    $invert = $invert ?? false;
@endphp
@if($compare)
    @php
        $positive = $invert ? ! $compare['up'] : $compare['up'];
        $arrow = $compare['up'] ? 'arrow_upward' : 'arrow_downward';
        $sign = $compare['diff'] > 0 ? '+' : '';
    @endphp
    <span class="finance-compare {{ $positive ? 'is-good' : 'is-bad' }}" title="{{ $label ?? '' }}">
        <span class="material-symbols-outlined">{{ $arrow }}</span>
        <span>{{ $sign }}{{ number_format($compare['percent'], 1) }}%</span>
        <small>{{ $label ?? 'مقابل الفترة السابقة' }}</small>
    </span>
@endif
