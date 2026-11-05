<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;

/** Paces background catalog reads; the limit is per IP, so user searches share the same quota. */
class CatalogThrottle
{
    private const PAUSE_KEY = 'anime.catalog.paused_until';

    private const SLOT_KEY = 'anime.catalog.sync_slot';

    public function interval(): int
    {
        return max(1, (int) config('services.anime_catalog.sync_interval', 2));
    }

    public function acquireSlot(): bool
    {
        return Cache::add(self::SLOT_KEY, true, $this->interval());
    }

    public function pause(int $seconds): void
    {
        Cache::put(self::PAUSE_KEY, now()->timestamp + $seconds, $seconds);
    }

    public function remainingPause(): int
    {
        return max(0, (int) Cache::get(self::PAUSE_KEY, 0) - now()->timestamp);
    }
}
