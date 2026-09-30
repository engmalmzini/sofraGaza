@forelse($orders as $order)
    <tr class="admin-click-row" data-href="{{ route('admin.orders.show', $order) }}" role="link" tabindex="0">
        <td><a class="font-bold text-primary hover:underline" href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a></td>
        <td>
            <div class="font-bold text-slate-900">{{ $order->user->name }}</div>
            <div class="text-xs text-on-surface-variant font-mono" dir="ltr">{{ $order->phone }}</div>
        </td>
        <td><span class="font-semibold text-slate-800">{{ $order->restaurant->name }}</span></td>
        <td><span class="font-mono font-bold text-slate-900">{{ number_format($order->total, 2) }}</span> <span class="ils">₪</span></td>
        <td><span class="text-xs text-slate-600">{{ $order->type === 'redemption' ? 'استبدال نقاط' : 'شراء' }}</span></td>
        <td>
            @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
        </td>
        <td class="whitespace-nowrap">
            <div class="admin-table-actions">
                <a class="admin-action-btn admin-action-btn--primary admin-action-btn--sm" href="{{ route('admin.orders.show', $order) }}">
                    <span class="material-symbols-outlined">visibility</span>
                    <span>عرض</span>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center py-6 text-slate-400">لا توجد طلبات مطابقة.</td></tr>
@endforelse
