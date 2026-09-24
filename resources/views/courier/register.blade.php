@extends('layouts.auth')

@section('title', 'تسجيل مندوب توصيل')
@section('shell_class', 'auth-shell--wide auth-shell--wizard')
@section('card_class', 'auth-card--wide auth-card--wizard')

@section('tabs')
    <a href="{{ route('register') }}" class="auth-tab">إنشاء حساب</a>
    <a href="{{ route('partner.register') }}" class="auth-tab">مطعم</a>
    <a href="{{ route('courier.register') }}" class="auth-tab is-active">توصيل</a>
@endsection

@section('content')
@php
    $stepMap = [
        1 => ['name', 'phone'],
        2 => ['password', 'password_confirmation'],
        3 => ['bike_type', 'photo', 'bike_photo', 'terms'],
    ];
    $skipPasswordStep = filled(session('courier_register.password'))
        && ! $errors->hasAny(['password', 'password_confirmation']);
    $startStep = 1;
    foreach ($stepMap as $step => $keys) {
        $keys = $skipPasswordStep
            ? array_values(array_filter($keys, fn ($key) => ! in_array($key, ['password', 'password_confirmation'], true)))
            : $keys;

        if ($keys !== [] && $errors->hasAny($keys)) {
            $startStep = $step;
            break;
        }
    }
    $steps = [
        ['n' => 1, 'title' => 'بياناتك', 'hint' => 'الاسم ورقم الهاتف', 'short' => 'اسم'],
        ['n' => 2, 'title' => 'كلمة المرور', 'hint' => 'حماية الحساب', 'short' => 'مرور'],
        ['n' => 3, 'title' => 'الدراجة والصور', 'hint' => 'النوع والصور للمراجعة', 'short' => 'دراجة'],
    ];
@endphp

<div class="partner-wizard" data-partner-wizard data-start-step="{{ $startStep }}">
    <ol class="partner-wizard__nav" aria-label="خطوات تسجيل المندوب">
        @foreach($steps as $item)
            <li>
                <button
                    type="button"
                    class="partner-wizard__btn @if($item['n'] === $startStep) is-current @endif"
                    data-step-btn="{{ $item['n'] }}"
                    @if($item['n'] === $startStep) aria-current="step" @endif
                >
                    <span class="partner-wizard__num">{{ $item['n'] }}</span>
                    <span class="partner-wizard__copy">
                        <strong>{{ $item['title'] }}</strong>
                        <em>{{ $item['hint'] }}</em>
                    </span>
                    <span class="partner-wizard__short">{{ $item['short'] }}</span>
                </button>
            </li>
        @endforeach
    </ol>

    <div class="partner-wizard__main">
        <h1 class="auth-title">سجّل كمندوب توصيل</h1>
        <p class="auth-lead">أدخل بياناتك وصورك. بعد موافقة الإدارة يصلك إشعار برابط لوحتك، والطلبات تُرسل لك شخصياً.</p>

        <form method="POST" action="{{ route('courier.register') }}" enctype="multipart/form-data" class="auth-form partner-wizard__form">
            @csrf

            <section class="partner-wizard__panel" data-step-panel="1" @if($startStep !== 1) hidden @endif>
                <p class="auth-kicker">الخطوة 1 من 3 — بياناتك</p>
                <label class="auth-field">
                    <input name="name" value="{{ old('name') }}" required maxlength="120" placeholder="الاسم الرباعي">
                </label>
                <label class="auth-field">
                    <input name="phone" value="{{ old('phone') }}" required inputmode="numeric" minlength="10" maxlength="15" pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}" placeholder="059XXXXXXXX">
                </label>
                @foreach(['name', 'phone'] as $field)
                    @error($field)
                        <p class="partner-wizard__error">{{ $message }}</p>
                    @enderror
                @endforeach
            </section>

            <section class="partner-wizard__panel" data-step-panel="2" @if($startStep !== 2) hidden @endif>
                <p class="auth-kicker">الخطوة 2 من 3 — كلمة المرور</p>
                <div class="auth-grid-2">
                    <label class="auth-field">
                        <input type="password" name="password" value="{{ old('password', session('courier_register.password')) }}" required minlength="6" autocomplete="new-password" placeholder="كلمة المرور">
                    </label>
                    <label class="auth-field">
                        <input type="password" name="password_confirmation" value="{{ old('password_confirmation', session('courier_register.password_confirmation')) }}" required minlength="6" autocomplete="new-password" placeholder="تأكيد كلمة المرور">
                    </label>
                </div>
                @error('password')
                    <p class="partner-wizard__error">{{ $message }}</p>
                @enderror
            </section>

            <section class="partner-wizard__panel" data-step-panel="3" @if($startStep !== 3) hidden @endif>
                <p class="auth-kicker">الخطوة 3 من 3 — الدراجة والصور</p>
                <label class="auth-field">
                    <select name="bike_type" required>
                        <option value="">نوع الدراجة</option>
                        <option value="bicycle" @selected(old('bike_type') === 'bicycle')>دراجة هوائية</option>
                        <option value="electric" @selected(old('bike_type') === 'electric')>دراجة كهربائية</option>
                    </select>
                </label>
                <label class="auth-upload">
                    <span class="material-symbols-outlined">person</span>
                    <span>صورة شخصية</span>
                    <input type="file" name="photo" accept="image/*" required>
                </label>
                <label class="auth-upload">
                    <span class="material-symbols-outlined">pedal_bike</span>
                    <span>صورة الدراجة</span>
                    <input type="file" name="bike_photo" accept="image/*" required>
                </label>
                <label class="auth-remember auth-remember--start">
                    <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
                    <span>أقرّ بصحة البيانات وأوافق على مراجعة الإدارة قبل تفعيل حساب التوصيل.</span>
                </label>
                @foreach(['bike_type', 'photo', 'bike_photo', 'terms'] as $field)
                    @error($field)
                        <p class="partner-wizard__error">{{ $message }}</p>
                    @enderror
                @endforeach
            </section>

            <div class="partner-wizard__actions">
                <button type="button" class="partner-wizard__back" data-wizard-prev>السابق</button>
                <button type="button" class="auth-submit partner-wizard__next" data-wizard-next>التالي</button>
                <button type="submit" class="auth-submit" data-wizard-submit hidden>إرسال طلب الانضمام</button>
            </div>
        </form>

        <div class="partner-wizard__intro" data-wizard-intro @if($startStep !== 1) hidden @endif>
            <div class="auth-split"><span>لديك حساب؟</span></div>
            <div class="auth-socials">
                <a href="{{ route('login') }}" class="auth-social">
                    <span class="material-symbols-outlined">login</span>
                    تسجيل الدخول
                </a>
                <a href="{{ route('partner.register') }}" class="auth-social">
                    <span class="material-symbols-outlined">storefront</span>
                    صاحب مطعم
                </a>
            </div>
        </div>
        <p class="auth-legal">بعد القبول تظهر لك الطلبات التي ترسلها الإدارة لك فقط، وليس لكل المندوبين.</p>
    </div>
</div>
@endsection
