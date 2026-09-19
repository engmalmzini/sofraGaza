<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AppNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

trait ManagesAppNotifications
{
    protected function notificationsInbox(string $view): View
    {
        $user = auth()->user();

        return view($view, [
            'notifications' => $user->notifications()->paginate(16),
            'unreadCount' => $user->unreadNotificationsCount(),
            'openRoute' => $this->notificationOpenRoute(),
            'readRoute' => $this->notificationReadRoute(),
        ]);
    }

    protected function markAllAppNotifications(): RedirectResponse
    {
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'تم تعليم كل الإشعارات كمقروءة.');
    }

    protected function openAppNotification(AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markRead();

        return redirect()->to($notification->link ?: $this->notificationsInboxPath());
    }

    abstract protected function notificationsInboxPath(): string;

    abstract protected function notificationOpenRoute(): string;

    abstract protected function notificationReadRoute(): string;
}
