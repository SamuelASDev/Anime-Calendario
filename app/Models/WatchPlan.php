<?php

namespace App\Models;

use App\Services\Planning\PlanProjection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WatchPlan extends Model
{
    protected $fillable = [
        'user_id',
        'anime_id',
        'episodes_watched',
        'episodes_per_day',
        'start_date',
        'watch_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function days()
    {
        return $this->hasMany(WatchPlanDay::class);
    }

    public function logs()
    {
        return $this->hasMany(WatchLog::class);
    }

    protected function casts(): array
    {
        return ['episodes_watched' => 'integer', 'episodes_per_day' => 'integer', 'completed_at' => 'datetime'];
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function pauses()
    {
        return $this->hasMany(WatchPlanPause::class);
    }

    public function getCurrentEpisodeCalculatedAttribute(): int
    {
        return app(PlanProjection::class)->currentEpisode($this);
    }
}
