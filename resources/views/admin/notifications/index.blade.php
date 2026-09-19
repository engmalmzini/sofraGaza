@extends('layouts.admin')

@section('title', 'الإشعارات')

@section('actions')
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.notifications.read') }}">
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
    <p class="sg-inbox__panel-lead">طلبات الانضمام، الطلبات المعلّقة، والاشتراكات التي تحتاج إجراءك.</p>
    @include('partials.notification-inbox', [
        'tone' => 'admin',
        'showHero' => false,
        'empty' => 'لا توجد تنبيهات إدارية حالياً. أي طلب انضمام أو مهمة جديدة ستظهر هنا.',
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'openRoute' => $openRoute,
        'readRoute' => $readRoute,
    ])
</section>
@endsection
