<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnimeMeta extends Model
{
    protected $table = 'user_anime_meta';

    protected $fillable = [
        'user_id',
        'anime_id',
        'rating',
        'comment',
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