<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'topic_id',
        'category_id',
        'title',
        'body',
        'media_path',
        'media_type',
        'status',
        'views_count',
        'share_count',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'share_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function collaborations()
    {
        return $this->morphMany(Collaboration::class, 'collaboratable');
    }

    public function acceptedCollaborators()
    {
        return $this->collaborations()->where('status', 'accepted')->with('collaborator');
    }

    public function getMediaUrlAttribute(): ?string
    {
        if (!$this->media_path) {
            return null;
        }
        if (Str::startsWith($this->media_path, ['http://', 'https://', '/'])) {
            return $this->media_path;
        }
        return asset('storage/' . $this->media_path);
    }
}
