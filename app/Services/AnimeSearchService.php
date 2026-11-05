<?php

namespace App\Services;

use App\Contracts\AnimeCatalogProvider;
use App\Data\CatalogAnime;
use App\Services\Catalog\CatalogRateLimited;
use App\Services\Catalog\CatalogThrottle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AnimeSearchService
{
    public function __construct(private AnimeCatalogProvider $provider, private CatalogThrottle $throttle) {}

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
            return Cache::remember('anime.search.'.hash('sha256', $this->provider->cacheIdentity().'|'.mb_strtolower($query)), now()->addMinutes(10), function () use ($query) {
                $results = $this->provider->search($query);
                // Lets an import reuse what the server already fetched, without trusting form fields or calling the API again.
                foreach ($results as $anime) {
                    Cache::put($this->itemKey($anime->malId), $anime, now()->addHour());
                }

                return $results;
            });
        } catch (Throwable $exception) {
            if ($exception instanceof CatalogRateLimited) {
                $this->throttle->pause($exception->retryAfter);
            }
            Log::warning('External anime search failed.', ['host' => parse_url(config('services.anime_catalog.base_url'), PHP_URL_HOST), 'exception' => $exception::class, 'code' => $exception->getCode()]);
            throw new RuntimeException('Anime search unavailable.', previous: $exception);
        }
    }

    public function recentResult(int $malId): ?CatalogAnime
    {
        $anime = Cache::get($this->itemKey($malId));

        return $anime instanceof CatalogAnime ? $anime : null;
    }

    private function itemKey(int $malId): string
    {
        return 'anime.catalog.item.'.hash('sha256', $this->provider->cacheIdentity().'|'.$malId);
    }
}
