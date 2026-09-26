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
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($listings as $listing)
                <tr>
                    <td class="font-bold">
                        {{ $listing->restaurant->name }}
                        <div class="text-xs text-on-surface-variant">{{ $listing->restaurant->owner->name ?? '—' }}</div>
                    </td>
                    <td>{{ $listing->plan->name }}</td>
                    <td>{{ number_format($listing->amount, 0) }} <span class="ils">₪</span></td>
                    <td>@include('admin.partials.pill', ['status' => $listing->status, 'label' => $listing->statusLabel()])</td>
                    <td>{{ $listing->ends_at?->format('Y-m-d') ?: '—' }}</td>
                    <td><a class="font-bold text-primary" href="{{ route('admin.listings.show', $listing) }}">مراجعة</a></td>
                </tr>
            @empty
                <tr><td colspan="6">لا توجد اشتراكات مطابقة.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $listings->links() }}</div>
@endsection
