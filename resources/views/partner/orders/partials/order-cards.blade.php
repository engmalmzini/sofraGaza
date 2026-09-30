@forelse($orders as $order)
    <div class="admin-card border {{ in_array($order->status, ['pending_confirmation', 'confirmed']) ? 'border-primary/40 bg-primary/[0.02] shadow-sm' : 'border-outline/10' }} p-4 rounded-2xl relative transition-all" data-order-card="{{ $order->id }}">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-outline/10 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-base font-extrabold text-primary">#{{ $order->id }}</span>
                <span class="admin-pill {{ in_array($order->status, ['pending_confirmation', 'confirmed']) ? 'admin-pill--wait' : ($order->status === 'preparing' ? 'admin-pill--warn' : 'admin-pill--ok') }}">
                    {{ $order->statusLabel() }}
                </span>
                @if($order->isPrepared())
                    <span class="admin-pill bg-emerald-100 text-emerald-800 text-[11px] font-bold">جاهز للاستلام 🍳</span>
                @endif
                @if($order->isPaidWithWallet())
                    <span class="admin-pill admin-pill--ok text-[11px]">مدفوع محفظة</span>
                @endif
            </div>
            <div class="text-xs text-on-surface-variant flex items-center gap-1 font-medium">
                <span class="material-symbols-outlined text-[15px]">schedule</span>
                <span>{{ $order->created_at?->diffForHumans() }}</span>
            </div>
        </div>

        <div class="my-3 space-y-2">
            <div class="flex items-center justify-between text-sm">
                <div class="font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">person</span>
                    <span>الزبون: {{ $order->user->name }}</span>
                </div>
                <span class="text-xs font-semibold text-on-surface-variant" dir="ltr">{{ $order->phone }}</span>
            </div>

            @if($order->courier)
                <div class="text-xs text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg flex items-center justify-between">
                    <span>المندوب: <strong>{{ $order->courier->name }}</strong></span>
                    <span dir="ltr">{{ $order->courier->phone }}</span>
                </div>
            @endif

            {{-- Items & Notes List --}}
            <div class="bg-surface-container-low/70 rounded-xl p-3 space-y-2 mt-2">
                <div class="text-xs font-bold text-slate-700 flex items-center justify-between">
                    <span>الأصناف المطلوبة ({{ $order->items->sum('quantity') }}):</span>
                    <span class="font-bold text-primary">{{ number_format($order->foodTotal(), 2) }} ₪</span>
                </div>

                <ul class="space-y-1.5 text-xs text-stone-900 divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <li class="pt-1.5 first:pt-0">
                            <div class="flex justify-between font-semibold">
                                <span>{{ $item->quantity }}× {{ $item->name }}</span>
                                <span>{{ number_format($item->line_total, 2) }} ₪</span>
                            </div>
                            @if($item->notes)
                                <div class="mt-1 p-1.5 rounded bg-rose-50 border border-rose-200 text-rose-900 font-bold text-[11px] flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-rose-600">error</span>
                                    <span>ملاحظة التحضير: {{ $item->notes }}</span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if($order->notes)
                    <div class="mt-2 pt-2 border-t border-slate-200/60 text-[11px] text-slate-600">
                        <strong>ملاحظات عامة:</strong> {{ $order->notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-outline/10">
            <div class="text-xs text-on-surface-variant">
                حساب الوجبات: <strong class="text-sm font-extrabold text-primary">{{ number_format($order->foodTotal(), 2) }} ₪</strong>
            </div>

            <div class="flex items-center gap-2">
                @if($order->status === 'confirmed')
                    <form method="POST" action="{{ route('partner.orders.update', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="admin-btn admin-btn--primary text-xs py-1 px-3">
                            <span class="material-symbols-outlined text-[15px]">cooking</span>
                            <span>بدء التحضير</span>
                        </button>
                    </form>
                @elseif($order->status === 'preparing')
                    @if(! $order->isPrepared())
                        <form method="POST" action="{{ route('partner.orders.prepared', $order) }}">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--primary text-xs py-1 px-3 bg-emerald-600 hover:bg-emerald-700">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                <span>تم تجهيز الطلب</span>
                            </button>
                        </form>
                    @else
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">task_alt</span>
                            <span>جاهز للاستلام</span>
                        </span>
                    @endif
                @endif
                <a href="{{ route('partner.orders.show', $order) }}" class="admin-btn admin-btn--ghost text-xs py-1 px-3">
                    عرض الفاتورة
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-8 text-on-surface-variant bg-surface-container-low/50 rounded-2xl border border-dashed border-outline/20">
        <span class="material-symbols-outlined text-4xl text-outline/60 block mb-1">receipt_long</span>
        <p class="font-bold text-sm">لا توجد طلبات جارية حالياً.</p>
        <p class="text-xs text-on-surface-variant mt-1">أي طلب جديد يصل للمطعم يظهر هنا مباشرة مع صوت تنبيه دون الحاجة لتحديث الصفحة.</p>
    </div>
@endforelse
