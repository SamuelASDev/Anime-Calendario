<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchPlanDay extends Model
{
    protected $fillable = [
        'watch_plan_id',
        'day_of_week',
        'episodes_planned',
        'is_variable',
    ];

    public function plan()
    {
        return $this->belongsTo(WatchPlan::class, 'watch_plan_id');
    }
}