@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('tabs')
    <a href="{{ route('register') }}" class="auth-tab">إنشاء حساب</a>
    <a href="{{ route('login') }}" class="auth-tab is-active">دخول</a>
@endsection

@section('content')
<h1 class="auth-title">تسجيل الدخول</h1>
<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf
    <label class="auth-field">
        <input name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel" minlength="10" maxlength="15" pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}" placeholder="059XXXXXXXX">
    </label>
    <label class="auth-field">
        <input type="password" name="password" autocomplete="current-password" placeholder="كلمة المرور">
    </label>
    <label class="auth-remember">
        <input type="checkbox" name="remember" value="1">
        <span>تذكرني</span>
    </label>
    <button type="submit" class="auth-submit">دخول</button>
</form>

<div class="auth-split"><span>أو سجّل كـ</span></div>
<div class="auth-socials">
    <a href="{{ route('register') }}" class="auth-social">
        <span class="material-symbols-outlined">person_add</span>
        حساب زبون
    </a>
    <a href="{{ route('courier.register') }}" class="auth-social">
        <span class="material-symbols-outlined">moped</span>
        مندوب توصيل
    </a>
</div>
<p class="auth-legal">مندوب التوصيل يسجّل من صفحة خاصة، وبعد القبول يدخل بنفس رقم الهاتف.</p>
@endsection
