@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', $adminUser ? 'تعديل صلاحيات '.$adminUser->name : 'إضافة مدير')

@section('content')
<form method="POST" action="{{ $adminUser ? route('admin.team.update', $adminUser) : route('admin.team.store') }}" class="max-w-3xl space-y-4">
    @csrf
    @if($adminUser)
        @method('PUT')
    @endif

    <section class="admin-card space-y-3">
        <h2>بيانات الحساب</h2>
        <div>
            <label class="block text-xs font-bold mb-1">الاسم</label>
            <input type="text" name="name" value="{{ old('name', $adminUser->name ?? '') }}" required placeholder="مثلاً: محمد المزيني">
        </div>
        <div>
            <label class="block text-xs font-bold mb-1">رقم الجوال</label>
            <input type="tel" name="phone" dir="ltr" value="{{ old('phone', $adminUser->phone ?? '') }}" required placeholder="059XXXXXXXX">
        </div>
        <div>
            <label class="block text-xs font-bold mb-1">البريد (اختياري)</label>
            <input type="email" name="email" dir="ltr" value="{{ old('email', $adminUser->email ?? '') }}">
        </div>
        <div>
            <label class="block text-xs font-bold mb-1">كلمة المرور {{ $adminUser ? '(اتركها فارغة للإبقاء عليها)' : '' }}</label>
            <input type="password" name="password" {{ $adminUser ? '' : 'required' }} minlength="6">
        </div>
        @if($adminUser)
            <label class="flex items-center gap-2 text-sm font-bold">
                <input type="checkbox" name="admin_active" value="1" @checked(old('admin_active', $adminUser->admin_active))>
                الحساب نشط ويقدر يدخل اللوحة
            </label>
            @if(! $adminUser->isSuperAdmin())
                <label class="flex items-center gap-2 text-sm font-bold text-primary">
                    <input type="checkbox" name="make_super" value="1">
                    ترقيته لمدير أعلى (لوحة كاملة)
                </label>
            @endif
        @endif
    </section>

    @if(! $adminUser?->isSuperAdmin())
        <section class="admin-card space-y-3">
            <h2>شو يقدر يتابع؟</h2>
            <p class="text-xs text-slate-500">حدّد الأقسام اللي تظهر في لوحته. الباقي مخفي عنه.</p>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($modules as $key => $module)
                    <label class="flex items-start gap-2 rounded-xl border border-slate-200 p-3 cursor-pointer hover:border-primary/40">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" class="mt-1"
                               @checked(in_array($key, old('permissions', $adminUser?->adminPermissionKeys() ?? []), true))>
                        <span>
                            <strong class="block text-sm">{{ $module['label'] }}</strong>
                            <small class="text-slate-500">{{ $module['hint'] }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>
    @else
        <p class="text-sm text-slate-500">هذا الحساب مدير أعلى — يشوف كل اللوحة ويقدر يضيف مدراء.</p>
    @endif

    <button class="admin-btn admin-btn--primary">حفظ</button>
</form>
@endsection
