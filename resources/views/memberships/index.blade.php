@extends('layouts.public')

@section('title', 'العضويات والمكافآت')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    <div class="mb-6">
        <div class="inline-flex items-center gap-1.5 text-amber-700 text-[12px] font-bold tracking-wider mb-1">
            <span class="material-symbols-outlined text-[16px]">stars</span>
            <span>برنامج الولاء</span>
        </div>
        <h1 class="font-headline-md text-2xl lg:text-[28px] font-bold text-stone-900">العضويات والمكافآت</h1>
        <p class="text-sm text-on-surface-variant mt-1">اشترك بتحويل شهري ثم أرفق إشعار الحوالة. بعد موافقة الإدارة تُفعَّل بطاقة رقمية في ملفك الشخصي، ويُطبَّق الخصم تلقائياً عند الدفع.</p>
    </div>

    @if($current?->isExpiringSoon())
        <p class="mb-4 rounded-2xl bg-tertiary-fixed text-tertiary px-4 py-3 text-sm font-medium">
            باقي {{ $current->daysRemaining() }} أيام على انتهاء عضويتك. جدّد من الأسفل حتى لا تفقد الخصم.
        </p>
    @endif

    @if($current)
        <section class="mb-6 grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            @include('partials.membership-card', ['subscription' => $current, 'holder' => auth()->user()])
            <article class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs">
                <h2 class="text-lg font-bold text-on-surface">تفاصيل اشتراكك</h2>
                <ul class="mt-3 space-y-2 text-sm text-on-surface-variant">
                    @foreach($current->membership->benefitsList() as $benefit)
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-secondary text-[18px]">check_circle</span>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-secondary text-[18px]">payments</span>
                        <span>قيمة الاشتراك {{ number_format($current->amount, 0) }} ₪ / شهر</span>
                    </li>
                </ul>

                <div class="mt-5 rounded-xl bg-surface-container-low px-4 py-3 text-sm">
                    <div class="font-bold text-on-surface">البطاقة الرقمية</div>
                    <p class="mt-1 text-on-surface-variant">بطاقة حسابك ظاهرة أعلاه وفي الملف الشخصي بعد تفعيل الأدمن للحوالة. الخصم يُطبَّق تلقائياً عند الدفع على المنصة.</p>
                    <p class="mt-2 text-on-surface-variant">للمطاعم المشتركة: يمكن للطاقم التحقق برقم {{ $current->cardNumber() }}.</p>
                    <p class="mt-2 font-semibold text-on-surface">بطاقة المطعم البلاستيكية: {{ $current->cardStatusLabel() }}</p>
                    @if($current->card_note)
                        <p class="mt-1 text-xs text-on-surface-variant">ملاحظة الاستلام: {{ $current->card_note }}</p>
                    @endif
                </div>

                @if($current->canRequestCard())
                    <form method="POST" action="{{ route('memberships.card') }}" class="mt-4 space-y-3" data-once-submit>
                        @csrf
                        <label class="block text-sm font-bold text-on-surface">عنوان أو فرع استلام البطاقة البلاستيكية (اختياري)</label>
                        <input name="card_note" value="{{ old('card_note') }}" placeholder="مثال: حي الرمال — استلام من المكتب" class="w-full rounded-xl border border-outline/30 px-3 py-3 text-sm">
                        <button class="w-full rounded-xl bg-primary hover:bg-primary-container text-on-primary py-3 font-semibold">طلب بطاقة المطعم</button>
                    </form>
                @elseif($current->card_status === 'pending')
                    <p class="mt-4 rounded-xl bg-tertiary-fixed text-tertiary px-4 py-3 text-sm font-medium">طلب البطاقة البلاستيكية وصل للإدارة وهو قيد التجهيز.</p>
                @elseif($current->card_status === 'ready')
                    <p class="mt-4 rounded-xl bg-secondary-fixed text-on-secondary-fixed px-4 py-3 text-sm font-medium">بطاقتك جاهزة للاستلام. أبرزها داخل المطاعم المشتركة لتأخذ خصمك.</p>
                @endif
            </article>
        </section>
    @endif

    @if($pending)
        <p class="mb-3 rounded-2xl bg-tertiary-fixed text-tertiary px-4 py-3 text-sm font-medium">طلب عضوية {{ $pending->membership->name }} بانتظار مراجعة الحوالة.</p>
    @endif

    <h2 class="mb-3 text-lg font-bold text-on-surface">{{ $current ? 'ترقية أو تجديد' : 'اختر عضويتك' }}</h2>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($memberships as $membership)
            @php $isCurrent = $current && $current->membership_id === $membership->id; @endphp
            <article class="rounded-2xl bg-surface-container-lowest border border-slate-100 p-5 shadow-xs {{ $isCurrent ? 'ring-2 ring-secondary/40' : '' }}">
                <h2 class="text-xl font-bold text-on-surface">{{ $membership->name }}</h2>
                <div class="mt-2 flex items-center gap-1.5 text-3xl font-bold text-primary">{{ number_format($membership->monthly_price) }} <span class="ils">₪</span> <span class="text-sm font-medium text-on-surface-variant">شهرياً</span></div>
                <ul class="mt-4 space-y-2 text-sm text-on-surface-variant">
                    @foreach($membership->benefitsList() as $benefit)
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-secondary text-[18px]">check_circle</span>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
                @if($isCurrent)
                    <p class="mt-5 rounded-xl bg-secondary-fixed text-on-secondary-fixed px-4 py-3 text-sm font-semibold text-center">هذه عضويتك الحالية</p>
                @elseif($pending)
                    <p class="mt-5 text-sm text-on-surface-variant">لا يمكن إرسال طلب جديد قبل مراجعة الحوالة الحالية.</p>
                @elseif(auth()->check())
                    <form method="POST" action="{{ route('memberships.subscribe', $membership) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
                        @csrf
                        <input type="file" name="receipt" accept="image/*" class="w-full rounded-xl border border-dashed border-outline/40 p-2 text-sm">
                        <button class="w-full rounded-xl bg-primary hover:bg-primary-container text-on-primary py-3 font-semibold">{{ $current ? 'ترقية وأرفق الحوالة' : 'اشترك وأرفق الحوالة' }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-5 inline-flex rounded-xl bg-primary px-4 py-2.5 text-on-primary font-semibold">سجّل دخول للاشتراك</a>
                @endif
            </article>
        @endforeach
    </div>
</div>
@endsection
