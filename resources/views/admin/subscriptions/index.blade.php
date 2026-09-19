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
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
                <tr>
                    <td>{{ $subscription->user->name }}</td>
                    <td>{{ $subscription->membership->name }}</td>
                    <td>{{ number_format($subscription->amount, 2) }} <span class="ils">₪</span></td>
                    <td>@include('admin.partials.pill', ['status' => $subscription->status, 'label' => $subscription->statusLabel()])</td>
                    <td><a class="font-bold text-primary" href="{{ route('admin.subscriptions.show', $subscription) }}">مراجعة</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
