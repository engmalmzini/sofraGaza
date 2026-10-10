@extends('layouts.auth')

@section('title', 'إنشاء حساب جديد')

@section('tabs')
    <a href="{{ route('login') }}" class="sg-auth-tab">
        <span class="material-symbols-outlined text-[17px]">login</span>
        <span>تسجيل الدخول</span>
    </a>
    <a href="{{ route('register') }}" class="sg-auth-tab is-active">
        <span class="material-symbols-outlined text-[17px]">person_add</span>
        <span>إنشاء حساب</span>
    </a>
@endsection

@section('content')
@php
    $step = $step ?? 'phone';
    $verifiedPhone = $verifiedPhone ?? null;
@endphp

<h1 class="sg-form-title">إنشاء حساب جديد</h1>
<p class="sg-form-lead">انضم إلى سفرة غزة واستمتع بأشهى وجبات القطاع مع نقاط مكافآت فورية.</p>

@if(filled($referralCode ?? null) && $step !== 'account')
    <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.95rem; background: #fffbeb; border: 1.5px solid #fef3c7; color: #92400e; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 0.45rem;">
        <span class="material-symbols-outlined text-[18px] text-amber-600">redeem</span>
        <span>كود الدعوة محفوظ: <strong dir="ltr">{{ $referralCode }}</strong> — ستحصل على نقاط ترحيبية فور إتمام التسجيل!</span>
    </div>
@endif

<ol class="auth-steps" aria-label="خطوات إنشاء الحساب">
    <li @class(['is-current' => $step === 'phone'])>
        <span>1. الهاتف</span>
    </li>
    <li @class(['is-current' => $step === 'code'])>
        <span>2. التحقق</span>
    </li>
    <li @class(['is-current' => $step === 'account'])>
        <span>3. البيانات</span>
    </li>
</ol>

@if($step === 'phone')
    <form method="POST" action="{{ route('register.phone') }}" class="auth-form">
        @csrf
        <div class="sg-form-group">
            <label class="sg-form-label" for="reg-phone">رقم الجوال</label>
            <div class="sg-input-wrapper">
                <span class="sg-input-icon">
                    <span class="material-symbols-outlined">call</span>
                </span>
                <input
                    id="reg-phone"
                    name="phone"
                    type="tel"
                    value="{{ old('phone') }}"
                    required
                    inputmode="numeric"
                    autocomplete="tel"
                    minlength="10"
                    maxlength="10"
                    pattern="{{ \App\Support\PalestinianPhone::CUSTOMER_HTML_PATTERN }}"
                    placeholder="05XXXXXXXX"
                    class="sg-input-field"
                >
            </div>
            @error('phone')
                <p class="sg-error-text">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
            <p style="font-size: 0.75rem; color: #78716c; margin-top: 0.4rem;">
                10 أرقام تبدأ بـ 05. سنرسل رمز تحقق من 6 أرقام عبر SMS للتأكيد.
            </p>
        </div>

        <button type="submit" class="sg-submit-btn">
            <span>إرسال رمز التحقق</span>
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </button>
    </form>
@elseif($step === 'code')
    <form method="POST" action="{{ route('register.verify') }}" class="auth-form">
        @csrf
        <p style="font-size: 0.85rem; color: #443833; margin-bottom: 0.85rem; font-weight: 600;">
            أدخل رمز التحقق المرسل إلى: <strong dir="ltr" style="color: #a33900;">{{ \App\Support\PalestinianPhone::mask($verifiedPhone ?? '') }}</strong>
        </p>

        <div class="sg-form-group">
            <label class="sg-form-label" for="otp-code">رمز التأكيد (6 أرقام)</label>
            <div class="sg-input-wrapper">
                <span class="sg-input-icon">
                    <span class="material-symbols-outlined">sms</span>
                </span>
                <input
                    id="otp-code"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    minlength="6"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="123456"
                    class="sg-input-field"
                    style="letter-spacing: 0.2em; font-size: 1.1rem; font-weight: 800;"
                >
            </div>
            @error('code')
                <p class="sg-error-text">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <button type="submit" class="sg-submit-btn">
            <span>تأكيد الرمز والمتابعة</span>
            <span class="material-symbols-outlined text-[18px]">check</span>
        </button>
    </form>

    <div class="auth-actions" style="margin-top: 1rem; display: flex; justify-content: space-between;">
        <form method="POST" action="{{ route('register.phone.resend') }}">
            @csrf
            <button type="submit" class="auth-text-btn">إعادة إرسال الرمز</button>
        </form>
        <form method="POST" action="{{ route('register.phone.reset') }}">
            @csrf
            <button type="submit" class="auth-text-btn">تغيير رقم الجوال</button>
        </form>
    </div>
