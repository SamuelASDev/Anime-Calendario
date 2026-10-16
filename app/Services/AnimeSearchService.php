<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AnimeSearchService
{
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }
        $baseUrl = rtrim(config('services.anime_catalog.base_url'), '/');
        try {
            return Cache::remember('anime.search.'.hash('sha256', $baseUrl.'|'.mb_strtolower($query)), now()->addMinutes(10), function () use ($query, $baseUrl) {
                $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
                    ->get($baseUrl.'/anime', ['q' => $query, 'limit' => 10]);
                if (! $response->successful() || ! is_array($response->json('data'))) {
                    throw new RuntimeException('Invalid catalog response.', $response->status());
                }

                return array_values(array_filter($response->json('data'), fn ($item) => is_array($item)
                    && isset($item['mal_id'], $item['title'], $item['images']['jpg']['image_url'])));
            });
        } catch (Throwable $exception) {
            Log::warning('External anime search failed.', ['host' => parse_url($baseUrl, PHP_URL_HOST), 'exception' => $exception::class, 'code' => $exception->getCode()]);
            throw new RuntimeException('Anime search unavailable.', previous: $exception);
        }
    }
}
