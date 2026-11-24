<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'event_type',
        'description',
        'poster_path',
        'banner_path',
        'location_name',
        'city',
        'event_date',
        'registration_deadline',
        'registration_status',
        'external_registration_url',
        'schedules',
        'gallery',
        'share_count',
    ];

    protected $casts = [
        'event_date' => 'date',
        'registration_deadline' => 'date',
        'schedules' => 'array',
        'gallery' => 'array',
        'share_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            if (empty($event->slug) && !empty($event->title)) {
                $base = Str::slug($event->title);
                $candidate = $base;
                $i = 0;
                while (self::where('slug', $candidate)->exists()) {
                    $i++;
                    $candidate = $base . '-' . $i;
                }
                $event->slug = $candidate;
            }
        });
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
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

    public function getPosterUrlAttribute(): string
    {
        if ($this->poster_path) {
            if (Str::startsWith($this->poster_path, ['http://', 'https://', '/'])) {
                return $this->poster_path;
            }
            return asset('storage/' . $this->poster_path);
        }
        return asset('/images/BANNER.jpg');
    }

    public function getBannerUrlAttribute(): string
    {
        if ($this->banner_path) {
            if (Str::startsWith($this->banner_path, ['http://', 'https://', '/'])) {
                return $this->banner_path;
            }
            return asset('storage/' . $this->banner_path);
        }
        return $this->poster_url;
    }

    public function isOpenForRegistration(): bool
    {
        if ($this->registration_status !== 'open') {
            return false;
        }
        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }
        return true;
    }
}