@else
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <p style="font-size: 0.82rem; color: #78716c; margin-bottom: 1rem;">
            الرقم الموثق: <strong dir="ltr" style="color: #a33900;">{{ $verifiedPhone }}</strong>
        </p>

        {{-- Name --}}
        <div class="sg-form-group">
            <label class="sg-form-label" for="reg-name">الاسم الكامل</label>
            <div class="sg-input-wrapper">
                <span class="sg-input-icon">
                    <span class="material-symbols-outlined">person</span>
                </span>
                <input
                    id="reg-name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="120"
                    autocomplete="name"
                    placeholder="محمد الغزاوي"
                    class="sg-input-field"
                >
            </div>
            @error('name')
                <p class="sg-error-text">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="sg-form-group">
            <label class="sg-form-label" for="reg-password">كلمة المرور</label>
            <div class="sg-input-wrapper">
                <span class="sg-input-icon">
                    <span class="material-symbols-outlined">lock</span>
                </span>
                <input
                    id="reg-password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="sg-input-field"
                >
                <button type="button" class="sg-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="عرض أو إخفاء كلمة المرور">
                    <span class="material-symbols-outlined text-[19px]">visibility</span>
                </button>
            </div>
        </div>

        {{-- Password Confirmation --}}
        <div class="sg-form-group">
            <label class="sg-form-label" for="reg-password-confirm">تأكيد كلمة المرور</label>
            <div class="sg-input-wrapper">
                <span class="sg-input-icon">
                    <span class="material-symbols-outlined">lock_reset</span>
                </span>
                <input
                    id="reg-password-confirm"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="sg-input-field"
                >
            </div>
            @error('password')
                <p class="sg-error-text">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        {{-- Referral Code --}}
        <div class="sg-form-group">
            <label class="sg-form-label" for="reg-ref">كود الدعوة (اختياري)</label>
            <div class="sg-input-wrapper">
                <span class="sg-input-icon">
                    <span class="material-symbols-outlined">card_giftcard</span>
                </span>
                <input
                    id="reg-ref"
                    name="referral_code"
                    value="{{ old('referral_code', $referralCode) }}"
                    maxlength="12"
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                    placeholder="SOFRA2026"
                    dir="ltr"
                    class="sg-input-field"
                >
            </div>
            <p style="font-size: 0.72rem; color: #78716c; margin-top: 0.35rem;">
                إذا دعاك صديق، أدخل كوده لتحصل أنت وهو على {{ $inviteePoints ?? 50 }} نقطة إضافية.
            </p>
            @error('referral_code')
                <p class="sg-error-text">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <button type="submit" class="sg-submit-btn" style="margin-top: 1.25rem;">
            <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
            <span>إتمام إنشاء الحساب</span>
        </button>
    </form>

    <div style="text-align: center; margin-top: 1rem;">
        <form method="POST" action="{{ route('register.phone.reset') }}">
            @csrf
            <button type="submit" class="auth-text-btn">تغيير رقم الجوال</button>
        </form>
    </div>
@endif

{{-- Partner Registration Grid --}}
<div class="sg-auth-divider">
    <span>أو سجّل كشريك عمل</span>
</div>

<div class="sg-partner-grid">
    <a href="{{ route('partner.register') }}" class="sg-partner-card" title="تسجيل مطعم جديد">
        <span class="material-symbols-outlined">storefront</span>
        <span class="sg-partner-card__title">صاحب مطعم</span>
        <span class="sg-partner-card__sub">انضم لشبكة المطاعم</span>
    </a>
    <a href="{{ route('courier.register') }}" class="sg-partner-card" title="تسجيل مندوب توصيل">
        <span class="material-symbols-outlined">moped</span>
        <span class="sg-partner-card__title">كابتن توصيل</span>
        <span class="sg-partner-card__sub">انضم لأسطول المناديب</span>
    </a>
</div>

<p style="text-align: center; font-size: 0.72rem; color: #a8a29e; margin-top: 1rem;">
    بإنشاء الحساب فإنك توافق على شروط الخدمة وسياسة الخصوصية في منصة سفرة غزة.
</p>
@endsection
