@extends('layouts.admin')

@section('kicker', 'المالية')
@section('title', 'إعلانات المطاعم')

@section('content')
<div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
    <a href="{{ route('admin.boosts.index') }}" class="admin-btn text-xs {{ ! $status ? 'admin-btn--primary' : 'admin-btn--ghost' }}">الكل ({{ $counts['all'] }})</a>
    <a href="{{ route('admin.boosts.index', ['status' => 'pending']) }}" class="admin-btn text-xs {{ $status === 'pending' ? 'admin-btn--primary' : 'admin-btn--ghost' }}">قيد المراجعة ({{ $counts['pending'] }})</a>
    <a href="{{ route('admin.boosts.index', ['status' => 'approved']) }}" class="admin-btn text-xs {{ $status === 'approved' ? 'admin-btn--primary' : 'admin-btn--ghost' }}">مفعّلة ({{ $counts['approved'] }})</a>
    <a href="{{ route('admin.boosts.index', ['status' => 'rejected']) }}" class="admin-btn text-xs {{ $status === 'rejected' ? 'admin-btn--primary' : 'admin-btn--ghost' }}">مرفوضة ({{ $counts['rejected'] }})</a>
</div>

<div class="admin-table-wrap mt-4">
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>المطعم</th>
                <th>الأيام</th>
                <th>المبلغ</th>
                <th>المدة</th>
                <th>الحالة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($boosts as $boost)
                <tr>
                    <td class="font-mono">#{{ $boost->id }}</td>
                    <td>
                        <a href="{{ route('admin.boosts.show', $boost) }}" class="font-bold hover:text-primary">{{ $boost->restaurant->name }}</a>
                        <div class="text-xs text-slate-500">{{ $boost->restaurant->owner->name ?? '' }}</div>
                    </td>
                    <td>{{ $boost->durationDays() }}</td>
                    <td class="font-mono font-bold">{{ number_format($boost->amount, 2) }} ₪</td>
                    <td class="text-xs">{{ $boost->starts_on->format('Y/m/d') }} — {{ $boost->ends_on->format('Y/m/d') }}</td>
                    <td>
                        @if($boost->isPending())
                            <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
                        @elseif($boost->isApproved())
                            <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px]">{{ $boost->isLive() ? 'ظاهر الآن' : $boost->statusLabel() }}</span>
                        @else
                            <span class="inline-flex px-2.5 py-1 rounded-full bg-rose-100 text-rose-900 font-bold text-[11px]">{{ $boost->statusLabel() }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.boosts.show', $boost) }}" class="admin-action-btn admin-action-btn--outline admin-action-btn--sm">مراجعة</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">لا توجد طلبات إعلان</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $boosts->links() }}</div>
@endsection
