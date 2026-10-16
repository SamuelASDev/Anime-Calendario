<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class JikanService
{
    public function search(string $query): array
    {
        $query = trim($query);
        try {
            return Cache::remember('jikan.search.'.hash('sha256', mb_strtolower($query)), now()->addMinutes(10), function () use ($query) {
                $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
                    ->get('https://api.jikan.moe/v4/anime', ['q' => $query, 'limit' => 10]);
                if (! $response->successful() || ! is_array($response->json('data'))) {
                    throw new RuntimeException('Invalid Jikan response.');
                }

                return array_values(array_filter($response->json('data'), fn ($item) => is_array($item)
                    && isset($item['mal_id'], $item['title'], $item['images']['jpg']['image_url'])));
            });
        } catch (Throwable $exception) {
            throw new RuntimeException('Anime search unavailable.', previous: $exception);
        }
    }
}
