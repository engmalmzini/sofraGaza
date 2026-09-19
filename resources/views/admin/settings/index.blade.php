@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', 'إعدادات المنصة')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="admin-card admin-form">
    @csrf
    @foreach($settings as $setting)
        <div id="setting-{{ $setting->key }}">
            <label class="mb-1 block text-sm font-bold">{{ $setting->label }}</label>
            <input name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
        </div>
    @endforeach
    <button class="admin-btn admin-btn--primary w-fit">حفظ الإعدادات</button>
</form>
@endsection
