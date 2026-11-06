@php
    $choices = $choices ?? [];
    $class = $class ?? 'text-[11px] text-stone-500 leading-5';
@endphp
@if($choices !== [])
    <div class="{{ $class }}">
        @foreach($choices as $choice)
            <div>
                @if(!empty($choice['group']))
                    <span class="font-bold">{{ $choice['group'] }}:</span>
                @endif
                {{ $choice['name'] ?? '' }}
                @if((float) ($choice['price'] ?? 0) > 0)
                    <span class="font-mono">(+{{ number_format((float) $choice['price'], 0) }} ₪)</span>
                @endif
            </div>
        @endforeach
    </div>
@endif
