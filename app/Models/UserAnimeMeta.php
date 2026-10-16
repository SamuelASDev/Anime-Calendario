<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserAnimeMeta extends Model
{
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereHas('user', fn ($users) => $users->where('is_public', true)->where('show_reviews_public', true));
    }

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_favorite' => 'boolean', 'top_position' => 'integer'];
    }

    protected $table = 'user_anime_meta';

    protected $fillable = [
        'user_id',
        'anime_id',
        'rating',
        'comment',
        'is_favorite',
        'top_position',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }
}
