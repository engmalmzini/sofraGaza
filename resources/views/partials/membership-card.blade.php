@php
    $holder = $holder ?? $subscription->user;
@endphp
<article class="sg-member-card">
    <div class="sg-member-card__top">
        <span>بطاقة عضوية رقمية</span>
        <strong>{{ $subscription->cardNumber() }}</strong>
    </div>
    <h2>{{ $subscription->membership->name }}</h2>
    <p class="sg-member-card__name">{{ $holder->name }}</p>
    <p class="sg-member-card__discount">{{ $subscription->membership->deliveryBenefitLabel() ?: 'بدون خصم على التوصيل' }}</p>
    <dl class="sg-member-card__meta">
        <div><dt>البداية</dt><dd>{{ $subscription->starts_at?->format('Y/m/d') ?? '—' }}</dd></div>
        <div><dt>الانتهاء</dt><dd>{{ $subscription->ends_at?->format('Y/m/d') ?? '—' }}</dd></div>
        <div><dt>المتبقي</dt><dd>{{ $subscription->daysRemaining() }} يوم</dd></div>
    </dl>
</article>
