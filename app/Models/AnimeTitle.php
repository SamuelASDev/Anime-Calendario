<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimeTitle extends Model
{
    public $timestamps = false;

    protected $fillable = ['anime_id', 'type', 'title'];

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }
}
