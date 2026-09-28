@forelse($orders as $order)
    <tr class="admin-click-row" data-href="{{ route('admin.orders.show', $order) }}" role="link" tabindex="0">
        <td><a class="font-bold text-primary" href="{{ route('admin.orders.show', $order) }}">{{ $order->id }}</a></td>
        <td>{{ $order->user->name }}<div class="text-xs text-on-surface-variant">{{ $order->phone }}</div></td>
        <td>{{ $order->restaurant->name }}</td>
        <td>{{ number_format($order->total, 2) }} <span class="ils">₪</span></td>
        <td>{{ $order->type === 'redemption' ? 'استبدال نقاط' : 'شراء' }}</td>
        <td>
            @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
            <span class="material-symbols-outlined admin-click-row__open" aria-hidden="true">chevron_left</span>
        </td>
    </tr>
@empty
    <tr><td colspan="6">لا توجد طلبات مطابقة.</td></tr>
@endforelse
