<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesAppNotifications;
use App\Models\AppNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController
{
    use ManagesAppNotifications;

    public function index(): View
    {
        return $this->notificationsInbox('admin.notifications.index');
    }

    public function markRead(): RedirectResponse
    {
        return $this->markAllAppNotifications();
    }

    public function open(AppNotification $notification): RedirectResponse
    {
        return $this->openAppNotification($notification);
    }

    protected function notificationsInboxPath(): string
    {
        return route('admin.notifications.index');
    }

    protected function notificationOpenRoute(): string
    {
        return 'admin.notifications.open';
    }

    protected function notificationReadRoute(): string
    {
        return 'admin.notifications.read';
    }
}
