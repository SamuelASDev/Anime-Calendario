<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (! $user->username) {
                $base = substr(Str::slug($user->name, ''), 0, 20) ?: 'user';
                $user->username = $base.'_'.strtolower(Str::random(8));
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function watchPlans()
    {
        return $this->hasMany(WatchPlan::class);
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'bio',
        'is_public',
        'show_watching_public',
        'show_completed_public',
        'show_favorites_public',
        'show_top10_public',
        'show_reviews_public',
        'profile_photo',
        'profile_banner',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'show_watching_public' => 'boolean',
            'show_completed_public' => 'boolean',
            'show_favorites_public' => 'boolean',
            'show_top10_public' => 'boolean',
            'show_reviews_public' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function animeMeta()
    {
        return $this->hasMany(UserAnimeMeta::class);
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo
            ? asset('storage/'.$this->profile_photo)
            : null;
    }

    public function getProfileBannerUrlAttribute()
    {
        return $this->profile_banner
            ? asset('storage/'.$this->profile_banner)
            : null;
    }
}
