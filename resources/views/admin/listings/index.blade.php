@extends('layouts.admin')

@section('kicker', 'التشغيل')
@section('title', 'اشتراكات المطاعم')

@section('content')
<div class="admin-toolbar">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('admin.listings.index') }}">الكل</a>
        @foreach(\App\Models\RestaurantSubscription::STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('admin.listings.index', ['status' => $key]) }}">{{ $label }}</a>
        @endforeach
    </div>
</div>
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>المطعم</th>
                <th>الباقة</th>
                <th>المبلغ</th>
                <th>الحالة</th>
                <th>ينتهي</th>
                <th class="text-center">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listings as $listing)
                <tr>
                    <td class="font-bold text-slate-900">
                        <a href="{{ route('admin.listings.show', $listing) }}" class="text-slate-900 hover:text-primary">{{ $listing->restaurant->name }}</a>
                        <div class="text-xs text-on-surface-variant font-normal">{{ $listing->restaurant->owner->name ?? '—' }}</div>
                    </td>
                    <td><span class="font-bold text-slate-800">{{ $listing->plan->name }}</span></td>
                    <td><span class="font-mono font-bold text-slate-900">{{ number_format($listing->amount, 0) }}</span> <span class="ils">₪</span></td>
                    <td>@include('admin.partials.pill', ['status' => $listing->status, 'label' => $listing->statusLabel()])</td>
                    <td class="font-mono text-xs text-slate-600">{{ $listing->ends_at?->format('Y-m-d') ?: '—' }}</td>
                    <td class="whitespace-nowrap">
                        <div class="admin-table-actions">
                            <a class="admin-action-btn admin-action-btn--primary" href="{{ route('admin.listings.show', $listing) }}">
                                <span class="material-symbols-outlined">rate_review</span>
                                <span>مراجعة</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-6 text-slate-400">لا توجد اشتراكات مطابقة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $listings->links() }}</div>
@endsection
