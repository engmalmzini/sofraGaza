@php
    $pickup = $order->restaurant?->address ?: $order->restaurant?->areaLabel();
    $dropoff = $order->address_details;
    $status = $order->status;
@endphp
<article class="courier-card">
    <a class="courier-card__body" href="{{ route('courier.orders.show', $order) }}">
        <div class="courier-card__head">
            <span class="courier-pill courier-pill--{{ $status }}">{{ $order->statusLabel() }}</span>
            <span class="courier-card__id">#{{ $order->id }}</span>
        </div>
        <h2>{{ $order->restaurant->name }}</h2>
        <ol class="courier-route">
            <li>
                <span>استلام</span>
                <strong>{{ $pickup }}</strong>
            </li>
            <li>
                <span>تسليم</span>
                <strong>{{ $dropoff }}</strong>
            </li>
        </ol>
        <div class="courier-card__meta">
            <span>{{ $order->user->name }}</span>
            <span>{{ $order->items->sum('quantity') }} أصناف</span>
            <b>{{ number_format($order->total, 2) }} <span class="ils">₪</span></b>
        </div>
    </a>
    <div class="courier-card__actions">
        <a class="courier-btn courier-btn--ghost" href="tel:{{ $order->phone }}">
            <span class="material-symbols-outlined">call</span>
            اتصال
        </a>
        @if($order->isAvailableForCourier())
            <form method="POST" action="{{ route('courier.orders.claim', $order) }}">
                @csrf
                <button class="courier-btn">أخذ التوصيل</button>
            </form>
        @elseif($order->courier_id === auth()->id() && $order->status === 'delivering')
            <form method="POST" action="{{ route('courier.orders.complete', $order) }}">
                @csrf
                <button class="courier-btn courier-btn--ok">تم التسليم</button>
            </form>
        @else
            <a class="courier-btn courier-btn--ghost" href="{{ route('courier.orders.show', $order) }}">التفاصيل</a>
        @endif
    </div>
</article>
