<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchPlanPause extends Model
{
    protected $fillable = ['started_on', 'resumed_on'];

    protected function casts(): array
    {
        return ['started_on' => 'date', 'resumed_on' => 'date'];
    }
}
