<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'topic_id',
        'title',
        'subtitle',
        'slug',
        'body',
        'image_path',
        'status',
        'views_count',
        'share_count',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'share_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($article) {
            if (empty($article->slug) && !empty($article->title)) {
                $article->slug = Str::slug($article->title) . '-' . Str::lower(Str::random(5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'article_likes')->withTimestamps();
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

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        if (Str::startsWith($this->image_path, ['http://', 'https://', '/'])) {
            return $this->image_path;
        }
        return asset('storage/' . $this->image_path);
    }
}
