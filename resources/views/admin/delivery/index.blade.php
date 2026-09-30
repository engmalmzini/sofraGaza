@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'إدارة التوصيل')

@section('content')
<div class="admin-metrics">
    <article class="admin-metric admin-metric--accent">
        <span>مندوب فاضي</span>
        <strong>{{ $idle->count() }}</strong>
    </article>
    <article class="admin-metric">
        <span>مندوب مشغول</span>
        <strong>{{ $busy->count() }}</strong>
    </article>
    <article class="admin-metric">
        <span>بانتظار مندوب</span>
        <strong>{{ $waitingCount }}</strong>
    </article>
    <article class="admin-metric">
        <span>قيد التوصيل / مسلّم اليوم</span>
        <strong>{{ $activeCount }} / {{ $doneCount }}</strong>
    </article>
    <article class="admin-metric {{ $pendingPayoutsCount > 0 ? 'border-amber-400 bg-amber-50' : '' }}">
        <span>طلبات سحب معلقة</span>
        <strong class="{{ $pendingPayoutsCount > 0 ? 'text-amber-600' : '' }}">{{ $pendingPayoutsCount }}</strong>
    </article>
</div>

@if($applications->isNotEmpty())
<section class="admin-card mb-4">
    <h2>طلبات انضمام المندوبين</h2>
    <p class="mt-1 text-sm text-on-surface-variant">قبول الطلب يرسل للمندوب إشعاراً برابط لوحته. الرفض ينهي الطلب.</p>
    <div class="mt-3 space-y-2">
        @foreach($applications as $application)
            <a class="admin-row" href="{{ route('admin.delivery.show', $application) }}">
                <span>{{ $application->name }} · {{ $application->bikeTypeLabel() }} · <span dir="ltr">{{ $application->phone }}</span></span>
                @include('admin.partials.pill', ['status' => 'pending', 'label' => 'جاري التحقق'])
            </a>
        @endforeach
    </div>
</section>
@endif

<div class="grid gap-4 xl:grid-cols-[1.7fr_1fr]">
    <section class="admin-card">
        <h2>المندوبون</h2>
        <p class="mt-1 text-sm text-on-surface-variant">فاضي = لا يوجد طلب قيد التوصيل. مشغول = معه طلب حالياً.</p>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @forelse($couriers as $courier)
                @php $current = $courier->activeDelivery(); @endphp
                <a href="{{ route('admin.delivery.show', $courier) }}" class="rounded-2xl border border-slate-100 bg-surface-container-lowest p-4 shadow-xs block hover:border-primary/30">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-extrabold">{{ $courier->name }}</div>
                            <div class="text-sm text-on-surface-variant" dir="ltr">{{ $courier->phone }}</div>
                        </div>
                        @include('admin.partials.pill', [
                            'status' => $current ? 'delivering' : 'delivered',
                            'label' => $current ? 'مشغول' : 'فاضي',
                        ])
                    </div>
                    <div class="mt-3 text-sm leading-7">
                        @if($current)
                            <div>الطلب الحالي: #{{ $current->id }} — {{ $current->restaurant?->name }}</div>
                            <div class="text-on-surface-variant">{{ $current->address_details }}</div>
                        @else
                            <div class="text-on-surface-variant">جاهز لاستلام طلب جديد.</div>
                        @endif
                        <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                            <span>مسلّم اليوم: {{ $courier->today_count }}</span>
                            <span class="font-semibold text-emerald-700">رصيد: {{ number_format($courier->courierAvailableBalance(), 2) }} ₪</span>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-on-surface-variant md:col-span-2">لا يوجد مندوبون بعد. أضف أول مندوب من النموذج.</p>
            @endforelse
        </div>
    </section>

    <section class="admin-card">
        <h2>إضافة مندوب</h2>
        <p class="mt-1 text-sm text-on-surface-variant">يدخل من صفحة تسجيل الدخول برقم الهاتف، ثم تُفتح له لوحة التوصيل.</p>
        <form method="POST" action="{{ route('admin.delivery.store') }}" class="admin-form mt-4 space-y-3">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-bold">الاسم</label>
                <input name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">الهاتف</label>
                <input name="phone" value="{{ old('phone') }}" required inputmode="numeric" placeholder="059XXXXXXXX">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">كلمة المرور</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button class="admin-btn admin-btn--primary w-full">حفظ المندوب</button>
        </form>
    </section>
</div>

