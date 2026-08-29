<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'organization_id',
        'user_id',
        'type',
        'title',
        'body',
        'link',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function notify(User $user, string $title, array $opts = []): self
    {
        return self::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'type' => $opts['type'] ?? 'info',
            'title' => $title,
            'body' => $opts['body'] ?? null,
            'link' => $opts['link'] ?? null,
            'data' => $opts['data'] ?? null,
        ]);
    }
}
