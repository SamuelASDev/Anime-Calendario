<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchLog extends Model
{
    protected $fillable = [
        'watch_plan_id',
        'watched_date',
        'episodes_watched_today',
        'notes',
    ];

    public function plan()
    {
        return $this->belongsTo(WatchPlan::class, 'watch_plan_id');
    }
}