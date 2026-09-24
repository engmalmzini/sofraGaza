@extends('layouts.auth')

@section('title', 'إنشاء حساب')

@section('tabs')
    <a href="{{ route('register') }}" class="auth-tab is-active">إنشاء حساب</a>
    <a href="{{ route('login') }}" class="auth-tab">دخول</a>
@endsection

@section('content')
<h1 class="auth-title">إنشاء حساب</h1>
<form method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf
    <div class="auth-grid-2">
        <label class="auth-field">
            <input name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" placeholder="الاسم الأول">
        </label>
        <label class="auth-field">
            <input name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" placeholder="اسم العائلة">
        </label>
    </div>
    <label class="auth-field auth-field--icon">
        <span class="material-symbols-outlined">mail</span>
        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="البريد الإلكتروني (اختياري)">
    </label>
    <label class="auth-field">
        <input name="phone" value="{{ old('phone') }}" required inputmode="numeric" autocomplete="tel" minlength="10" maxlength="15" pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}" placeholder="059XXXXXXXX">
    </label>
    <label class="auth-field">
        <input type="password" name="password" autocomplete="new-password" placeholder="كلمة المرور">
    </label>
    <label class="auth-field">
        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="تأكيد كلمة المرور">
    </label>
    <button type="submit" class="auth-submit">إنشاء حساب</button>
</form>

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
