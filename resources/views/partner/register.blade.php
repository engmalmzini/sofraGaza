@extends('layouts.auth')

@section('title', 'تسجيل مطعم')
@section('shell_class', 'auth-shell--wide auth-shell--wizard')
@section('card_class', 'auth-card--wide auth-card--wizard')

@section('tabs')
    <a href="{{ route('register') }}" class="auth-tab">إنشاء حساب</a>
    <a href="{{ route('partner.register') }}" class="auth-tab is-active">مطعم</a>
    <a href="{{ route('courier.register') }}" class="auth-tab">توصيل</a>
@endsection

@section('content')
@php
    $cuisines = $cuisines ?? config('brand.cuisines', []);
    $areas = $areas ?? config('brand.areas', []);
    $stepMap = [
        1 => ['owner_name', 'owner_national_id', 'phone', 'email'],
        2 => ['password', 'password_confirmation'],
        3 => ['restaurant_name', 'type', 'cuisine', 'restaurant_phone', 'license_number'],
        4 => ['area', 'address', 'opens_at', 'closes_at', 'description'],
        5 => ['image', 'terms'],
    ];
    $skipPasswordStep = filled(session('partner_register.password'))
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
        ['n' => 1, 'title' => 'صاحب المطعم', 'hint' => 'الاسم والهوية والتواصل', 'short' => 'صاحب'],
        ['n' => 2, 'title' => 'كلمة المرور', 'hint' => 'حماية الحساب', 'short' => 'مرور'],
        ['n' => 3, 'title' => 'بيانات المطعم', 'hint' => 'الاسم والنوع والهاتف', 'short' => 'مطعم'],
        ['n' => 4, 'title' => 'الموقع والدوام', 'hint' => 'العنوان وساعات العمل', 'short' => 'موقع'],
        ['n' => 5, 'title' => 'المراجعة', 'hint' => 'الصورة والموافقة', 'short' => 'نشر'],
    ];
@endphp

