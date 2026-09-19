<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    public function notify(User $user, string $title, string $body, ?string $link = null): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'link' => $link,
        ]);
    }

    public function notifyAdmins(string $title, string $body, ?string $link = null): void
    {
        User::query()->where('role', 'admin')->get()->each(function (User $admin) use ($title, $body, $link) {
            $this->notify($admin, $title, $body, $link);
        });
    }
}
