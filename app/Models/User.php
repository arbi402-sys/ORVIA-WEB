<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_INSTITUTION = 'institution';
    public const ROLE_ADMIN = 'admin';

    public const LEGACY_USER_ROLES = ['mahasiswa', 'dosen', 'client'];

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'email',
        'bio',
        'headline',
        'institution',
        'location',
        'city',
        'website',
        'category',
        'newsletter',
        'role',
        'verification_status',
        'is_verified',
        'institution_access_code',
        'access_code_used_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'theme_preference',
        'language_preference',
        'two_factor_enabled',
        'social_instagram',
        'social_whatsapp',
        'social_x',
        'proof_image',
        'avatar',
        'cover_image',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'newsletter' => 'boolean',
            'is_verified' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_activity' => 'integer',
            'access_code_used_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /* ================= Role & Verification Helpers ================= */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isInstitution(): bool
    {
        return in_array($this->role, [self::ROLE_INSTITUTION, 'community'], true);
    }

    public function isVerifiedInstitution(): bool
    {
        return $this->isInstitution() && (bool) $this->is_verified && $this->verification_status === 'approved';
    }

    public function isPendingInstitution(): bool
    {
        return $this->isInstitution() && !$this->is_verified;
    }

    public function isRegularUser(): bool
    {
        return $this->role === self::ROLE_USER || in_array($this->role, self::LEGACY_USER_ROLES, true);
    }

    public function canCreateEvents(): bool
    {
        return $this->isAdmin() || $this->isVerifiedInstitution();
    }

    public function canCreateTopics(): bool
    {
        return $this->isAdmin() || $this->isVerifiedInstitution();
    }

    public function canCollaborate(): bool
    {
        return $this->isAdmin() || $this->isVerifiedInstitution();
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'Administrator';
        }
        if ($this->isInstitution()) {
            return $this->is_verified ? 'Akun Lembaga • Terverifikasi' : 'Akun Lembaga • Menunggu Aktivasi';
        }
        return 'Pengguna';
    }

    /* ================= Media Accessors ================= */

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

    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        if (Str::startsWith($this->cover_image, ['http://', 'https://', '/'])) {
            return $this->cover_image;
        }
        return asset('storage/' . $this->cover_image);
    }

    /* ================= Legacy & Existing Relationships ================= */

    public function artworks()
    {
        return $this->hasMany(Artwork::class);
    }

    public function likedArtworks()
    {
        return $this->belongsToMany(Artwork::class, 'artwork_likes')->withTimestamps();
    }

    public function savedArtworks()
    {
        return $this->belongsToMany(Artwork::class, 'artwork_saves')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(self::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /* ================= New ORVIA Relationships ================= */

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    public function likedArticles()
    {
        return $this->belongsToMany(Article::class, 'article_likes')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function createdTopics()
    {
        return $this->hasMany(Topic::class, 'creator_id');
    }

    public function joinedTopics()
    {
        return $this->belongsToMany(Topic::class, 'topic_members')->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function incomingCollaborations()
    {
        return $this->hasMany(Collaboration::class, 'collaborator_id');
    }

    public function outgoingCollaborations()
    {
        return $this->hasMany(Collaboration::class, 'inviter_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    public function unreadMessagesCount(): int
    {
        return Message::whereHas('conversation.participants', function ($q) {
            $q->where('users.id', $this->id);
        })
            ->where('sender_id', '!=', $this->id)
            ->where(function ($q) {
                $q->whereNull('read_at');
            })
            ->count();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->userNotifications()->whereNull('read_at')->count();
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->username) && !empty($user->name)) {
                $base = Str::slug($user->name);
                $candidate = $base ?: Str::slug($user->email ?? 'user');
                $i = 0;
                while (self::where('username', $candidate)->exists()) {
                    $i++;
                    $candidate = $base . '-' . $i;
                }
                $user->username = $candidate;
            }
        });
    }
}
