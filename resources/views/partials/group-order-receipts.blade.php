@php
    $group = $order->groupOrder;
@endphp
@if($group)
    <section class="{{ ($tone ?? 'admin') === 'admin' ? 'admin-card' : 'rounded-2xl bg-white border border-slate-100 p-5 shadow-xs' }}">
        <h2 class="flex items-center gap-1.5 font-bold">
            <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
            <span>الطلب الجماعي — مين طلب ومين دفع</span>
        </h2>
        <p class="mt-1 text-xs text-stone-500">فاتورة واحدة وتوصيل واحد. كل شخص دفع نصيب أكله.</p>
        <div class="mt-3 space-y-3">
            @foreach($group->members->sortByDesc('is_host') as $member)
                <div class="rounded-xl border border-slate-100 bg-stone-50/70 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-extrabold text-stone-900">
                                {{ $member->displayName() }}
                                @if($member->is_host)
                                    <span class="text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded-full mr-1">صاحب الطلب</span>
                                @endif
                            </p>
                            <p class="text-[11px] text-stone-500" dir="ltr">{{ $member->phone }}</p>
                        </div>
                        <div class="text-left">
                            <p class="font-mono text-sm font-black text-stone-900">{{ number_format((float) $member->total, 2) }} ₪</p>
                            <p class="text-[11px] text-stone-500">{{ $member->paymentLabel() }} · {{ $member->statusLabel() }}</p>
                        </div>
                    </div>
                    @if($member->items())
                        <ul class="mt-2 space-y-1 text-xs text-stone-700">
                            @foreach($member->items() as $line)
                                <li>{{ $line['qty'] ?? 1 }}× {{ $line['name'] ?? 'صنف' }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($member->transfer_receipt_path)
                        @php
                            $viewer = auth()->user();
                            $canSeeReceipt = $viewer && (
                                $viewer->isAdmin()
                                || $viewer->isPartner()
                                || (int) $group->host_user_id === (int) $viewer->id
                                || (int) $member->user_id === (int) $viewer->id
                            );
                        @endphp
                        @if($canSeeReceipt)
                            <a href="{{ route('group-orders.receipt', [$group, $member]) }}"
                               target="_blank" rel="noopener"
                               class="mt-2 inline-flex items-center gap-1 text-[11px] font-extrabold text-primary">
                                <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                <span>إشعار حوالة {{ $member->displayName() }}</span>
                            </a>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
