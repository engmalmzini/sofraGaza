<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Concerns\ManagesAppNotifications;
use App\Models\AppNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController
{
    use ManagesAppNotifications;

    public function index(): View
    {
        return $this->notificationsInbox('partner.notifications.index');
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
        return route('partner.notifications.index');
    }

    protected function notificationOpenRoute(): string
    {
        return 'partner.notifications.open';
    }

    protected function notificationReadRoute(): string
    {
        return 'partner.notifications.read';
    }
}
