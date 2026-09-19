@extends('layouts.partner')

@section('title', 'الإشعارات')

@section('actions')
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('partner.notifications.read') }}">
            @csrf
            <button type="submit" class="admin-btn admin-btn--ghost">تعليم الكل كمقروء</button>
        </form>
    @endif
@endsection

@section('content')
<div class="admin-metrics">
    <article class="admin-metric admin-metric--accent">
        <span>غير مقروء</span>
        <strong>{{ $unreadCount }}</strong>
    </article>
    <article class="admin-metric">
        <span>كل الإشعارات</span>
        <strong>{{ $notifications->total() }}</strong>
    </article>
</div>

<section class="admin-card mt-4">
    <p class="sg-inbox__panel-lead">حالة التحقق، الطلبات الجديدة، وأي رسالة من إدارة المنصة.</p>
    @include('partials.notification-inbox', [
        'tone' => 'partner',
        'showHero' => false,
        'empty' => 'لا إشعارات لمطعمك الآن. سنخبرك هنا عند مراجعة طلبك أو وصول طلب جديد.',
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'openRoute' => $openRoute,
        'readRoute' => $readRoute,
    ])
</section>
@endsection
