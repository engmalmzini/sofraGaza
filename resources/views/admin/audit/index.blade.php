@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', 'سجل التعديلات')

@section('content')
<p class="text-sm text-slate-500 mb-4 leading-relaxed">
    كل إجراء من لوحة الإدارة ينحفظ بالاسم والتاريخ والوقت — عشان نعرف مين عدّل لو صار نزاع أو خطأ.
</p>

<form method="GET" class="admin-card mb-4 grid gap-3 sm:grid-cols-4 items-end">
    <div>
        <label class="block text-xs font-bold mb-1">بحث</label>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="اسم المدير أو الإجراء">
    </div>
    <div>
        <label class="block text-xs font-bold mb-1">المدير</label>
        <select name="admin">
            <option value="">الكل</option>
            @foreach($admins as $admin)
                <option value="{{ $admin->id }}" @selected((string) request('admin') === (string) $admin->id)>{{ $admin->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold mb-1">من تاريخ</label>
        <input type="date" name="from" value="{{ request('from') }}">
    </div>
    <div>
        <label class="block text-xs font-bold mb-1">إلى تاريخ</label>
        <input type="date" name="to" value="{{ request('to') }}">
    </div>
    <div class="sm:col-span-4">
        <button class="admin-btn admin-btn--secondary">تصفية</button>
        <a href="{{ route('admin.audit.index') }}" class="admin-btn admin-btn--ghost">مسح</a>
    </div>
</form>

<div class="space-y-2">
    @forelse($logs as $log)
        <article class="admin-card !p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-extrabold text-slate-900">{{ $log->actorDisplayName() }}</p>
                    <p class="text-sm text-slate-700 mt-0.5">{{ $log->description }}</p>
                </div>
                <time class="text-xs font-bold text-slate-500 shrink-0" dir="ltr">{{ $log->created_at?->format('Y/m/d H:i') }}</time>
            </div>
            @if(!empty($log->properties['reason']))
                <p class="mt-2 text-xs text-slate-500">السبب: {{ $log->properties['reason'] }}</p>
            @endif
        </article>
    @empty
        <div class="admin-card text-sm text-slate-500">ما في تعديلات مسجّلة بهالفلتر.</div>
    @endforelse
</div>

<div class="mt-4">{{ $logs->links() }}</div>
@endsection
