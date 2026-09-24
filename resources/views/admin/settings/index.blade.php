@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', 'إعدادات المنصة')

@section('content')
@php
    $hints = [
        'points_per_amount' => 'سعر اكتساب النقاط لكل المطاعم. يمكن تخصيص مطعم من صفحة المطعم، أو صنف من المنيو.',
        'points_redeem_per_amount' => 'سعر استبدال النقاط العام. يُتجاوز بسعر المطعم أو رقم ثابت للصنف.',
        'points_include_delivery' => '1 = طلب بـ 40₪ (طعام + توصيل) يكسب 40 نقطة عندما يكون السعر 1. 0 = الطعام فقط.',
    ];
@endphp
<form method="POST" action="{{ route('admin.settings.update') }}" class="admin-card admin-form">
    @csrf
    @foreach($settings as $setting)
    <div id="setting-{{ $setting->key }}">
        <label class="mb-1 block text-sm font-bold">{{ $setting->label }}</label>
        <input name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
        @if(! empty($hints[$setting->key]))
            <p class="mt-1 text-xs leading-6 text-on-surface-variant">{{ $hints[$setting->key] }}</p>
        @endif
    </div>
    @endforeach
    <button class="admin-btn admin-btn--primary w-fit">حفظ الإعدادات</button>
</form>
@endsection
