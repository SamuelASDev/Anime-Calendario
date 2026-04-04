<?php

namespace App\Models;

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
}