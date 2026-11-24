<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'name',
        'slug',
        'description',
        'avatar',
        'banner',
    ];

    protected static function booted()
    {
        static::creating(function ($topic) {
            if (empty($topic->slug) && !empty($topic->name)) {
                $base = Str::slug($topic->name);
                $candidate = $base;
                $i = 0;
                while (self::where('slug', $candidate)->exists()) {
                    $i++;
                    $candidate = $base . '-' . $i;
                }
                $topic->slug = $candidate;
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'topic_members')->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function getDisplayHandleAttribute(): string
    {
        return 'O/' . $this->name;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (Str::startsWith($this->avatar, ['http://', 'https://', '/'])) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }
        return asset('/images/icon/profil.png');
    }
}
