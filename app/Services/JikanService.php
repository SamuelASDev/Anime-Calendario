<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class JikanService
{
    public function search($query)
    {
        $response = Http::get('https://api.jikan.moe/v4/anime', [
            'q' => $query,
            'limit' => 10
        ]);

        return $response->json();
    }
}