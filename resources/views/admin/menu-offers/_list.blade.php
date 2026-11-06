@php
    $offers = $offers ?? collect();
    $createRoute = $createRoute ?? null;
    $editRouteName = $editRouteName ?? null;
    $destroyRouteName = $destroyRouteName ?? null;
    $restaurant = $restaurant ?? null;
@endphp
<section class="admin-card mb-4">
    <div class="flex items-center justify-between gap-2 flex-wrap mb-3">
        <div>
            <h2 class="text-base font-extrabold">العروض</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">صنف واحد أو صنفان بسعر خاص يظهران في أول صفحة المكان.</p>
        </div>
        @if($createRoute)
            <a href="{{ $createRoute }}" class="admin-btn admin-btn--primary text-xs">إضافة عرض</a>
        @endif
    </div>
    @forelse($offers as $offer)
        <div class="flex items-center justify-between gap-3 py-2 border-t border-slate-100 first:border-0 first:pt-0">
            <div class="min-w-0">
                <div class="font-bold text-slate-900 truncate">{{ $offer->name }}</div>
                <div class="text-xs text-on-surface-variant mt-0.5">{{ $offer->itemsLabel() }} · {{ number_format($offer->price, 2) }} ₪ بدل {{ number_format($offer->originalPrice(), 2) }} ₪</div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @include('admin.partials.pill', [
                    'status' => $offer->is_active ? 'approved' : 'cancelled',
                    'label' => $offer->is_active ? 'ظاهر' : 'مخفي',
                ])
                @if($editRouteName)
                    <a class="admin-action-btn admin-action-btn--outline admin-action-btn--sm" href="{{ $restaurant ? route($editRouteName, [$restaurant, $offer]) : route($editRouteName, $offer) }}">تعديل</a>
                @endif
                @if($destroyRouteName)
                    <form method="POST" action="{{ $restaurant ? route($destroyRouteName, [$restaurant, $offer]) : route($destroyRouteName, $offer) }}" onsubmit="return confirm('حذف هذا العرض؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-action-btn admin-action-btn--danger admin-action-btn--sm">حذف</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-on-surface-variant">ما في عروض حالياً. أضف عرضاً ليظهر في أول صفحة المطعم أو الكافي.</p>
    @endforelse
</section>
