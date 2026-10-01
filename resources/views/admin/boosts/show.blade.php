@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'مراجعة إعلان '.$boost->restaurant->name)

@section('actions')
    <a href="{{ route('admin.boosts.index') }}" class="admin-btn admin-btn--ghost">كل الإعلانات</a>
@endsection

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
    <section class="admin-card leading-8">
        <div>المطعم: {{ $boost->restaurant->name }}</div>
        <div>صاحب المطعم: {{ $boost->restaurant->owner->name ?? '—' }} — {{ $boost->restaurant->owner->phone ?? '' }}</div>
        <div>المدة المطلوبة: {{ $boost->durationDays() }} يوم × {{ number_format((float) $boost->daily_rate, 0) }} ₪</div>
        <div>المبلغ: {{ number_format($boost->amount, 2) }} <span class="ils">₪</span></div>
        <div>الفترة: {{ $boost->starts_on->format('Y/m/d') }} — {{ $boost->ends_on->format('Y/m/d') }}</div>
        <div class="mt-2">
            @if($boost->isPending())
                <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
            @elseif($boost->isApproved())
                <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
            @else
                <span class="inline-flex px-2.5 py-1 rounded-full bg-rose-100 text-rose-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
            @endif
        </div>
        @if($boost->isPending())
            <p class="mt-3 text-sm text-on-surface-variant">راجع إشعار الحوالة ثم أكّد ليظهر المطعم أولاً في الصفحة الرئيسية وقائمة المطاعم.</p>
            <form method="POST" action="{{ route('admin.boosts.approve', $boost) }}" class="mt-4">
                @csrf
                <button class="admin-btn admin-btn--primary">تأكيد الدفع وتفعيل الإعلان</button>
            </form>
            <form method="POST" action="{{ route('admin.boosts.reject', $boost) }}" class="mt-3">
                @csrf
                <textarea name="rejection_reason" required placeholder="سبب الرفض" class="w-full rounded-xl border border-slate-200 p-2.5 text-sm"></textarea>
                <button class="admin-btn admin-btn--ghost mt-2">رفض الحوالة</button>
            </form>
        @endif
        @if($boost->rejection_reason)
            <p class="mt-3 text-sm">سبب الرفض: {{ $boost->rejection_reason }}</p>
        @endif
        @if($boost->approver)
            <p class="mt-3 text-xs text-slate-500">أكّده {{ $boost->approver->name }} في {{ $boost->approved_at?->format('Y/m/d H:i') }}</p>
        @endif
    </section>
    <section class="admin-card sg-receipt-card">
        <h2>إشعار الحوالة</h2>
        @if($boost->hasReceiptFile())
            <a href="{{ route('admin.boosts.receipt', $boost) }}" target="_blank" rel="noopener" class="sg-receipt-frame">
                <img src="{{ $boost->receiptDataUri() }}" alt="إشعار الحوالة">
            </a>
            <a href="{{ route('admin.boosts.receipt', $boost) }}" target="_blank" rel="noopener" class="sg-receipt-open">
                فتح الصورة بحجم كامل
                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            </a>
        @elseif($boost->transfer_receipt_path)
            <p class="sg-receipt-empty">تم رفع الإشعار لكن تعذر قراءة الملف من التخزين.</p>
        @else
            <p class="sg-receipt-empty">لا يوجد إشعار مرفوع (حملة مسجّلة من المالية).</p>
        @endif
    </section>
</div>
@endsection
