@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'حوالة اشتراك مطعم')

@section('actions')
<a href="{{ route('admin.restaurants.show', $listing->restaurant) }}" class="admin-btn admin-btn--ghost">صفحة المطعم</a>
@endsection

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
    <section class="admin-card leading-8">
        <div>المطعم: {{ $listing->restaurant->name }}</div>
        <div>صاحب المطعم: {{ $listing->restaurant->owner->name ?? '—' }} — {{ $listing->restaurant->owner->phone ?? '' }}</div>
        <div>الباقة: {{ $listing->plan->name }} ({{ $listing->plan->durationLabel() }})</div>
        <div>المبلغ: {{ number_format($listing->amount, 2) }} <span class="ils">₪</span></div>
        <div class="mt-2">@include('admin.partials.pill', ['status' => $listing->status, 'label' => $listing->statusLabel()])</div>
        @if($listing->ends_at)
            <div>من {{ $listing->starts_at->format('Y-m-d') }} إلى {{ $listing->ends_at->format('Y-m-d') }}</div>
        @endif
        @if($listing->status === 'pending')
            <p class="mt-3 text-sm text-on-surface-variant">راجع إشعار الحوالة ثم اضغط تم لتفعيل اللوحة ونشر المطعم.</p>
            <form method="POST" action="{{ route('admin.listings.approve', $listing) }}" class="mt-4" data-once-submit>
                @csrf
                <button class="admin-btn admin-btn--secondary">تم — تأكيد الدفع وتفعيل اللوحة</button>
            </form>
            <form method="POST" action="{{ route('admin.listings.reject', $listing) }}" class="mt-3" data-once-submit>
                @csrf
                <textarea name="rejection_reason" placeholder="سبب الرفض"></textarea>
                <button class="admin-btn admin-btn--danger mt-2">رفض الحوالة</button>
            </form>
        @endif
        @if($listing->rejection_reason)
            <p class="mt-3 text-sm">سبب الرفض: {{ $listing->rejection_reason }}</p>
        @endif
    </section>
    <section class="admin-card sg-receipt-card">
        <h2>إشعار الحوالة</h2>
        @if($listing->hasReceiptFile())
            <a href="{{ route('admin.listings.receipt', $listing) }}" target="_blank" rel="noopener" class="sg-receipt-frame">
                <img src="{{ $listing->receiptDataUri() }}" alt="إشعار الحوالة">
            </a>
            <a href="{{ route('admin.listings.receipt', $listing) }}" target="_blank" rel="noopener" class="sg-receipt-open">
                فتح الصورة بحجم كامل
                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            </a>
        @elseif($listing->transfer_receipt_path)
            <p class="sg-receipt-empty">تم رفع الإشعار لكن تعذر قراءة الملف من التخزين.</p>
        @else
            <p class="sg-receipt-empty">لا يوجد إشعار مرفوع.</p>
        @endif
    </section>
</div>
@endsection