<div class="partner-wizard" data-partner-wizard data-start-step="{{ $startStep }}">
    <ol class="partner-wizard__nav" aria-label="خطوات تسجيل المطعم">
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
        <h1 class="auth-title">سجّل مطعمك</h1>
        <p class="auth-lead" data-step-lead>أدخل البيانات على خمس خطوات قصيرة. يظهر المطعم للزبائن بعد موافقة الإدارة.</p>

        <form method="POST" action="{{ route('partner.register') }}" enctype="multipart/form-data" class="auth-form partner-wizard__form">
            @csrf

            <section class="partner-wizard__panel" data-step-panel="1" @if($startStep !== 1) hidden @endif>
                <p class="auth-kicker">الخطوة 1 من 5 — صاحب المطعم</p>
                <div class="auth-grid-2">
                    <label class="auth-field">
                        <input name="owner_name" value="{{ old('owner_name') }}" required maxlength="120" placeholder="الاسم الرباعي">
                    </label>
                    <label class="auth-field">
                        <input name="owner_national_id" value="{{ old('owner_national_id') }}" required inputmode="numeric" maxlength="9" minlength="9" pattern="[0-9]{9}" placeholder="رقم الهوية">
                    </label>
                    <label class="auth-field">
                        <input name="phone" value="{{ old('phone') }}" required inputmode="numeric" minlength="10" maxlength="15" pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}" placeholder="059XXXXXXXX">
                    </label>
                    <label class="auth-field auth-field--icon">
                        <span class="material-symbols-outlined">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="البريد الإلكتروني (اختياري)">
                    </label>
                </div>
                @foreach(['owner_name', 'owner_national_id', 'phone', 'email'] as $field)
                    @error($field)
                        <p class="partner-wizard__error">{{ $message }}</p>
                    @enderror
                @endforeach
            </section>

            <section class="partner-wizard__panel" data-step-panel="2" @if($startStep !== 2) hidden @endif>
                <p class="auth-kicker">الخطوة 2 من 5 — كلمة المرور</p>
                <div class="auth-grid-2">
                    <label class="auth-field">
                        <input type="password" name="password" value="{{ old('password', session('partner_register.password')) }}" required minlength="6" autocomplete="new-password" placeholder="كلمة المرور">
                    </label>
                    <label class="auth-field">
                        <input type="password" name="password_confirmation" value="{{ old('password_confirmation', session('partner_register.password_confirmation')) }}" required minlength="6" autocomplete="new-password" placeholder="تأكيد كلمة المرور">
                    </label>
                </div>
                @error('password')
                    <p class="partner-wizard__error">{{ $message }}</p>
                @enderror
            </section>

            <section class="partner-wizard__panel" data-step-panel="3" @if($startStep !== 3) hidden @endif>
                <p class="auth-kicker">الخطوة 3 من 5 — بيانات المطعم</p>
                <label class="auth-field">
                    <input name="restaurant_name" value="{{ old('restaurant_name') }}" required maxlength="150" placeholder="اسم المطعم أو الكافي">
                </label>
                <div class="auth-grid-2">
                    <label class="auth-field">
                        <select name="type" required>
                            <option value="restaurant" @selected(old('type', 'restaurant') === 'restaurant')>مطعم</option>
                            <option value="cafe" @selected(old('type') === 'cafe')>كافي</option>
                        </select>
                    </label>
                    <label class="auth-field">
                        <select name="cuisine" required>
                            <option value="">نوع المطبخ</option>
                            @foreach($cuisines as $key => $label)
                                <option value="{{ $key }}" @selected(old('cuisine') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="auth-field">
                        <input name="restaurant_phone" value="{{ old('restaurant_phone') }}" required inputmode="numeric" minlength="10" maxlength="15" pattern="{{ \App\Support\PalestinianPhone::HTML_PATTERN }}" placeholder="059XXXXXXXX">
                    </label>
                    <label class="auth-field">
                        <input name="license_number" value="{{ old('license_number') }}" placeholder="رقم الرخصة (اختياري)">
                    </label>
                </div>
                @foreach(['restaurant_name', 'type', 'cuisine', 'restaurant_phone'] as $field)
                    @error($field)
                        <p class="partner-wizard__error">{{ $message }}</p>
                    @enderror
                @endforeach
            </section>

            <section class="partner-wizard__panel" data-step-panel="4" @if($startStep !== 4) hidden @endif>
                <p class="auth-kicker">الخطوة 4 من 5 — الموقع والدوام</p>
                <div class="auth-grid-2">
                    <label class="auth-field">
                        <select name="area" required>
                            <option value="">المنطقة</option>
                            @foreach($areas as $area)
                                <option value="{{ $area['key'] }}" @selected(old('area') === $area['key'])>{{ $area['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="auth-field">
                        <input name="address" value="{{ old('address') }}" required maxlength="255" placeholder="العنوان التفصيلي">
                    </label>
                    <label class="auth-field">
                        <input type="time" name="opens_at" value="{{ old('opens_at', '09:00') }}" required aria-label="يفتح الساعة">
                    </label>
                    <label class="auth-field">
                        <input type="time" name="closes_at" value="{{ old('closes_at', '23:00') }}" required aria-label="يغلق الساعة">
                    </label>
                </div>
                <label class="auth-field auth-field--area">
                    <textarea name="description" rows="3" required minlength="20" maxlength="1000" placeholder="وصف المطعم وأشهر أطباقه">{{ old('description') }}</textarea>
                </label>
                @foreach(['area', 'address', 'opens_at', 'closes_at', 'description'] as $field)
                    @error($field)
                        <p class="partner-wizard__error">{{ $message }}</p>
                    @enderror
                @endforeach
            </section>

            <section class="partner-wizard__panel" data-step-panel="5" @if($startStep !== 5) hidden @endif>
                <p class="auth-kicker">الخطوة 5 من 5 — المراجعة والنشر</p>
                <label class="auth-upload">
                    <span class="material-symbols-outlined">add_photo_alternate</span>
                    <span>صورة الغلاف (اختياري)</span>
                    <input type="file" name="image" accept="image/*">
                </label>
                <label class="auth-remember auth-remember--start">
                    <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
                    <span>أقرّ بصحة البيانات وأوافق على مراجعة الإدارة قبل نشر المطعم.</span>
                </label>
                @error('image')
                    <p class="partner-wizard__error">{{ $message }}</p>
                @enderror
                @error('terms')
                    <p class="partner-wizard__error">{{ $message }}</p>
                @enderror
            </section>

            <div class="partner-wizard__actions">
                <button type="button" class="partner-wizard__back" data-wizard-prev>السابق</button>
                <button type="button" class="auth-submit partner-wizard__next" data-wizard-next>التالي</button>
                <button type="submit" class="auth-submit" data-wizard-submit hidden>إنشاء حساب المطعم</button>
            </div>
        </form>

        <div class="partner-wizard__intro" data-wizard-intro @if($startStep !== 1) hidden @endif>
            <div class="auth-split"><span>لديك حساب؟</span></div>
            <div class="auth-socials">
                <a href="{{ route('login') }}" class="auth-social">
                    <span class="material-symbols-outlined">login</span>
                    تسجيل الدخول
                </a>
                <a href="{{ route('register') }}" class="auth-social">
                    <span class="material-symbols-outlined">person</span>
                    حساب زبون
                </a>
            </div>
        </div>
        <p class="auth-legal">يمكنك تجهيز المنيو من لوحتك أثناء التحقق، ولن يظهر المطعم للزبائن قبل الموافقة.</p>
    </div>
</div>
@endsection
