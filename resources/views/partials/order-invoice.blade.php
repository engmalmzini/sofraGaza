@php
    $tone = $tone ?? 'admin';
@endphp
<section class="{{ $tone === 'admin' ? 'admin-card' : 'rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs' }} sg-invoice">
    <div class="sg-invoice__head">
        <div>
            <p class="sg-invoice__kicker">فاتورة الطلب</p>
            <h2>طلب #{{ $order->id }}</h2>
            <p class="sg-invoice__meta">{{ $order->restaurant->name }} · {{ $order->type === 'redemption' ? 'استبدال نقاط' : 'شراء' }}</p>
        </div>
        <div class="sg-invoice__when">
            <span>{{ $order->created_at?->format('Y/m/d') }}</span>
            <span>{{ $order->created_at?->format('H:i') }}</span>
        </div>
    </div>

    <div class="{{ $tone === 'admin' ? 'admin-table-wrap ' : '' }}sg-invoice__table-wrap">
        <table class="sg-invoice__table">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>سعر الوحدة</th>
                    <th>الكمية</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->name }}</strong>
                        </td>
                        <td>{{ number_format($item->price, 2) }} <span class="ils">₪</span></td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->line_total, 2) }} <span class="ils">₪</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">لا توجد أصناف مسجّلة على هذا الطلب.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="sg-invoice__totals">
        <div><span>مجموع الأصناف</span><span>{{ number_format($order->subtotal, 2) }} <span class="ils">₪</span></span></div>
        @if((float) $order->discount_amount > 0)
            <div class="sg-invoice__discount"><span>خصم العضوية {{ $order->discount_percent }}%</span><span>− {{ number_format($order->discount_amount, 2) }} <span class="ils">₪</span></span></div>
        @endif
        <div><span>التوصيل</span><span>@if((float) $order->delivery_fee > 0){{ number_format($order->delivery_fee, 2) }} <span class="ils">₪</span>@else مجاني @endif</span></div>
        @if($order->type === 'redemption')
            <div><span>النقاط المستخدمة</span><span>{{ number_format($order->points_spent) }}</span></div>
        @endif
        <div class="sg-invoice__grand"><span>الإجمالي المستحق</span><span>{{ number_format($order->total, 2) }} <span class="ils">₪</span></span></div>
    </div>
</section>
