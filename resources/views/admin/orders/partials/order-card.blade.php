@php
    $cardContext = $cardContext ?? 'admin';
    $showRouteName = $showRouteName ?? 'admin.orders.show';
    $readonly = $readonly ?? false;
    $canDrag = ! $readonly && ! in_array($order->status, ['delivered', 'cancelled', 'rejected'], true);
    $amount = $cardContext === 'partner' ? $order->foodTotal() : $order->total;
@endphp

<article
    class="admin-order-card {{ $canDrag ? 'admin-order-card--draggable' : '' }}"
    data-order-card="{{ $order->id }}"
    data-order-status="{{ $order->status }}"
    data-order-column="{{ $order->boardColumn() }}"
>
    <div class="admin-order-card__header">
        @if($canDrag)
            <span class="admin-order-card__drag" data-order-drag-handle title="اسحب لنقل الطلب" role="button" tabindex="0">
                <span class="material-symbols-outlined">drag_indicator</span>
            </span>
        @endif
        <a href="{{ route($showRouteName, $order) }}" class="admin-order-card__id hover:underline">
            #{{ $order->id }}
            @if($order->group_order_id)
                <span class="admin-order-card__group">جماعي</span>
            @endif
        </a>
        <span class="admin-order-card__time">
            {{ $order->created_at->diffForHumans(null, true) }}
        </span>
        <span class="admin-order-card__total">
            {{ number_format($amount, 2) }} <span class="ils">₪</span>
        </span>
    </div>

    @if($cardContext !== 'partner')
        <div class="admin-order-card__venue">
            <span class="material-symbols-outlined">storefront</span>
            <span class="truncate">{{ $order->restaurant->name ?? 'مطعم' }}</span>
        </div>
    @else
        <div class="admin-order-card__venue">
            <span class="material-symbols-outlined">person</span>
            <span class="truncate">{{ $order->user->name ?? 'عميل' }}</span>
        </div>
    @endif

    <div class="admin-order-card__customer">
        <span class="truncate font-semibold">
            @if($cardContext === 'partner')
                {{ $order->statusLabel() }}
            @else
                {{ $order->user->name ?? 'عميل' }}
            @endif
        </span>
        @if($cardContext !== 'customer')
            <span class="admin-order-card__phone">{{ $order->phone ?? ($order->user->phone ?? '—') }}</span>
        @endif
    </div>

    @if($cardContext === 'partner' && $order->items && $order->items->isNotEmpty())
        <ul class="admin-order-card__items">
            @foreach($order->items->take(3) as $item)
                <li>
                    <span>{{ $item->quantity }}× {{ $item->name }}</span>
                    @if($item->notes)
                        <span class="admin-order-card__note">{{ $item->notes }}</span>
                    @endif
                </li>
            @endforeach
            @if($order->items->count() > 3)
                <li class="text-slate-400">+{{ $order->items->count() - 3 }} أصناف أخرى</li>
            @endif
        </ul>
    @endif

    @if($order->status !== 'delivered' && $cardContext !== 'customer')
        @if($order->courier)
            <div class="admin-order-card__courier">
                <span class="material-symbols-outlined">two_wheeler</span>
                <span class="truncate">المندوب: {{ $order->courier->name }}</span>
            </div>
        @else
            <div class="admin-order-card__courier admin-order-card__courier--empty">
                <span class="material-symbols-outlined">two_wheeler</span>
                <span>لم يُعيّن مندوب</span>
            </div>
        @endif
    @endif

    <div class="admin-order-card__footer">
        <span class="admin-order-card__pay">
            @if($cardContext === 'partner')
                حساب الوجبات:
            @elseif($order->payment_method === 'wallet')
                💳 محفظة
            @else
                💵 كاش
            @endif
        </span>
        <a href="{{ route($showRouteName, $order) }}" class="admin-order-card__btn">
            <span>التفاصيل</span>
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
        </a>
    </div>

    @if($cardContext === 'partner' && $order->status === 'preparing' && ! $order->isPrepared())
        <form method="POST" action="{{ route('partner.orders.prepared', $order) }}" class="admin-order-card__action">
            @csrf
            <button type="submit" class="admin-btn admin-btn--primary text-xs py-1 px-3 w-full justify-center bg-emerald-600 hover:bg-emerald-700">
                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                <span>تم تجهيز الطلب</span>
            </button>
        </form>
    @elseif($cardContext === 'partner' && $order->isPrepared() && $order->status === 'preparing')
        <span class="admin-order-card__ready">جاهز للاستلام 🍳</span>
    @endif
</article>
