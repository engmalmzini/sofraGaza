@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('tabs')
    <a href="{{ route('login') }}" class="sg-auth-tab is-active">
        <span class="material-symbols-outlined text-[17px]">login</span>
        <span>تسجيل الدخول</span>
    </a>
    <a href="{{ route('register') }}" class="sg-auth-tab">
        <span class="material-symbols-outlined text-[17px]">person_add</span>
        <span>إنشاء حساب</span>
    </a>
@endsection

@section('content')
<h1 class="sg-form-title">تسجيل الدخول</h1>
<p class="sg-form-lead">أهلاً بك مجدداً! أدخل رقم هاتفك وكلمة المرور للمتابعة</p>

@if ($errors->any())
    <div style="margin-bottom: 1.15rem; padding: 0.75rem 1rem; border-radius: 0.95rem; background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c; font-size: 0.82rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
        <span class="material-symbols-outlined text-[19px] text-red-600">error</span>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf

    {{-- Phone Field --}}
    <div class="sg-form-group">
        <label class="sg-form-label" for="phone">رقم الجوال</label>
        <div class="sg-input-wrapper">
            <span class="sg-input-icon">
                <span class="material-symbols-outlined">call</span>
            </span>
            <input
                id="phone"
                name="phone"
                type="tel"
                value="{{ old('phone') }}"
                required
                inputmode="numeric"
                autocomplete="tel"
                minlength="10"
                maxlength="15"
                pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}"
                placeholder="059XXXXXXX أو 056XXXXXXX"
                class="sg-input-field"
            >
        </div>
        @error('phone')
            <p class="sg-error-text">
                <span class="material-symbols-outlined text-[14px]">error</span>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    {{-- Password Field --}}
    <div class="sg-form-group">
        <label class="sg-form-label" for="password">كلمة المرور</label>
        <div class="sg-input-wrapper">
            <span class="sg-input-icon">
                <span class="material-symbols-outlined">lock</span>
            </span>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="sg-input-field"
            >
            <button type="button" class="sg-toggle-btn" onclick="toggleAuthPassword(this)" aria-label="عرض أو إخفاء كلمة المرور">
                <span class="material-symbols-outlined text-[19px]">visibility</span>
            </button>
        </div>
        @error('password')
            <p class="sg-error-text">
                <span class="material-symbols-outlined text-[14px]">error</span>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    {{-- Options: Remember Me --}}
    <div class="sg-form-options">
        <label class="sg-checkbox-label">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>تذكرني في هذا الجهاز</span>
        </label>
    </div>

    {{-- Submit Button --}}
    <button type="submit" class="sg-submit-btn">
        <span class="material-symbols-outlined text-[20px]">login</span>
        <span>دخول إلى حسابي</span>
    </button>
</form>

{{-- Social / Alternative Registration --}}
<div class="sg-auth-divider">
    <span>أو انضم كشريك في سفرة غزة</span>
</div>

<div class="sg-partner-grid">
    <a href="{{ route('partner.register') }}" class="sg-partner-card" title="تسجيل مطعم جديد">
        <span class="material-symbols-outlined">storefront</span>
        <span class="sg-partner-card__title">صاحب مطعم</span>
        <span class="sg-partner-card__sub">انضم لشبكة سفرة غزة</span>
    </a>
    <a href="{{ route('courier.register') }}" class="sg-partner-card" title="تسجيل مندوب توصيل">
        <span class="material-symbols-outlined">moped</span>
        <span class="sg-partner-card__title">كابتن توصيل</span>
        <span class="sg-partner-card__sub">انضم لأسطول المناديب</span>
    </a>
</div>

<p style="text-align: center; font-size: 0.72rem; color: #a8a29e; margin-top: 1rem; line-height: 1.5;">
    مندوب التوصيل والشركاء يسجلون عبر البوابة المخصصة، ويتم تفعيل الحسابات فور مراجعتها.
</p>
@endsection
