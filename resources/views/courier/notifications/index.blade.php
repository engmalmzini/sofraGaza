@extends('layouts.courier')

@section('title', 'الإشعارات')
@section('back', route('courier.dashboard'))

@section('content')
<div class="courier-lead-row">
    <p class="courier-lead">تنبيهات الجاهزية وتحديثات التوصيل.</p>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('courier.notifications.read') }}">
            @csrf
            <button type="submit" class="courier-text-btn">تعليم الكل كمقروء</button>
        </form>
    @endif
</div>
<div class="courier-inbox">
    @include('partials.notification-inbox', [
        'tone' => 'partner',
        'showHero' => false,
        'empty' => 'لا إشعارات حالياً. عند جهوزية طلب جديد سيظهر هنا.',
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'openRoute' => $openRoute,
        'readRoute' => $readRoute,
    ])
</div>
@endsection