<section class="admin-card mt-4">
    <div class="admin-toolbar">
        <h2>{{ $tab === 'payouts' ? 'طلبات سحب مستحقات المندوبين' : 'طلبات التوصيل' }}</h2>
    </div>
    <div class="admin-chips mb-4">
        <a class="admin-chip {{ $tab === 'waiting' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'waiting']) }}">بانتظار مندوب ({{ $waitingCount }})</a>
        <a class="admin-chip {{ $tab === 'active' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'active']) }}">قيد التوصيل ({{ $activeCount }})</a>
        <a class="admin-chip {{ $tab === 'done' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'done']) }}">مسلّم اليوم ({{ $doneCount }})</a>
        <a class="admin-chip {{ $tab === 'all' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'all']) }}">كل طلبات التوصيل</a>
        <a class="admin-chip {{ $tab === 'payouts' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'payouts']) }}">
            طلبات سحب الأرباح
            @if($pendingPayoutsCount > 0)
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full mr-1">{{ $pendingPayoutsCount }}</span>
            @endif
        </a>
    </div>

    @if($tab === 'payouts')
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-on-surface-variant">
                يتم احتساب الرصيد تلقائياً (صافي 85% من رسوم التوصيل بعد خصم 15% نسبة المنصة). عند تحويل المبلغ للمندوب اضغط "تم التحويل" لإرسال إشعار له وتصفير الرصيد المسحوب.
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.delivery.index', ['tab' => 'payouts']) }}" class="admin-chip {{ !request('payout_status') ? 'is-active' : '' }}">الكل</a>
                <a href="{{ route('admin.delivery.index', ['tab' => 'payouts', 'payout_status' => 'pending']) }}" class="admin-chip {{ request('payout_status') === 'pending' ? 'is-active' : '' }}">معلقة</a>
                <a href="{{ route('admin.delivery.index', ['tab' => 'payouts', 'payout_status' => 'completed']) }}" class="admin-chip {{ request('payout_status') === 'completed' ? 'is-active' : '' }}">تم التحويل</a>
                <a href="{{ route('admin.delivery.index', ['tab' => 'payouts', 'payout_status' => 'rejected']) }}" class="admin-chip {{ request('payout_status') === 'rejected' ? 'is-active' : '' }}">مرفوضة</a>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المندوب</th>
                        <th>المبلغ المطلوب</th>
                        <th>طريقة التحويل والبيانات</th>
                        <th>تاريخ الطلب</th>
                        <th>الحالة</th>
                        <th>الإجراءات / المعالجة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                        <tr>
                            <td>
                                <strong class="text-primary">#{{ $payout->id }}</strong>
                            </td>
                            <td>
                                @if($payout->courier)
                                    <a class="font-bold text-primary hover:underline" href="{{ route('admin.delivery.show', $payout->courier) }}">
                                        {{ $payout->courier->name }}
                                    </a>
                                    <div class="text-xs text-on-surface-variant" dir="ltr">{{ $payout->courier->phone }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        الرصيد المتاح: {{ number_format($payout->courier->courierAvailableBalance(), 2) }} ₪
                                    </div>
                                @else
                                    <span class="text-on-surface-variant">مندوب محذوف</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-base font-black text-emerald-700">{{ number_format((float) $payout->amount, 2) }} ₪</span>
                            </td>
                            <td>
                                <div class="font-bold text-slate-800">{{ $payout->methodLabel() }}</div>
                                <div class="text-xs font-mono bg-slate-100 p-1.5 rounded-lg border border-slate-200 mt-1 max-w-xs break-all" dir="ltr">
                                    {{ $payout->transfer_details }}
                                </div>
                            </td>
                            <td class="text-xs text-on-surface-variant whitespace-nowrap">
                                <div>{{ $payout->created_at->translatedFormat('Y-m-d H:i') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $payout->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $payout->statusBadgeClass() }}">
                                    {{ $payout->statusLabel() }}
                                </span>
                            </td>
                            <td class="min-w-[220px]">
                                @if($payout->isPending())
                                    <div class="space-y-2">
                                        <form method="POST" action="{{ route('admin.delivery.payouts.complete', $payout) }}" onsubmit="return confirm('تأكيد تحويل مبلغ {{ $payout->amount }} ₪ للمندوب؟');" class="space-y-1.5">
                                            @csrf
                                            <input type="text" name="admin_notes" placeholder="ملاحظات / رقم الحوالة (اختياري)" class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200">
                                            <button type="submit" class="admin-action-btn admin-action-btn--primary w-full justify-center">
                                                <span class="material-symbols-outlined">check_circle</span>
                                                <span>تأكيد التحويل للمندوب</span>
                                            </button>
                                        </form>

                                        <details class="text-xs">
                                            <summary class="cursor-pointer text-rose-600 font-bold hover:underline py-1 inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[15px]">cancel</span>
                                                <span>رفض الطلب</span>
                                            </summary>
                                            <form method="POST" action="{{ route('admin.delivery.payouts.reject', $payout) }}" class="mt-1.5 space-y-1.5">
                                                @csrf
                                                <input type="text" name="admin_notes" required placeholder="سبب الرفض..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-rose-300">
                                                <button type="submit" class="admin-action-btn admin-action-btn--danger w-full justify-center">
                                                    <span>تأكيد الرفض</span>
                                                </button>
                                            </form>
                                        </details>
                                    </div>
                                @elseif($payout->isCompleted())
                                    <div class="text-xs text-slate-600">
                                        <div class="font-bold text-emerald-700">تم التحويل بواسطة: {{ $payout->processor?->name ?? 'الإدارة' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $payout->processed_at?->translatedFormat('Y-m-d H:i') }}</div>
                                        @if($payout->admin_notes)
                                            <div class="mt-1 bg-slate-50 p-1.5 rounded border border-slate-200 text-slate-700">{{ $payout->admin_notes }}</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-rose-700">
                                        <div class="font-bold">مرفوض: {{ $payout->processor?->name ?? 'الإدارة' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $payout->processed_at?->translatedFormat('Y-m-d H:i') }}</div>
                                        @if($payout->admin_notes)
                                            <div class="mt-1 bg-rose-50 p-1.5 rounded border border-rose-200 text-rose-800">{{ $payout->admin_notes }}</div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-6 text-on-surface-variant">لا توجد طلبات سحب في هذا القسم.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payouts->links() }}</div>
    @else
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المطعم / الزبون</th>
                        <th>العنوان</th>
                        <th>المندوب</th>
                        <th>الحالة</th>
                        <th class="text-center">تعيين</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a class="font-bold text-primary hover:underline" href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a>
                                <div class="text-xs font-mono font-bold text-slate-600">{{ number_format((float) $order->total, 2) }} ₪</div>
                            </td>
                            <td>
                                <div class="font-bold text-slate-900">{{ $order->restaurant?->name }}</div>
                                <div class="text-xs text-on-surface-variant">{{ $order->user?->name }} · {{ $order->phone }}</div>
                            </td>
                            <td class="text-sm text-slate-700">{{ $order->address_details }}</td>
                            <td>
                                @if($order->courier)
                                    <a class="font-bold text-primary hover:underline" href="{{ route('admin.delivery.show', $order->courier) }}">{{ $order->courier->name }}</a>
                                    <div class="text-xs text-on-surface-variant font-mono" dir="ltr">{{ $order->courier->phone }}</div>
                                @else
                                    <span class="text-slate-400 italic text-xs">بدون مندوب</span>
                                @endif
                            </td>
                            <td>
                                @include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])
                                @if($order->isPrepared())
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">
                                            🍳 جهز بالمطعم
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if(in_array($order->status, ['preparing', 'delivering'], true))
                                    <div class="flex flex-col gap-1.5">
                                        <form method="POST" action="{{ route('admin.delivery.assign', $order) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            <select name="courier_id" class="min-w-[8.5rem] !py-1 !text-xs" required>
                                                <option value="">اختر مندوب…</option>
                                                @foreach($idle as $courier)
                                                    <option value="{{ $courier->id }}" @selected($order->courier_id === $courier->id)>{{ $courier->name }} (فاضي)</option>
                                                @endforeach
                                                @foreach($busy as $courier)
                                                    <option value="{{ $courier->id }}" @selected($order->courier_id === $courier->id)>{{ $courier->name }} (مشغول)</option>
                                                @endforeach
                                            </select>
                                            <button class="admin-action-btn admin-action-btn--dark admin-action-btn--sm">
                                                <span class="material-symbols-outlined">person_add</span>
                                                <span>تعيين</span>
                                            </button>
                                        </form>
                                        @if($order->courier_id)
                                            <form method="POST" action="{{ route('admin.delivery.unassign', $order) }}">
                                                @csrf
                                                <button class="admin-action-btn admin-action-btn--danger admin-action-btn--sm">
                                                    <span class="material-symbols-outlined">person_remove</span>
                                                    <span>إلغاء التعيين</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-on-surface-variant">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">لا توجد طلبات في هذا التبويب.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</section>
@endsection
