@php
    $tone = $tone ?? 'admin';
@endphp
<section class="{{ $tone === 'admin' ? 'admin-card' : 'rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs' }} sg-invoice">
    <div class="sg-invoice__head">
        <div>
            <p class="sg-invoice__kicker">فاتورة الطلب</p>
            <h2>طلب #{{ $order->id }}</h2>
            <p class="sg-invoice__meta">
                {{ $order->restaurant->name }} · {{ $order->type === 'redemption' ? 'استبدال نقاط' : 'شراء' }}
                @if($order->isGroupOrder())
                    · طلب جماعي
                @endif
            </p>
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
                @if($order->items->isEmpty())
                    <tr>
                        <td colspan="4">لا توجد أصناف مسجّلة على هذا الطلب.</td>
                    </tr>
                @else
                    @php
                        $invoiceGroups = $order->isGroupOrder()
                            ? $order->items->groupBy(fn ($item) => $item->ordered_by_name ?: 'غير معروف')
                            : collect(['' => $order->items]);
                    @endphp
                    @foreach($invoiceGroups as $memberName => $memberItems)
                        @if($order->isGroupOrder() && $memberName !== '')
                            <tr class="sg-invoice__member">
                                <td colspan="4">طلب {{ $memberName }}</td>
                            </tr>
                        @endif
                        @foreach($memberItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->notes)
                                        <div class="mt-1 text-xs text-primary font-medium flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                            <span>ملاحظات: {{ $item->notes }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ number_format($item->price, 2) }} <span class="ils">₪</span></td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->line_total, 2) }} <span class="ils">₪</span></td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="sg-invoice__totals">
        <div><span>مجموع الأصناف</span><span>{{ number_format($order->subtotal, 2) }} <span class="ils">₪</span></span></div>
        @if((float) $order->discount_amount > 0)
            <div class="sg-invoice__discount"><span>خصم العضوية {{ $order->discount_percent }}%</span><span>− {{ number_format($order->discount_amount, 2) }} <span class="ils">₪</span></span></div>
        @endif
        @if(($tone ?? '') !== 'partner')
            <div><span>التوصيل @if($order->delivery_area)({{ $order->deliveryAreaLabel() }})@endif</span><span>@if((float) $order->delivery_fee > 0){{ number_format($order->delivery_fee, 2) }} <span class="ils">₪</span>@else مجاني @endif</span></div>
        @endif
        @if($order->type === 'redemption')
            <div><span>النقاط المستخدمة</span><span>{{ number_format($order->points_spent) }}</span></div>
        @endif
        @if(($tone ?? '') === 'partner')
            <div class="sg-invoice__grand"><span>إجمالي حساب الوجبات</span><span>{{ number_format($order->foodTotal(), 2) }} <span class="ils">₪</span></span></div>
        @else
            <div class="sg-invoice__grand"><span>الإجمالي المستحق</span><span>{{ number_format($order->total, 2) }} <span class="ils">₪</span></span></div>
        @endif
    </div>
</section>
