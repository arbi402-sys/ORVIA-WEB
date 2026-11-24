<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'attachment_path',
        'shared_event_id',
        'shared_collaboration_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function sharedEvent()
    {
        return $this->belongsTo(Event::class, 'shared_event_id');
    }

    public function sharedCollaboration()
    {
        return $this->belongsTo(Collaboration::class, 'shared_collaboration_id');
    }
}
