@extends('layouts.public')

@section('title', 'العضويات والمكافآت')
@section('body_class', 'sg-memberships-page')

@section('content')
<style>
    body.sg-memberships-page,
    body.sg-memberships-page .site-main {
        background: #FAF6F0 !important;
    }
    .ms-page {
        max-width: 80rem;
        margin: 0 auto;
        padding: 1.25rem 1.5rem 3rem;
    }
    .ms-crumb {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
        color: #9a8b80;
        margin-bottom: 0.85rem;
    }
    .ms-crumb a { color: #9a8b80; text-decoration: none; }
    .ms-crumb a:hover { color: #a33900; }
    .ms-crumb .is-current { color: #1a130f; }

    .ms-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }
    .ms-hero__copy {
        background: #fff;
        border-radius: 28px;
        padding: 1.6rem 1.7rem 1.45rem;
        box-shadow: 0 8px 28px rgba(80, 40, 10, 0.06);
        display: flex;
        flex-direction: column;
    }
    .ms-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: #c84500;
        margin-bottom: 0.45rem;
    }
    .ms-hero h1 {
        margin: 0;
        font-size: 2.15rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: #1a130f;
    }
    .ms-hero__lead {
        margin: 0.55rem 0 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: #8a7a6e;
        line-height: 1.65;
        max-width: 38rem;
    }
    .ms-perks {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
        margin-top: 1.2rem;
    }
    .ms-perk {
        background: #FAF6F0;
        border-radius: 16px;
        padding: 0.75rem 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .ms-perk .material-symbols-outlined { color: #c84500; font-size: 22px; }
    .ms-perk b { display: block; font-size: 0.82rem; font-weight: 800; color: #1a130f; }
    .ms-perk span { display: block; font-size: 0.68rem; font-weight: 600; color: #9a8b80; margin-top: 0.1rem; }

    .ms-hero__visual {
        position: relative;
        border-radius: 28px;
        overflow: hidden;
        min-height: 320px;
        background:
            radial-gradient(120% 80% at 20% 0%, rgba(255, 190, 140, 0.35), transparent 50%),
            linear-gradient(160deg, #1a130f 0%, #3d2418 55%, #c84500 140%);
        color: #fff;
        padding: 1.6rem 1.5rem;
        box-shadow: 0 8px 28px rgba(80, 40, 10, 0.12);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .ms-hero__visual::after {
        content: '';
        position: absolute;
        width: 14rem;
        height: 14rem;
        border-radius: 50%;
        border: 18px solid rgba(255,255,255,0.06);
        inset-inline-end: -4rem;
        bottom: -5rem;
        pointer-events: none;
    }
    .ms-hero__visual small {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #ffb089;
    }
    .ms-hero__visual strong {
        display: block;
        font-size: 1.55rem;
        font-weight: 900;
        margin-top: 0.35rem;
        line-height: 1.25;
    }
    .ms-hero__visual p {
        margin: 0.45rem 0 0;
        font-size: 0.82rem;
        font-weight: 600;
        color: rgba(255,255,255,0.78);
        max-width: 18rem;
        line-height: 1.55;
    }
    .ms-hero__pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        position: relative;
        z-index: 1;
    }
    .ms-hero__pills em {
        font-style: normal;
        background: rgba(255,255,255,0.12);
        border-radius: 9999px;
        padding: 0.38rem 0.75rem;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .ms-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        border-radius: 18px;
        padding: 0.95rem 1.1rem;
        margin-bottom: 1rem;
        font-size: 0.86rem;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(80, 40, 10, 0.04);
    }
    .ms-alert--warn { background: #fff7ed; color: #9a3412; }
    .ms-alert--info { background: #fff; color: #1a130f; }
    .ms-alert .material-symbols-outlined { color: #c84500; }

    .ms-current {
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 1.15rem;
        margin-bottom: 1.75rem;
        align-items: start;
    }
    .ms-current__details {
        background: #fff;
        border-radius: 24px;
        padding: 1.25rem 1.3rem 1.35rem;
        box-shadow: 0 8px 24px rgba(80, 40, 10, 0.06);
    }
    .ms-current__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #f0e7dc;
    }
    .ms-current__head h2 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: #1a130f;
    }
    .ms-current__head h2 .material-symbols-outlined { color: #c84500; }
    .ms-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.22rem 0.7rem;
        font-size: 0.7rem;
        font-weight: 800;
        background: #ecfdf3;
        color: #067647;
    }
    .ms-current__details ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }
    .ms-current__details li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        font-size: 0.84rem;
        font-weight: 700;
        color: #3d342c;
    }
    .ms-current__details li .material-symbols-outlined { color: #c84500; font-size: 18px; }
    .ms-note {
        margin-top: 1rem;
        background: #FAF6F0;
        border-radius: 16px;
        padding: 0.9rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #5c5148;
        line-height: 1.65;
    }
    .ms-note b { color: #1a130f; }
    .ms-note .mono {
        font-family: ui-monospace, monospace;
        background: #fff;
        border-radius: 8px;
        padding: 0.1rem 0.45rem;
        font-weight: 800;
    }
    .ms-form label {
        display: block;
        font-size: 0.78rem;
        font-weight: 800;
        color: #1a130f;
        margin: 0.9rem 0 0.4rem;
    }
    .ms-form input[type="text"],
    .ms-form input:not([type="file"]) {
        width: 100%;
        border: 1px solid #eadfd3;
        border-radius: 14px;
        padding: 0.7rem 0.9rem;
        font-size: 0.86rem;
        font-weight: 600;
        outline: none;
        background: #fff;
    }
    .ms-form input:focus { border-color: #c84500; }
    .ms-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        border: 0;
        border-radius: 9999px;
        padding: 0.85rem 1.1rem;
        background: #c84500;
        color: #fff;
        font-size: 0.88rem;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 8px 16px rgba(200, 69, 0, 0.22);
    }
    .ms-btn:hover { background: #b03d00; }
    .ms-status {
        margin-top: 0.9rem;
        border-radius: 14px;
        padding: 0.8rem 0.95rem;
        font-size: 0.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .ms-status--pending { background: #fff7ed; color: #9a3412; }
    .ms-status--ready { background: #ecfdf3; color: #067647; }

    .ms-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
        flex-wrap: wrap;
    }
    .ms-section-head h2 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #1a130f;
    }
    .ms-section-head p {
        margin: 0.3rem 0 0;
        font-size: 0.84rem;
        font-weight: 600;
        color: #8a7a6e;
    }
    .ms-section-head a {
        font-size: 0.78rem;
        font-weight: 800;
        color: #c84500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        white-space: nowrap;
    }

    .ms-plans {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.1rem;
        margin-bottom: 1.75rem;
    }
    .ms-plan {
        position: relative;
        background: #fff;
        border-radius: 24px;
        padding: 1.35rem 1.3rem 1.3rem;
        box-shadow: 0 8px 24px rgba(80, 40, 10, 0.05);
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .ms-plan:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(80, 40, 10, 0.1);
    }
    .ms-plan.is-popular {
        background: #1a130f;
        color: #fff;
    }
    .ms-plan.is-current { box-shadow: 0 0 0 2px #c84500, 0 10px 28px rgba(200, 69, 0, 0.12); }
    .ms-plan__badge {
        position: absolute;
        top: -11px;
        inset-inline-end: 1.2rem;
        background: #c84500;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 800;
        border-radius: 9999px;
        padding: 0.22rem 0.7rem;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }
    .ms-plan__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.7rem;
        margin-bottom: 0.35rem;
    }
    .ms-plan h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 900;
    }
    .ms-plan.is-popular h2 { color: #fff; }
    .ms-plan__disc {
        background: #fff7ed;
        color: #c84500;
        border-radius: 9999px;
        padding: 0.28rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .ms-plan.is-popular .ms-plan__disc { background: rgba(200, 69, 0, 0.25); color: #ffd0b5; }
    .ms-plan__desc {
        margin: 0.2rem 0 0;
        font-size: 0.78rem;
        font-weight: 600;
        color: #9a8b80;
    }
    .ms-plan.is-popular .ms-plan__desc { color: rgba(255,255,255,0.65); }
    .ms-plan__price {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        margin: 1rem 0;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0e7dc;
    }
    .ms-plan.is-popular .ms-plan__price { border-color: rgba(255,255,255,0.12); }
    .ms-plan__price b {
        font-size: 2.4rem;
        font-weight: 900;
        letter-spacing: -0.04em;
        line-height: 1;
    }
    .ms-plan__price span { font-size: 0.8rem; font-weight: 700; color: #9a8b80; }
    .ms-plan.is-popular .ms-plan__price span { color: rgba(255,255,255,0.6); }
    .ms-plan ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        flex: 1;
    }
    .ms-plan li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        font-size: 0.84rem;
        font-weight: 700;
    }
    .ms-plan li .material-symbols-outlined { color: #c84500; font-size: 18px; }
    .ms-plan.is-popular li .material-symbols-outlined { color: #ffb089; }
    .ms-plan__foot { margin-top: 1.2rem; }
    .ms-plan.is-popular .ms-btn { background: #c84500; }
    .ms-locked {
        text-align: center;
        background: #FAF6F0;
        color: #8a7a6e;
        border-radius: 16px;
        padding: 0.9rem;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .ms-current-flag {
        text-align: center;
        background: #ecfdf3;
        color: #067647;
        border-radius: 16px;
        padding: 0.9rem;
        font-size: 0.82rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }
    .ms-file {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        border: 1.5px dashed #eadfd3;
        background: #FAF6F0;
        border-radius: 16px;
        padding: 0.85rem 0.7rem;
        cursor: pointer;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 800;
        color: #5c5148;
        margin-bottom: 0.7rem;
    }
    .ms-plan.is-popular .ms-file {
        background: rgba(255,255,255,0.06);
        border-color: rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.8);
    }
    .ms-file:hover { border-color: #c84500; color: #c84500; }
    .ms-file input { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }

    .ms-steps {
        background: #fff;
        border-radius: 24px;
        padding: 1.25rem 1.3rem 1.4rem;
        box-shadow: 0 8px 24px rgba(80, 40, 10, 0.05);
        margin-bottom: 1.5rem;
    }
    .ms-steps h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: #1a130f;
    }
    .ms-steps h3 .material-symbols-outlined { color: #c84500; }
    .ms-steps > p {
        margin: 0.3rem 0 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #8a7a6e;
    }
    .ms-steps__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }
    .ms-step {
        background: #FAF6F0;
        border-radius: 18px;
        padding: 1rem 1.05rem;
    }
    .ms-step b {
        display: block;
        width: 2rem;
        height: 2rem;
        border-radius: 10px;
        background: #c84500;
        color: #fff;
        font-size: 0.85rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.65rem;
    }
    .ms-step h4 { margin: 0; font-size: 0.88rem; font-weight: 800; color: #1a130f; }
    .ms-step p { margin: 0.3rem 0 0; font-size: 0.75rem; font-weight: 600; color: #8a7a6e; line-height: 1.55; }

    .ms-pay > div {
        margin: 0;
        border: 0;
        background: #fff;
        box-shadow: 0 8px 24px rgba(80, 40, 10, 0.05);
    }

    @media (max-width: 1023px) {
        .ms-page { padding: 1rem 1rem 2.25rem; }
        .ms-hero,
        .ms-current,
        .ms-plans,
        .ms-steps__grid { grid-template-columns: 1fr; }
        .ms-hero h1 { font-size: 1.65rem; }
        .ms-hero__visual { min-height: 220px; }
        .ms-crumb { display: none; }
    }
</style>

<div class="ms-page">
    <section class="ms-hero">
        <div class="ms-hero__copy">
            <nav class="ms-crumb hidden lg:flex" aria-label="مسار التنقل">
                <a href="{{ route('home') }}">الرئيسية</a>
                <span>/</span>
                <span class="is-current">المكافآت</span>
            </nav>
            <div class="ms-kicker">
                <span class="material-symbols-outlined text-[16px]">stars</span>
                برنامج الولاء في غزة
            </div>
            <h1>العضويات والمكافآت</h1>
            <p class="ms-hero__lead">اشترك بتحويل شهري ثم أرفق إشعار الحوالة. بعد موافقة الإدارة تُفعّل بطاقة رقمية في ملفك الشخصي، ويُطبّق الخصم تلقائياً عند الدفع.</p>
            <div class="ms-perks">
                <div class="ms-perk">
                    <span class="material-symbols-outlined">percent</span>
                    <div><b>خصم مباشر</b><span>على جميع طلباتك</span></div>
                </div>
                <div class="ms-perk">
                    <span class="material-symbols-outlined">moped</span>
                    <div><b>توصيل مجاني</b><span>للباقات المؤهلة</span></div>
                </div>
                <div class="ms-perk">
                    <span class="material-symbols-outlined">toll</span>
                    <div><b>مضاعفة النقاط</b><span>لوجبات مجانية</span></div>
                </div>
                <div class="ms-perk">
                    <span class="material-symbols-outlined">credit_card</span>
                    <div><b>بطاقة مطاعم</b><span>رقمية وبلاستيكية</span></div>
                </div>
            </div>
        </div>
        <aside class="ms-hero__visual">
            <div>
                <small>SOFRA GAZA</small>
                <strong>بطاقة ذهبية لأهل غزة</strong>
                <p>حوّل عبر جوال باي أو بال باي أو بنك فلسطين، وأرفق الإشعار. الخصم يُحسب تلقائياً داخل المنصة.</p>
            </div>
            <div class="ms-hero__pills">
                <em>PalPay</em>
                <em>Jawwal Pay</em>
                <em>بنك فلسطين</em>
            </div>
        </aside>
    </section>

    @if($current?->isExpiringSoon())
        <div class="ms-alert ms-alert--warn">
            <span class="material-symbols-outlined">alarm</span>
            <div>
                <span class="font-extrabold">تنبيه انتهاء العضوية:</span>
                باقي {{ $current->daysRemaining() }} أيام على انتهاء عضويتك. جدّد من الأسفل حتى لا تفقد الخصم.
            </div>
        </div>
    @endif

    @if($pending)
        <div class="ms-alert ms-alert--info">
            <span class="material-symbols-outlined">hourglass_top</span>
            <div>
                <span class="font-extrabold">طلب العضوية قيد المراجعة:</span>
                طلب عضوية {{ $pending->membership->name }} بانتظار مراجعة الحوالة.
            </div>
        </div>
    @endif

    @if($current)
        <section class="ms-current">
            @include('partials.membership-card', ['subscription' => $current, 'holder' => auth()->user()])

            <article class="ms-current__details">
                <div class="ms-current__head">
                    <h2>
                        <span class="material-symbols-outlined">badge</span>
                        تفاصيل اشتراكك
                    </h2>
                    <span class="ms-pill">مفعّل</span>
                </div>

                <ul>
                    @foreach($current->membership->benefitsList() as $benefit)
                        <li>
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                    <li>
                        <span class="material-symbols-outlined">payments</span>
                        <span>قيمة الاشتراك {{ number_format($current->amount, 0) }} ₪ / شهر</span>
                    </li>
                </ul>

                <div class="ms-note">
                    <div class="font-extrabold text-[#1a130f] mb-1 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-[#c84500]">smartphone</span>
                        البطاقة الرقمية
                    </div>
                    <p>بطاقة حسابك ظاهرة أعلاه وفي الملف الشخصي بعد تفعيل الأدمن للحوالة. الخصم يُطبَّق تلقائياً عند الدفع على المنصة.</p>
                    <p class="mt-2">للمطاعم المشتركة: يمكن للطاقم التحقق برقم <strong class="mono">{{ $current->cardNumber() }}</strong>.</p>
                    <p class="mt-2 font-extrabold text-[#1a130f]">بطاقة المطعم البلاستيكية: {{ $current->cardStatusLabel() }}</p>
                    @if($current->card_note)
                        <p class="mt-2">ملاحظة الاستلام: {{ $current->card_note }}</p>
                    @endif
                </div>

                @if($current->canRequestCard())
                    <form method="POST" action="{{ route('memberships.card') }}" class="ms-form" data-once-submit>
                        @csrf
                        <label>عنوان أو فرع استلام البطاقة البلاستيكية (اختياري)</label>
                        <input name="card_note" value="{{ old('card_note') }}" placeholder="مثال: حي الرمال — استلام من المكتب">
                        <button class="ms-btn mt-3">طلب بطاقة المطعم</button>
                    </form>
                @elseif($current->card_status === 'pending')
                    <p class="ms-status ms-status--pending">
                        <span class="material-symbols-outlined text-[18px]">pending</span>
                        <span>طلب البطاقة البلاستيكية وصل للإدارة وهو قيد التجهيز.</span>
                    </p>
                @elseif($current->card_status === 'ready')
                    <p class="ms-status ms-status--ready">
                        <span class="material-symbols-outlined text-[18px]">task_alt</span>
                        <span>بطاقتك جاهزة للاستلام. أبرزها داخل المطاعم المشتركة لتأخذ خصمك.</span>
                    </p>
                @endif
            </article>
        </section>
    @endif

    <section>
        <div class="ms-section-head">
            <div>
                <h2>{{ $current ? 'ترقية أو تجديد' : 'اختر عضويتك' }}</h2>
                <p>اختر الباقة المناسبة لاستهلاكك واستمتع بخصومات حصرية وتوصيل مجاني ومضاعفة النقاط</p>
            </div>
            <a href="#payment-accounts">
                عرض حسابات الدفع
                <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
            </a>
        </div>

        <div class="ms-plans">
            @foreach($memberships as $membership)
                @php
                    $isCurrent = $current && $current->membership_id === $membership->id;
                    $isPopular = ! $isCurrent && ($membership->sort_order == 2 || $membership->discount_percent >= 10);
                @endphp
                <article class="ms-plan{{ $isCurrent ? ' is-current' : '' }}{{ $isPopular ? ' is-popular' : '' }}">
                    @if($isCurrent)
                        <div class="ms-plan__badge">
                            <span class="material-symbols-outlined text-[13px]">check</span>
                            عضويتك الحالية
                        </div>
                    @elseif($isPopular)
                        <div class="ms-plan__badge">
                            <span class="material-symbols-outlined text-[13px]">auto_awesome</span>
                            الأكثر توفيراً
                        </div>
                    @endif

                    <div class="ms-plan__top">
                        <h2>{{ $membership->name }}</h2>
                        <span class="ms-plan__disc">خصم {{ $membership->discount_percent }}%</span>
                    </div>

                    @if($membership->description)
                        <p class="ms-plan__desc">{{ $membership->description }}</p>
                    @endif

                    <div class="ms-plan__price">
                        <b>{{ number_format($membership->monthly_price) }}</b>
                        <span class="ils">₪</span>
                        <span>/ شهرياً</span>
                    </div>

                    <ul>
                        @foreach($membership->benefitsList() as $benefit)
                            <li>
                                <span class="material-symbols-outlined">check_circle</span>
                                <span>{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="ms-plan__foot">
                        @if($isCurrent)
                            <p class="ms-current-flag">
                                <span class="material-symbols-outlined text-[18px]">verified</span>
                                <span>هذه عضويتك الحالية</span>
                            </p>
                        @elseif($pending)
                            <p class="ms-locked">لا يمكن إرسال طلب جديد قبل مراجعة الحوالة الحالية.</p>
                        @elseif(auth()->check())
                            <form method="POST" action="{{ route('memberships.subscribe', $membership) }}" enctype="multipart/form-data" data-once-submit>
                                @csrf
                                <label class="ms-file">
                                    <input type="file" name="receipt" accept="image/*" required onchange="previewMembershipReceipt(this, 'receipt-badge-{{ $membership->id }}', 'receipt-placeholder-{{ $membership->id }}')">
                                    <div id="receipt-placeholder-{{ $membership->id }}" class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[20px]">add_photo_alternate</span>
                                        <span>أرفق إشعار التحويل (صورة)</span>
                                    </div>
                                    <div id="receipt-badge-{{ $membership->id }}" class="hidden items-center gap-1.5 text-emerald-700">
                                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                        <span class="receipt-filename truncate max-w-[200px]"></span>
                                    </div>
                                </label>
                                <button type="submit" class="ms-btn">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                    <span>{{ $current ? 'ترقية وأرفق الحوالة' : 'اشترك وأرفق الحوالة' }}</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="ms-btn">
                                <span class="material-symbols-outlined text-[18px]">login</span>
                                <span>سجّل دخول للاشتراك</span>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="ms-steps">
        <h3>
            <span class="material-symbols-outlined">route</span>
            كيف تشترك في 3 خطوات بسيطة؟
        </h3>
        <p>خطوات سريعة وسهلة لتفعيل عضويتك والبدء بالاستفادة من الخصومات فوراً</p>
        <div class="ms-steps__grid">
            <div class="ms-step">
                <b>1</b>
                <h4>اختر باقتك المفضلة</h4>
                <p>حدد الباقة المناسبة لاستهلاكك واعرف قيمة الاشتراك الشهري المناسبة لك.</p>
            </div>
            <div class="ms-step">
                <b>2</b>
                <h4>حوّل الرسوم المعتمدة</h4>
                <p>أرسل المبلغ عبر محفظة جوال باي، بال باي، أو بنك فلسطين من البيانات أدناه.</p>
            </div>
            <div class="ms-step">
                <b>3</b>
                <h4>أرفق الإشعار والتفعيل</h4>
                <p>ارفع صورة الإشعار وسيتم تفعيل بطاقتك الرقمية وخصوماتك فور مراجعة الإدارة.</p>
            </div>
        </div>
    </section>

    <div id="payment-accounts" class="ms-pay">
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
