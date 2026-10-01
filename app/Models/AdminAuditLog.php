<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_name',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'url',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actorDisplayName(): string
    {
        return $this->actor?->name ?: $this->actor_name;
    }

    public function whenLabel(): string
    {
        return $this->created_at?->timezone(config('app.timezone'))->translatedFormat('Y/m/d — h:i A')
            ?: '';
    }
}
