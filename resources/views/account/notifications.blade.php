@extends('layouts.public')

@section('title', 'الإشعارات')

@section('content')
<div class="sg-inbox-page">
    @include('partials.notification-inbox', [
        'tone' => 'public',
        'kicker' => 'حسابك',
        'heading' => 'الإشعارات',
        'lead' => 'تابع حالة طلباتك، نقاطك، وأي تنبيه يهمك في مكان واحد مرتب.',
        'empty' => 'عندما يتحرك طلبك أو تُضاف نقاط لحسابك ستجد التنبيه هنا.',
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'openRoute' => $openRoute,
        'readRoute' => $readRoute,
    ])
</div>
@endsection
