@php
    $tone = $tone ?? 'public';
    $showHero = $showHero ?? true;
@endphp
<div class="sg-inbox sg-inbox--{{ $tone }}">
    @if($showHero)
        <div class="sg-inbox__hero">
            <div>
                <p class="sg-inbox__kicker">{{ $kicker ?? 'صندوق التنبيهات' }}</p>
                <h2 class="sg-inbox__title">{{ $heading ?? 'الإشعارات' }}</h2>
                <p class="sg-inbox__lead">{{ $lead }}</p>
            </div>
            <div class="sg-inbox__hero-side">
                <span class="sg-inbox__count">{{ $unreadCount }} غير مقروء</span>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route($readRoute) }}">
                        @csrf
                        <button type="submit" class="sg-inbox__read">تعليم الكل كمقروء</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="sg-inbox__list">
        @forelse($notifications as $note)
            <a href="{{ route($openRoute, $note) }}" class="sg-inbox__item {{ $note->isUnread() ? 'is-unread' : '' }}">
                <span class="sg-inbox__icon" aria-hidden="true">
                    <span class="material-symbols-outlined">{{ $note->icon() }}</span>
                </span>
                <span class="sg-inbox__copy">
                    <strong>{{ $note->title }}</strong>
                    <em>{{ $note->body }}</em>
                </span>
                <span class="sg-inbox__meta">
                    <time datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->diffForHumans() }}</time>
                    @if($note->isUnread())
                        <i>جديد</i>
                    @endif
                    <span class="material-symbols-outlined">chevron_left</span>
                </span>
            </a>
        @empty
            <div class="sg-inbox__empty">
                <span class="material-symbols-outlined">notifications_off</span>
                <strong>لا إشعارات حالياً</strong>
                <p>{{ $empty ?? 'ستظهر هنا تنبيهات طلباتك ونقاطك أولاً بأول.' }}</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="sg-inbox__pager">{{ $notifications->links() }}</div>
    @endif
</div>
