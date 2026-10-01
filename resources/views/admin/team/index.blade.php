@extends('layouts.admin')

@section('kicker', 'النظام')
@section('title', 'فريق الإدارة')

@section('actions')
    <a href="{{ route('admin.team.create') }}" class="admin-btn admin-btn--primary">
        <span class="material-symbols-outlined">person_add</span>
        <span>إضافة مدير</span>
    </a>
@endsection

@section('content')
<p class="text-sm text-slate-500 mb-4 leading-relaxed">
    لوحة الأم تقدر تضيف مدراء جدد وتحدد شو بيتابعوا: مطاعم فقط، طلبات، مالية… كل إجراء بينحفظ بالاسم والتاريخ في سجل التعديلات.
</p>

<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>المدير</th>
                <th>الصلاحية</th>
                <th>الحالة</th>
                <th class="text-center">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($admins as $adminUser)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">{{ mb_substr($adminUser->name, 0, 1) }}</span>
                            <div>
                                <div class="font-bold text-slate-900">{{ $adminUser->name }}</div>
                                <div class="font-mono text-xs text-slate-500" dir="ltr">{{ $adminUser->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm">{{ $adminUser->adminRoleLabel() }}</td>
                    <td>
                        @if($adminUser->isActiveAdmin())
                            <span class="admin-pill admin-pill--ok">نشط</span>
                        @else
                            <span class="admin-pill admin-pill--wait">موقوف</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">
                        <div class="admin-table-actions">
                            <a class="admin-action-btn admin-action-btn--primary" href="{{ route('admin.team.edit', $adminUser) }}">تعديل</a>
                            @if($adminUser->id !== auth()->id() && $adminUser->isActiveAdmin())
                                <form method="POST" action="{{ route('admin.team.destroy', $adminUser) }}" onsubmit="return confirm('إيقاف صلاحيات {{ $adminUser->name }}؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-action-btn">إيقاف</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
