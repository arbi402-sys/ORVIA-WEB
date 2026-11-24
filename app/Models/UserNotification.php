<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'message',
        'thumbnail',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function notifyUser(
        int $userId,
        ?int $actorId,
        string $type,
        string $message,
        ?string $actionUrl = null,
        ?string $thumbnail = null
    ): ?self {
        if ($actorId && $userId === $actorId) {
            return null;
        }
        return self::create([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'type' => $type,
            'message' => $message,
            'action_url' => $actionUrl,
            'thumbnail' => $thumbnail,
        ]);
    }
}
