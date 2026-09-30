@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'الزبائن والنقاط')

@section('content')
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الزبون</th>
                <th>رقم الهاتف</th>
                <th>رصيد النقاط</th>
                <th class="text-center">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="font-bold text-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ mb_substr($user->name, 0, 1) }}
                            </span>
                            <span>{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="font-mono text-slate-600" dir="ltr">{{ $user->phone }}</td>
                    <td>
                        <span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/70 text-xs">
                            <span class="material-symbols-outlined text-[15px] text-amber-600">stars</span>
                            <span>{{ number_format($user->points_balance) }} نقطة</span>
                        </span>
                    </td>
                    <td class="whitespace-nowrap">
                        <div class="admin-table-actions">
                            <a class="admin-action-btn admin-action-btn--primary" href="{{ route('admin.users.show', $user) }}">
                                <span class="material-symbols-outlined">visibility</span>
                                <span>عرض الحساب</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
