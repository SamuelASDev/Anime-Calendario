<?php

namespace App\Services;

use App\Contracts\AnimeCatalogProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AnimeSearchService
{
    public function __construct(private AnimeCatalogProvider $provider) {}

    public function sourceName(): string
    {
        return $this->provider->sourceName();
    }

    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }
        try {
            return Cache::remember('anime.search.'.hash('sha256', $this->provider->cacheIdentity().'|'.mb_strtolower($query)), now()->addMinutes(10),
                fn () => $this->provider->search($query));
        } catch (Throwable $exception) {
            Log::warning('External anime search failed.', ['host' => parse_url(config('services.anime_catalog.base_url'), PHP_URL_HOST), 'exception' => $exception::class, 'code' => $exception->getCode()]);
            throw new RuntimeException('Anime search unavailable.', previous: $exception);
        }
    }
}
