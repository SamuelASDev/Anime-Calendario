<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    public function latestPublicReview()
    {
        return $this->hasOne(UserAnimeMeta::class)->ofMany(['updated_at' => 'max', 'id' => 'max'], fn ($query) => $query
            ->publiclyVisible()->where(fn ($review) => $review->whereNotNull('rating')->orWhereNotNull('comment')));
    }

    public function globalPlan()
    {
        return $this->hasOne(WatchPlan::class)->global();
    }

    public function userMeta()
    {
        return $this->hasMany(UserAnimeMeta::class);
    }

    protected $fillable = [
        'mal_id',
        'title',
        'episodes',
        'synopsis',
        'synopsis_pt',
        'anime_status',
        'image',
        'genres',
    ];

    public function watchPlans()
    {
        return $this->hasMany(WatchPlan::class);
    }
}
