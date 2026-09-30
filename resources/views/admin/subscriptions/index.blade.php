@extends('layouts.admin')

@section('kicker', 'الزبائن')
@section('title', 'اشتراكات الزبائن')

@section('content')
<div class="admin-toolbar">
    <div class="admin-chips">
        <a class="admin-chip {{ request('status') ? '' : 'is-active' }}" href="{{ route('admin.subscriptions.index') }}">الكل</a>
        @foreach(\App\Models\MembershipSubscription::STATUSES as $key => $label)
            <a class="admin-chip {{ request('status') === $key ? 'is-active' : '' }}" href="{{ route('admin.subscriptions.index', ['status' => $key]) }}">{{ $label }}</a>
        @endforeach
    </div>
</div>
<div class="admin-table-wrap">
    <table>
        <thead>
            <tr>
                <th>الزبون</th>
                <th>العضوية</th>
                <th>المبلغ</th>
                <th>الحالة</th>
                <th>البطاقة</th>
                <th class="text-center">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
                <tr>
                    <td class="font-bold text-slate-900">{{ $subscription->user->name }}</td>
                    <td><span class="font-bold text-slate-800">{{ $subscription->membership->name }}</span></td>
                    <td class="font-mono font-bold text-slate-900">{{ number_format($subscription->amount, 2) }} <span class="ils">₪</span></td>
                    <td>@include('admin.partials.pill', ['status' => $subscription->status, 'label' => $subscription->statusLabel()])</td>
                    <td><span class="text-xs font-semibold text-slate-600">{{ $subscription->cardStatusLabel() }}</span></td>
                    <td class="whitespace-nowrap">
                        <div class="admin-table-actions">
                            <a class="admin-action-btn admin-action-btn--primary" href="{{ route('admin.subscriptions.show', $subscription) }}">
                                <span class="material-symbols-outlined">rate_review</span>
                                <span>مراجعة</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
