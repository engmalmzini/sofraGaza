@extends('layouts.partner')

@section('title', 'الإعلان والظهور الأول')

@section('content')
@if(! $restaurant->isApproved())
    <div class="admin-alert admin-alert--wait">
        بعد موافقة الإدارة على {{ $restaurant->venueNounYours() }} تقدر تشتري إعلاناً ليظهر في المقدمة للزبائن.
    </div>
@elseif($restaurant->panel_suspended)
    <div class="admin-alert admin-alert--err">
        اللوحة موقوفة حالياً. لا يمكن شراء إعلان حتى تعيد الإدارة تفعيل {{ $restaurant->venueNounYours() }}.
    </div>
@endif

<div class="admin-metrics !grid-cols-2 lg:!grid-cols-4">
    <article class="admin-metric">
        <div class="admin-metric__top"><span>سعر اليوم</span></div>
        <strong>{{ number_format($rate, 0) }} <span class="ils">₪</span></strong>
        <span class="admin-metric__note">إعلان مدفوع يظهر أولاً</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>الحملة الحالية</span></div>
        <strong>{{ $active ? $active->remainingDays().' يوم' : 'لا يوجد' }}</strong>
        <span class="admin-metric__note">{{ $active ? $active->starts_on->format('Y/m/d').' — '.$active->ends_on->format('Y/m/d') : 'اشترِ أياماً ليظهر '.$restaurant->venueNounYours().' في المقدمة' }}</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>طلب قيد المراجعة</span></div>
        <strong>{{ $pending ? number_format($pending->amount, 0).' ₪' : 'لا' }}</strong>
        <span class="admin-metric__note">{{ $pending ? $pending->days.' يوم بانتظار تأكيد الحوالة' : 'يمكنك إرسال طلب جديد' }}</span>
    </article>
    <article class="admin-metric">
        <div class="admin-metric__top"><span>ما يفعله الإعلان</span></div>
        <strong class="!text-base">الظهور أولاً</strong>
        <span class="admin-metric__note">تصميم إعلاني في الصفحة الرئيسية وقائمة المطاعم</span>
    </article>
</div>

@if($active)
    <section class="admin-card mt-4 border-2 border-amber-200 bg-amber-50/40">
        <h2>إعلانك ظاهر الآن</h2>
        <p class="text-sm text-slate-600 mt-1">{{ $restaurant->venueNounYours() }} يظهر في المقدمة حتى {{ $active->ends_on->format('Y/m/d') }} (باقي {{ $active->remainingDays() }} يوم).</p>
    </section>
@endif

@if($pending)
    <section class="admin-card mt-4">
        <h2>بانتظار تأكيد الإدارة</h2>
        <p class="text-sm mt-2">طلب إعلان لمدة <strong>{{ $pending->days }}</strong> يوم بقيمة <strong>{{ number_format($pending->amount, 0) }} ₪</strong>. بعد مراجعة الحوالة يظهر {{ $restaurant->venueNounYours() }} أولاً.</p>
    </section>
@endif

@if($canRequest)
    <section class="admin-card mt-4">
        <h2>اشترِ إعلاناً</h2>
        <p class="text-sm text-slate-500 mb-4">حدد عدد الأيام، يُحسب السعر تلقائياً ({{ number_format($rate, 0) }} ₪ × الأيام). حوّل المبلغ ثم أرفق إشعار الحوالة.</p>

        <div class="boost-price-box mb-4">
            <div>
                <span>عدد الأيام</span>
                <strong id="boost-days-label">7</strong>
            </div>
            <div>
                <span>السعر المستحق</span>
                <strong id="boost-total-label">{{ number_format($rate * 7, 0) }} <span class="ils">₪</span></strong>
            </div>
        </div>

        @include('partials.payment-instructions', ['requiredAmount' => $rate * 7])

        <form method="POST" action="{{ route('partner.boosts.store') }}" enctype="multipart/form-data" class="grid gap-4 mt-4 sm:grid-cols-2" id="partner-boost-form">
            @csrf
            <label>
                <span class="block font-bold text-sm mb-1">كم يوماً تريد الإعلان؟</span>
                <input type="number" name="days" id="boost-days" min="1" max="30" value="{{ old('days', 7) }}" required class="w-full !h-11 text-lg font-black">
            </label>
            <label>
                <span class="block font-bold text-sm mb-1">إشعار الحوالة</span>
                <input type="file" name="receipt" accept="image/*" required class="w-full rounded-xl border border-dashed border-slate-300 p-2 text-sm bg-white">
            </label>
            <div class="sm:col-span-2">
                <button class="admin-btn admin-btn--primary" type="submit">إرسال الحوالة وطلب الإعلان</button>
            </div>
        </form>
    </section>
@endif

@if($history->isNotEmpty())
    <section class="admin-card mt-4">
        <h2>سجل الحملات</h2>
        <div class="admin-table-wrap mt-3">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>المدة</th>
                        <th>الأيام</th>
                        <th>المبلغ</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $boost)
                        <tr>
                            <td class="text-xs">{{ $boost->starts_on->format('Y/m/d') }} — {{ $boost->ends_on->format('Y/m/d') }}</td>
                            <td>{{ $boost->durationDays() }}</td>
                            <td class="font-mono">{{ number_format($boost->amount, 0) }} ₪</td>
                            <td>
                                @if($boost->isPending())
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
                                @elseif($boost->isApproved())
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">{{ $boost->isLive() ? 'ظاهر الآن' : $boost->statusLabel() }}</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-rose-100 text-rose-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif

<script>
    (function () {
        var input = document.getElementById('boost-days');
        if (!input) return;
        var rate = {{ (float) $rate }};
        var daysLabel = document.getElementById('boost-days-label');
        var totalLabel = document.getElementById('boost-total-label');
        var amountBox = document.querySelector('[data-required-amount], .rounded-3xl .font-mono');
        function render() {
            var days = Math.max(1, Math.min(30, parseInt(input.value, 10) || 1));
            input.value = days;
            var total = days * rate;
            if (daysLabel) daysLabel.textContent = days;
            if (totalLabel) totalLabel.innerHTML = new Intl.NumberFormat('en-US').format(total) + ' <span class="ils">₪</span>';
            document.querySelectorAll('.rounded-3xl .font-mono').forEach(function (el) {
                if (el.closest('.rounded-3xl')) {
                    el.textContent = new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(total) + ' ₪';
                }
            });
        }
        input.addEventListener('input', render);
        render();
    })();
</script>
@endsection
