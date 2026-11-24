<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'created_by',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->oldest();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function otherParticipant(User $me): ?User
    {
        return $this->participants->firstWhere('id', '!=', $me->id);
    }

    public function unreadCountFor(User $me): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $me->id)
            ->whereNull('read_at')
            ->count();
    }

    public static function findOrCreateDirect(User $userA, User $userB): self
    {
        $existing = self::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('users.id', $userA->id))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $userB->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $conv = self::create([
            'type' => 'direct',
            'created_by' => $userA->id,
            'last_message_at' => now(),
        ]);

        $conv->participants()->attach([
            $userA->id => ['last_read_at' => now()],
            $userB->id => ['last_read_at' => null],
        ]);

        return $conv;
    }
}
