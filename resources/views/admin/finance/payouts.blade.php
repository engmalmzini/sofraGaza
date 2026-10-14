@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'طلبات سحب المطاعم')

@section('content')
@include('admin.finance.partials.toolbar', ['routeName' => 'admin.finance.payouts'])

<p class="text-xs text-slate-500 mb-3">
    صافي المطعم = المبيعات ناقص عمولة المنصة {{ $commissionLabel }}. لتأكيد الصرف ارفع صورة إشعار التحويل ثم اضغط «تأكيد التحويل». الرصيد لا يُخصم إلا بعد التأكيد.
</p>

<div class="admin-chips mb-4">
    <a class="admin-chip {{ $status === '' ? 'is-active' : '' }}" href="{{ route('admin.finance.payouts') }}">الكل</a>
    <a class="admin-chip {{ $status === 'pending' ? 'is-active' : '' }}" href="{{ route('admin.finance.payouts', ['payout_status' => 'pending']) }}">معلقة ({{ $pendingCount }})</a>
    <a class="admin-chip {{ $status === 'completed' ? 'is-active' : '' }}" href="{{ route('admin.finance.payouts', ['payout_status' => 'completed']) }}">تم التحويل</a>
    <a class="admin-chip {{ $status === 'rejected' ? 'is-active' : '' }}" href="{{ route('admin.finance.payouts', ['payout_status' => 'rejected']) }}">مرفوضة</a>
</div>

<section class="admin-card">
    <h2>طلبات سحب مستحقات المطاعم</h2>
    <div class="admin-table-wrap mt-3">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المطعم</th>
                    <th>المبلغ</th>
                    <th>الفترة</th>
                    <th>التحويل</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                    <tr>
                        <td class="font-bold text-primary">#{{ $payout->id }}</td>
                        <td>
                            <div class="font-bold">{{ $payout->restaurant?->name ?? '—' }}</div>
                            @if($payout->restaurant)
                                <div class="text-[11px] text-slate-500">المتاح: {{ number_format($payout->restaurant->availableBalance(), 2) }} ₪</div>
                            @endif
                        </td>
                        <td class="font-mono font-black text-emerald-700">{{ number_format((float) $payout->amount, 2) }} ₪</td>
                        <td>{{ $payout->periodLabel() }}</td>
                        <td class="text-xs">
                            <div class="font-bold">{{ $payout->methodLabel() }}</div>
                            <div class="text-slate-500">{{ $payout->transfer_details }}</div>
                        </td>
                        <td class="text-xs">{{ $payout->created_at->translatedFormat('Y-m-d H:i') }}</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $payout->statusClass() }}">{{ $payout->statusLabel() }}</span>
                        </td>
                        <td class="text-xs min-w-[230px]">
                            @if($payout->isPending())
                                <form method="POST" action="{{ route('admin.finance.payouts.complete', $payout) }}" enctype="multipart/form-data" onsubmit="return confirm('تأكيد تحويل {{ $payout->amount }} ₪ وإرفاق إشعار التحويل؟');" class="space-y-1.5">
                                    @csrf
                                    <label class="admin-payout-upload">
                                        <input type="file" name="receipt" accept="image/*" required class="sr-only" onchange="markPayoutReceipt(this)">
                                        <span class="admin-payout-upload__empty">
                                            <span class="material-symbols-outlined">add_photo_alternate</span>
                                            <span>رفع إشعار التحويل</span>
                                        </span>
                                        <span class="admin-payout-upload__filled hidden">
                                            <span class="material-symbols-outlined">check_circle</span>
                                            <span class="admin-payout-upload__name"></span>
                                        </span>
                                    </label>
                                    <input type="text" name="admin_notes" placeholder="ملاحظة اختيارية" class="w-full !h-8 text-xs">
                                    <button class="admin-action-btn admin-action-btn--sm w-full justify-center" type="submit">تأكيد التحويل</button>
                                </form>
                                <form method="POST" action="{{ route('admin.finance.payouts.reject', $payout) }}" class="mt-1.5 space-y-1.5">
                                    @csrf
                                    <input type="text" name="admin_notes" required placeholder="سبب الرفض" class="w-full !h-8 text-xs">
                                    <button class="admin-action-btn admin-action-btn--danger admin-action-btn--sm w-full justify-center" type="submit">رفض</button>
                                </form>
                            @elseif($payout->isCompleted())
                                <div class="font-bold text-emerald-700">{{ $payout->processor?->name ?? 'الإدارة' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $payout->processed_at?->translatedFormat('Y-m-d H:i') }}</div>
                                @if($payout->admin_notes)
                                    <div class="mt-1 bg-slate-50 p-1.5 rounded border border-slate-200 text-slate-700">{{ $payout->admin_notes }}</div>
                                @endif
                                @if($payout->hasReceiptFile())
                                    <a href="{{ route('admin.finance.payouts.receipt', $payout) }}" target="_blank" rel="noopener" class="admin-payout-receipt">
                                        <img src="{{ route('admin.finance.payouts.receipt', $payout) }}" alt="إشعار التحويل">
                                        <span>عرض إشعار التحويل</span>
                                    </a>
                                @endif
                            @else
                                <div class="font-bold">مرفوض: {{ $payout->processor?->name ?? 'الإدارة' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $payout->admin_notes }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-on-surface-variant">لا توجد طلبات سحب في هذا القسم.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payouts->links() }}</div>
</section>
<script>
function markPayoutReceipt(input) {
    const wrap = input.closest('.admin-payout-upload');
    if (!wrap) return;
    const empty = wrap.querySelector('.admin-payout-upload__empty');
    const filled = wrap.querySelector('.admin-payout-upload__filled');
    const name = wrap.querySelector('.admin-payout-upload__name');
    const file = input.files && input.files[0];
    if (!file) {
        empty.classList.remove('hidden');
        filled.classList.add('hidden');
        filled.classList.remove('flex');
        return;
    }
    empty.classList.add('hidden');
    filled.classList.remove('hidden');
    filled.classList.add('flex');
    name.textContent = file.name;
}
</script>
@endsection
