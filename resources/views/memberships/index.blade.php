@extends('layouts.public')

@section('title', 'العضويات والمكافآت')

@section('content')
<div class="mx-auto max-w-5xl px-margin lg:px-margin-desktop py-5 lg:py-10">
    {{-- 1. Hero Header Section --}}
    <div class="mb-8">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-800 text-[12px] font-bold mb-2.5">
            <span class="material-symbols-outlined text-[16px] text-amber-600">stars</span>
            <span>برنامج الولاء</span>
        </div>
        <h1 class="font-headline-md text-2xl lg:text-[32px] font-black text-stone-900 tracking-tight">العضويات والمكافآت</h1>
        <p class="text-xs sm:text-sm text-stone-600 mt-1.5 max-w-2xl leading-relaxed">
            اشترك بتحويل شهري ثم أرفق إشعار الحوالة. بعد موافقة الإدارة تُفعّل بطاقة رقمية في ملفك الشخصي، ويُطبّق الخصم تلقائياً عند الدفع.
        </p>

        {{-- VIP Perks Highlights --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3.5 mt-5">
            <div class="rounded-2xl bg-white border border-stone-200/80 p-3 sm:p-3.5 shadow-2xs flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-orange-100 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">percent</span>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-stone-900 block truncate">خصم مباشر</span>
                    <span class="text-[11px] text-stone-500 block truncate">على جميع طلباتك</span>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-stone-200/80 p-3 sm:p-3.5 shadow-2xs flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">moped</span>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-stone-900 block truncate">توصيل مجاني</span>
                    <span class="text-[11px] text-stone-500 block truncate">للباقات المؤهلة</span>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-stone-200/80 p-3 sm:p-3.5 shadow-2xs flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">toll</span>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-stone-900 block truncate">مضاعفة النقاط</span>
                    <span class="text-[11px] text-stone-500 block truncate">لوجبات مجانية</span>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-stone-200/80 p-3 sm:p-3.5 shadow-2xs flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[19px]">credit_card</span>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-stone-900 block truncate">بطاقة مطاعم</span>
                    <span class="text-[11px] text-stone-500 block truncate">رقمية وبلاستيكية</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Status Notifications --}}
    @if($current?->isExpiringSoon())
        <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200/90 p-4 flex items-center gap-3 text-amber-900 text-sm shadow-2xs">
            <span class="material-symbols-outlined text-amber-600 text-[24px] shrink-0">alarm</span>
            <div class="flex-1">
                <span class="font-bold block sm:inline">تنبيه انتهاء العضوية:</span>
                <span class="text-amber-800">باقي {{ $current->daysRemaining() }} أيام على انتهاء عضويتك. جدّد من الأسفل حتى لا تفقد الخصم.</span>
            </div>
        </div>
    @endif

    @if($pending)
        <div class="mb-6 rounded-2xl bg-blue-50 border border-blue-200/90 p-4 flex items-center gap-3 text-blue-900 text-sm shadow-2xs">
            <span class="material-symbols-outlined text-blue-600 text-[24px] shrink-0">hourglass_top</span>
            <div class="flex-1">
                <span class="font-bold block sm:inline">طلب العضوية قيد المراجعة:</span>
                <span class="text-blue-800">طلب عضوية {{ $pending->membership->name }} بانتظار مراجعة الحوالة.</span>
            </div>
        </div>
    @endif

    {{-- 3. Active Subscription Card (If Subscribed) --}}
    @if($current)
        <section class="mb-10 grid items-start gap-5 lg:grid-cols-[1.15fr_0.85fr]">
            @include('partials.membership-card', ['subscription' => $current, 'holder' => auth()->user()])

            <article class="rounded-3xl bg-white border border-stone-200/80 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between gap-3 border-b border-stone-100 pb-3.5 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">badge</span>
                        </span>
                        <h2 class="text-lg font-extrabold text-stone-900">تفاصيل اشتراكك</h2>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">مفعّل</span>
                </div>

                <ul class="space-y-2.5 text-sm text-stone-700">
                    @foreach($current->membership->benefitsList() as $benefit)
                        <li class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 text-[18px] shrink-0 mt-0.5">check_circle</span>
                            <span class="font-medium">{{ $benefit }}</span>
                        </li>
                    @endforeach
                    <li class="flex items-start gap-2.5 pt-1 border-t border-stone-100">
                        <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5">payments</span>
                        <span class="font-bold text-stone-900">قيمة الاشتراك {{ number_format($current->amount, 0) }} ₪ / شهر</span>
                    </li>
                </ul>

                <div class="mt-5 rounded-2xl bg-stone-50 border border-stone-100 p-4 text-xs sm:text-sm text-stone-700 space-y-2">
                    <div class="font-bold text-stone-900 text-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-stone-600">smartphone</span>
                        <span>البطاقة الرقمية</span>
                    </div>
                    <p class="text-stone-600 leading-relaxed">بطاقة حسابك ظاهرة أعلاه وفي الملف الشخصي بعد تفعيل الأدمن للحوالة. الخصم يُطبَّق تلقائياً عند الدفع على المنصة.</p>
                    <p class="text-stone-600">للمطاعم المشتركة: يمكن للطاقم التحقق برقم <strong class="font-mono text-stone-900 bg-white px-2 py-0.5 rounded border border-stone-200">{{ $current->cardNumber() }}</strong>.</p>
                    <div class="pt-2 border-t border-stone-200/60 flex items-center justify-between gap-2">
                        <span class="font-bold text-stone-900">بطاقة المطعم البلاستيكية: {{ $current->cardStatusLabel() }}</span>
                    </div>
                    @if($current->card_note)
                        <p class="text-xs text-stone-500 bg-white p-2 rounded-lg border border-stone-200/60">ملاحظة الاستلام: {{ $current->card_note }}</p>
                    @endif
                </div>

                @if($current->canRequestCard())
                    <form method="POST" action="{{ route('memberships.card') }}" class="mt-4 space-y-3" data-once-submit>
                        @csrf
                        <label class="block text-xs sm:text-sm font-bold text-stone-800">عنوان أو فرع استلام البطاقة البلاستيكية (اختياري)</label>
                        <input name="card_note" value="{{ old('card_note') }}" placeholder="مثال: حي الرمال — استلام من المكتب" class="w-full rounded-xl border border-stone-200 px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        <button class="w-full rounded-xl bg-primary hover:bg-primary-container text-white py-3 font-bold shadow-2xs hover:shadow-xs active:scale-[0.99] transition-all cursor-pointer">طلب بطاقة المطعم</button>
                    </form>
                @elseif($current->card_status === 'pending')
                    <p class="mt-4 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-800 px-4 py-3 text-xs sm:text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">pending</span>
                        <span>طلب البطاقة البلاستيكية وصل للإدارة وهو قيد التجهيز.</span>
                    </p>
                @elseif($current->card_status === 'ready')
                    <p class="mt-4 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-800 px-4 py-3 text-xs sm:text-sm font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">task_alt</span>
                        <span>بطاقتك جاهزة للاستلام. أبرزها داخل المطاعم المشتركة لتأخذ خصمك.</span>
                    </p>
                @endif
            </article>
        </section>
    @endif

    {{-- 4. Membership Plans & Pricing (Placed at Top Before Payment) --}}
    <section class="mb-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-stone-900">{{ $current ? 'ترقية أو تجديد' : 'اختر عضويتك' }}</h2>
                <p class="text-xs sm:text-sm text-stone-600 mt-1">اختر الباقة المناسبة لاستهلاكك واستمتع بخصومات حصرية وتوصيل مجاني ومضاعفة النقاط</p>
            </div>
            <a href="#payment-accounts" class="self-start sm:self-auto text-xs font-bold text-primary hover:text-primary-container flex items-center gap-1 transition-colors">
                <span>عرض حسابات الدفع</span>
                <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
            </a>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            @foreach($memberships as $membership)
                @php
                    $isCurrent = $current && $current->membership_id === $membership->id;
                    $isPopular = $membership->sort_order == 2 || $membership->discount_percent >= 10;
                @endphp
                <article class="rounded-3xl bg-white border {{ $isCurrent ? 'border-primary ring-2 ring-primary/20 bg-gradient-to-b from-orange-50/20 to-white' : ($isPopular ? 'border-amber-300 shadow-sm' : 'border-stone-200/80 shadow-2xs') }} p-5 sm:p-6 flex flex-col justify-between relative transition-all hover:shadow-md">
                    @if($isCurrent)
                        <div class="absolute -top-3 inset-inline-end-6 bg-primary text-white text-[11px] font-extrabold px-3 py-0.5 rounded-full shadow-2xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">check</span>
                            <span>عضويتك الحالية</span>
                        </div>
                    @elseif($isPopular)
                        <div class="absolute -top-3 inset-inline-end-6 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[11px] font-extrabold px-3 py-0.5 rounded-full shadow-2xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">auto_awesome</span>
                            <span>الأكثر توفيراً</span>
                        </div>
                    @endif

                    <div>
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <h2 class="text-xl font-black text-stone-900">{{ $membership->name }}</h2>
                            <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-orange-100/70 text-primary border border-orange-200/60">
                                خصم {{ $membership->discount_percent }}%
                            </span>
                        </div>

                        @if($membership->description)
                            <p class="text-xs text-stone-500 mb-3">{{ $membership->description }}</p>
                        @endif

                        <div class="mt-3 flex items-baseline gap-1.5 border-b border-stone-100 pb-4">
                            <span class="text-3xl sm:text-4xl font-black text-stone-900 font-mono">{{ number_format($membership->monthly_price) }}</span>
                            <span class="ils text-xl text-stone-800">₪</span>
                            <span class="text-xs font-semibold text-stone-500">/ شهرياً</span>
                        </div>

                        <ul class="mt-4 space-y-2.5 text-xs sm:text-sm text-stone-700">
                            @foreach($membership->benefitsList() as $benefit)
                                <li class="flex items-start gap-2.5">
                                    <span class="material-symbols-outlined text-emerald-600 text-[19px] shrink-0 mt-0.5">check_circle</span>
                                    <span class="font-medium">{{ $benefit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-stone-100">
                        @if($isCurrent)
                            <p class="w-full rounded-2xl bg-emerald-50 border border-emerald-200/70 text-emerald-800 py-3.5 px-4 text-xs sm:text-sm font-extrabold text-center flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">verified</span>
                                <span>هذه عضويتك الحالية</span>
                            </p>
                        @elseif($pending)
                            <p class="w-full rounded-2xl bg-stone-100 text-stone-500 py-3 px-4 text-xs font-semibold text-center">
                                لا يمكن إرسال طلب جديد قبل مراجعة الحوالة الحالية.
                            </p>
                        @elseif(auth()->check())
                            <form method="POST" action="{{ route('memberships.subscribe', $membership) }}" enctype="multipart/form-data" class="space-y-3" data-once-submit>
                                @csrf
                                <div class="relative">
                                    <label class="group flex flex-col items-center justify-center border border-dashed border-stone-300 hover:border-primary/50 bg-stone-50/70 hover:bg-orange-50/20 rounded-2xl p-3 text-center cursor-pointer transition-all">
                                        <input type="file" name="receipt" accept="image/*" class="sr-only" required onchange="previewMembershipReceipt(this, 'receipt-badge-{{ $membership->id }}', 'receipt-placeholder-{{ $membership->id }}')">
                                        
                                        <div id="receipt-placeholder-{{ $membership->id }}" class="flex items-center gap-2 text-xs font-bold text-stone-600 group-hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-[20px] text-stone-400 group-hover:text-primary">add_photo_alternate</span>
                                            <span>أرفق إشعار التحويل (صورة)</span>
                                        </div>

                                        <div id="receipt-badge-{{ $membership->id }}" class="hidden items-center gap-1.5 text-xs font-bold text-emerald-700">
                                            <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>
                                            <span class="receipt-filename truncate max-w-[200px]"></span>
                                        </div>
                                    </label>
                                </div>

                                <button type="submit" class="w-full rounded-2xl bg-primary hover:bg-primary-container text-white py-3.5 font-bold shadow-2xs hover:shadow-xs active:scale-[0.99] transition-all flex items-center justify-center gap-2 text-sm cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                    <span>{{ $current ? 'ترقية وأرفق الحوالة' : 'اشترك وأرفق الحوالة' }}</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="w-full rounded-2xl bg-primary hover:bg-primary-container text-white py-3.5 font-bold shadow-2xs text-center flex items-center justify-center gap-2 text-sm transition-all">
                                <span class="material-symbols-outlined text-[18px]">login</span>
                                <span>سجّل دخول للاشتراك</span>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- 5. How It Works (3 Steps) --}}
    <section class="mb-10 rounded-3xl bg-white border border-stone-200/80 p-5 sm:p-6 shadow-2xs">
        <h3 class="text-base sm:text-lg font-black text-stone-900 mb-1 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">route</span>
            <span>كيف تشترك في 3 خطوات بسيطة؟</span>
        </h3>
        <p class="text-xs text-stone-500 mb-5">خطوات سريعة وسهلة لتفعيل عضويتك والبدء بالاستفادة من الخصومات فوراً</p>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl bg-stone-50/90 border border-stone-200/60 p-3.5 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-orange-500/15 text-primary font-black text-sm flex items-center justify-center shrink-0">1</div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-stone-900">اختر باقتك المفضلة</h4>
                    <p class="text-[11px] sm:text-xs text-stone-600 mt-1 leading-relaxed">حدد الباقة المناسبة لاستهلاكك واعرف قيمة الاشتراك الشهري المناسبة لك.</p>
                </div>
            </div>

            <div class="rounded-2xl bg-stone-50/90 border border-stone-200/60 p-3.5 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-700 font-black text-sm flex items-center justify-center shrink-0">2</div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-stone-900">حوّل الرسوم المعتمدة</h4>
                    <p class="text-[11px] sm:text-xs text-stone-600 mt-1 leading-relaxed">أرسل المبلغ عبر محفظة جوال باي، بال باي، أو بنك فلسطين من البيانات أدناه.</p>
                </div>
            </div>

            <div class="rounded-2xl bg-stone-50/90 border border-stone-200/60 p-3.5 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-700 font-black text-sm flex items-center justify-center shrink-0">3</div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-stone-900">أرفق الإشعار والتفعيل</h4>
                    <p class="text-[11px] sm:text-xs text-stone-600 mt-1 leading-relaxed">ارفع صورة الإشعار وسيتم تفعيل بطاقتك الرقمية وخصوماتك فور مراجعة الإدارة.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. Payment Accounts & Instructions (Placed Right Where Users Need to Pay) --}}
    <div id="payment-accounts">
        @include('partials.payment-instructions')
    </div>
</div>

<script>
function previewMembershipReceipt(input, badgeId, placeholderId) {
    const badge = document.getElementById(badgeId);
    const placeholder = document.getElementById(placeholderId);
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (badge && placeholder) {
        placeholder.classList.add('hidden');
        badge.classList.remove('hidden');
        badge.classList.add('flex');
        const nameSpan = badge.querySelector('.receipt-filename');
        if (nameSpan) {
            nameSpan.textContent = file.name;
        }
    }
}
</script>
@endsection
