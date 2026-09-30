<article class="admin-order-card">
    <div class="admin-order-card__header">
        <a href="{{ route('admin.orders.show', $order) }}" class="admin-order-card__id hover:underline">
            #{{ $order->id }}
        </a>
        <span class="admin-order-card__time">
            {{ $order->created_at->diffForHumans(null, true) }}
        </span>
        <span class="admin-order-card__total">
            {{ number_format($order->total, 2) }} <span class="ils">₪</span>
        </span>
    </div>

    <div class="admin-order-card__venue">
        <span class="material-symbols-outlined">storefront</span>
        <span class="truncate">{{ $order->restaurant->name ?? 'مطعم' }}</span>
    </div>

    <div class="admin-order-card__customer">
        <span class="truncate font-semibold">{{ $order->user->name ?? 'عميل' }}</span>
        <span class="admin-order-card__phone">{{ $order->phone ?? ($order->user->phone ?? '—') }}</span>
    </div>

    @if($order->status !== 'delivered')
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
            @if($order->payment_method === 'wallet')
                💳 محفظة
            @else
                💵 كاش
            @endif
        </span>
        <a href="{{ route('admin.orders.show', $order) }}" class="admin-order-card__btn">
            <span>التفاصيل</span>
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
        </a>
    </div>
</article>
