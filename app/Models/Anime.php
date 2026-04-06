<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    public function watchPlan()
    {
        return $this->hasOne(WatchPlan::class);
    }

    public function userMeta()
    {
        return $this->hasMany(\App\Models\UserAnimeMeta::class);
    }

    protected $fillable = [
        'mal_id',
        'title',
        'episodes',
        'synopsis',
        'synopsis_pt',
        'anime_status',
        'image'
    ];
}
