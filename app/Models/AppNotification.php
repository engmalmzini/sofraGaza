<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'link',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function icon(): string
    {
        $haystack = $this->title.' '.$this->body.' '.($this->link ?? '');

        return match (true) {
            str_contains($haystack, 'طلب') => 'receipt_long',
            str_contains($haystack, 'بطاقة') => 'credit_card',
            str_contains($haystack, 'عضوية') || str_contains($haystack, 'اشتراك') => 'workspace_premium',
            str_contains($haystack, 'نقاط') || str_contains($haystack, 'استبدال') => 'stars',
            str_contains($haystack, 'مطعم') || str_contains($haystack, 'انضمام') || str_contains($haystack, 'تحقق') || str_contains($haystack, 'مراجعة') => 'storefront',
            default => 'notifications',
        };
    }
}
