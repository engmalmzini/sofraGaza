@if(! $item->isUnlimitedStock())
    @if($item->remainingStock() > 0)
        <span class="{{ $class ?? 'text-[11px] font-bold text-amber-700' }}">متبقي {{ $item->remainingStock() }}</span>
    @else
        <span class="{{ $class ?? 'text-[11px] font-bold text-rose-600' }}">نفدت الكمية</span>
    @endif
@endif
