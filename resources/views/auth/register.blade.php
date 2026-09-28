@extends('layouts.auth')

@section('title', 'إنشاء حساب')

@section('tabs')
    <a href="{{ route('register') }}" class="auth-tab is-active">إنشاء حساب</a>
    <a href="{{ route('login') }}" class="auth-tab">دخول</a>
@endsection

@section('content')
@php
    $step = $step ?? 'phone';
    $verifiedPhone = $verifiedPhone ?? null;
@endphp

<h1 class="auth-title">إنشاء حساب</h1>
<p class="auth-lead">نبدأ بالتحقق من رقم الجوال، ثم تكمل بيانات الحساب.</p>

<ol class="auth-steps" aria-label="خطوات إنشاء الحساب">
    <li @class(['is-current' => $step === 'phone'])>الهاتف</li>
    <li @class(['is-current' => $step === 'code'])>التحقق</li>
    <li @class(['is-current' => $step === 'account'])>الحساب</li>
</ol>

@if($step === 'phone')
    <form method="POST" action="{{ route('register.phone') }}" class="auth-form">
        @csrf
        <label class="auth-field">
            <input name="phone" value="{{ old('phone') }}" required inputmode="numeric" autocomplete="tel" minlength="10" maxlength="10" pattern="{{ \App\Support\PalestinianPhone::CUSTOMER_HTML_PATTERN }}" placeholder="05XXXXXXXX">
        </label>
        @error('phone')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <p class="auth-legal auth-legal--start">10 أرقام تبدأ بـ 05. سنرسل رمز تحقق من 6 أرقام برسالة SMS.</p>
        <button type="submit" class="auth-submit">إرسال رمز التحقق</button>
    </form>
@elseif($step === 'code')
    <form method="POST" action="{{ route('register.verify') }}" class="auth-form">
        @csrf
        <p class="auth-lead">أدخل الرمز الذي وصل إلى {{ \App\Support\PalestinianPhone::mask($verifiedPhone ?? '') }}</p>
        <label class="auth-field auth-field--otp">
            <input name="code" value="{{ old('code') }}" required inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" pattern="[0-9]{6}" placeholder="رمز التحقق">
        </label>
        @error('code')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <button type="submit" class="auth-submit">تأكيد الرمز</button>
    </form>
    <div class="auth-actions">
        <form method="POST" action="{{ route('register.phone.resend') }}">
            @csrf
            <button type="submit" class="auth-text-btn">إعادة إرسال الرمز</button>
        </form>
        <form method="POST" action="{{ route('register.phone.reset') }}">
            @csrf
            <button type="submit" class="auth-text-btn">تغيير الرقم</button>
        </form>
    </div>
@else
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <p class="auth-lead">الرقم المتحقق: <span dir="ltr">{{ $verifiedPhone }}</span></p>
        <label class="auth-field">
            <input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" placeholder="الاسم الكامل">
        </label>
        @error('name')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <label class="auth-field">
            <input type="password" name="password" autocomplete="new-password" placeholder="كلمة المرور">
        </label>
        <label class="auth-field">
            <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="تأكيد كلمة المرور">
        </label>
        @error('password')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <button type="submit" class="auth-submit">إنشاء حساب</button>
    </form>
    <form method="POST" action="{{ route('register.phone.reset') }}" class="auth-actions">
        @csrf
        <button type="submit" class="auth-text-btn">تغيير رقم الهاتف</button>
    </form>
@endif

<div class="auth-split"><span>أو سجّل كـ</span></div>
<div class="auth-socials">
    <a href="{{ route('partner.register') }}" class="auth-social">
        <span class="material-symbols-outlined">storefront</span>
        صاحب مطعم
    </a>
    <a href="{{ route('courier.register') }}" class="auth-social">
        <span class="material-symbols-outlined">moped</span>
        مندوب توصيل
    </a>
</div>
<p class="auth-legal">بإنشاء الحساب فإنك توافق على شروط الخدمة في سفرة غزة.</p>
@endsection
