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
                        <div>مسلّم اليوم: {{ $courier->today_count }}</div>
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
        <h2>طلبات التوصيل</h2>
    </div>
    <div class="admin-chips mb-4">
        <a class="admin-chip {{ $tab === 'waiting' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'waiting']) }}">بانتظار مندوب ({{ $waitingCount }})</a>
        <a class="admin-chip {{ $tab === 'active' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'active']) }}">قيد التوصيل ({{ $activeCount }})</a>
        <a class="admin-chip {{ $tab === 'done' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'done']) }}">مسلّم اليوم ({{ $doneCount }})</a>
        <a class="admin-chip {{ $tab === 'all' ? 'is-active' : '' }}" href="{{ route('admin.delivery.index', ['tab' => 'all']) }}">كل طلبات التوصيل</a>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المطعم / الزبون</th>
                    <th>العنوان</th>
                    <th>المندوب</th>
                    <th>الحالة</th>
                    <th>تعيين</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a class="font-bold text-primary" href="{{ route('admin.orders.show', $order) }}">{{ $order->id }}</a>
                            <div class="text-xs text-on-surface-variant">{{ number_format((float) $order->total, 2) }} ₪</div>
                        </td>
                        <td>
                            <div class="font-bold">{{ $order->restaurant?->name }}</div>
                            <div class="text-xs text-on-surface-variant">{{ $order->user?->name }} · {{ $order->phone }}</div>
                        </td>
                        <td class="text-sm">{{ $order->address_details }}</td>
                        <td>
                            @if($order->courier)
                                <a class="font-bold text-primary" href="{{ route('admin.delivery.show', $order->courier) }}">{{ $order->courier->name }}</a>
                                <div class="text-xs text-on-surface-variant" dir="ltr">{{ $order->courier->phone }}</div>
                            @else
                                <span class="text-on-surface-variant">بدون مندوب</span>
                            @endif
                        </td>
                        <td>@include('admin.partials.pill', ['status' => $order->status, 'label' => $order->statusLabel()])</td>
                        <td class="whitespace-nowrap">
                            @if(in_array($order->status, ['preparing', 'delivering'], true))
                                <form method="POST" action="{{ route('admin.delivery.assign', $order) }}" class="flex items-center gap-2">
                                    @csrf
                                    <select name="courier_id" class="min-w-[9rem]" required>
                                        <option value="">مندوب…</option>
                                        @foreach($idle as $courier)
                                            <option value="{{ $courier->id }}" @selected($order->courier_id === $courier->id)>{{ $courier->name }} (فاضي)</option>
                                        @endforeach
                                        @foreach($busy as $courier)
                                            <option value="{{ $courier->id }}" @selected($order->courier_id === $courier->id)>{{ $courier->name }} (مشغول)</option>
                                        @endforeach
                                    </select>
                                    <button class="admin-btn admin-btn--secondary">تعيين</button>
                                </form>
                                @if($order->courier_id)
                                    <form method="POST" action="{{ route('admin.delivery.unassign', $order) }}" class="mt-2">
                                        @csrf
                                        <button class="text-sm text-primary">إلغاء التعيين</button>
                                    </form>
                                @endif
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
</section>
@endsection
