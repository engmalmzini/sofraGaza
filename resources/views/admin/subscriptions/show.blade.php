@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'طلب عضوية')

@section('content')
<div class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
    <section class="admin-card leading-8">
        <div>الزبون: {{ $subscription->user->name }} — {{ $subscription->user->phone }}</div>
        <div>العضوية: {{ $subscription->membership->name }}</div>
        <div>المبلغ: {{ number_format($subscription->amount, 2) }} <span class="ils">₪</span></div>
        <div class="mt-2">@include('admin.partials.pill', ['status' => $subscription->status, 'label' => $subscription->statusLabel()])</div>
        @if($subscription->ends_at)
            <div>من {{ $subscription->starts_at->format('Y-m-d') }} إلى {{ $subscription->ends_at->format('Y-m-d') }}</div>
        @endif
        @if($subscription->status === 'pending')
            <p class="mt-3 text-sm text-on-surface-variant">راجع إشعار الحوالة ثم فعّل البطاقة الرقمية داخل حساب الزبون. بعدها أرسل نسخة من البطاقة يدوياً عبر واتساب.</p>
            <form method="POST" action="{{ route('admin.subscriptions.approve', $subscription) }}" class="mt-4" data-once-submit>
                @csrf
                <button class="admin-btn admin-btn--secondary">تفعيل البطاقة 30 يوماً</button>
            </form>
            <form method="POST" action="{{ route('admin.subscriptions.reject', $subscription) }}" class="mt-3" data-once-submit>
                @csrf
                <textarea name="rejection_reason" placeholder="سبب الرفض"></textarea>
                <button class="admin-btn admin-btn--danger mt-2">رفض</button>
            </form>
        @endif
        @if($subscription->status === 'approved')
            <div class="mt-5">
                @include('partials.membership-card', ['subscription' => $subscription, 'holder' => $subscription->user])
            </div>
            @if($subscription->whatsappCardUrl())
                <a href="{{ $subscription->whatsappCardUrl() }}" target="_blank" rel="noopener" class="admin-btn admin-btn--secondary mt-4 inline-flex">
                    إرسال نسخة البطاقة عبر واتساب
                </a>
                <p class="mt-2 text-xs text-on-surface-variant">التسليم خارج المنصة: افتح واتساب وأرسل صورة البطاقة أو تفاصيلها للزبون كتأكيد إضافي.</p>
            @endif
            <div class="mt-5 border-t border-white/40 pt-4">
                <h2>بطاقة المطعم البلاستيكية</h2>
                <p class="mt-1 text-sm text-on-surface-variant">اختيارية. البطاقة الرقمية مفعّلة تلقائياً بعد الموافقة. هذه البطاقة يبرزها الزبون داخل المطاعم المشتركة.</p>
                <div class="mt-1 text-sm">الرقم: {{ $subscription->cardNumber() }}</div>
                <div class="mt-2">@include('admin.partials.pill', ['status' => $subscription->card_status ?: 'cancelled', 'label' => $subscription->cardStatusLabel()])</div>
                @if($subscription->card_note)
                    <p class="mt-2 text-sm">ملاحظة الزبون: {{ $subscription->card_note }}</p>
                @endif
                @if($subscription->card_status)
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($subscription->card_status !== 'ready')
                            <form method="POST" action="{{ route('admin.subscriptions.card', $subscription) }}" data-once-submit>
                                @csrf
                                <input type="hidden" name="card_status" value="ready">
                                <button class="admin-btn admin-btn--secondary">البطاقة جاهزة</button>
                            </form>
                        @endif
                        @if($subscription->card_status !== 'delivered')
                            <form method="POST" action="{{ route('admin.subscriptions.card', $subscription) }}" data-once-submit>
                                @csrf
                                <input type="hidden" name="card_status" value="delivered">
                                <button class="admin-btn admin-btn--ghost">تم التسليم</button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="mt-2 text-sm text-on-surface-variant">الزبون لم يطلب البطاقة البلاستيكية بعد.</p>
                @endif
            </div>
        @endif
    </section>
    <section class="admin-card sg-receipt-card">
        <h2>إشعار الحوالة</h2>
        @if($subscription->hasReceiptFile())
            <a href="{{ route('admin.subscriptions.receipt', $subscription) }}" target="_blank" rel="noopener" class="sg-receipt-frame">
                <img src="{{ $subscription->receiptDataUri() }}" alt="إشعار الحوالة">
            </a>
            <a href="{{ route('admin.subscriptions.receipt', $subscription) }}" target="_blank" rel="noopener" class="sg-receipt-open">
                فتح الصورة بحجم كامل
                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            </a>
        @elseif($subscription->transfer_receipt_path)
            <p class="sg-receipt-empty">تم رفع الإشعار لكن تعذر قراءة الملف من التخزين. جرّب فتح الرابط المباشر أو اطلب من الزبون إعادة الإرسال.</p>
            <a href="{{ route('admin.subscriptions.receipt', $subscription) }}" class="sg-receipt-open" target="_blank" rel="noopener">محاولة فتح الملف</a>
        @else
            <p class="sg-receipt-empty">لا يوجد إشعار حوالة مرفوع على هذا الطلب.</p>
        @endif
    </section>
</div>
@endsection
