<?php

namespace App\Services\Catalog;

use App\Contracts\AnimeCatalogProvider;
use App\Data\CatalogAnime;
use App\Models\Anime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Completes registered animes one per call, so a run needs no queue worker or cron,
 * never holds a PHP process for long and can stop and resume at any point.
 */
class AnimeMetadataSync
{
    public function __construct(private AnimeCatalogProvider $provider, private CatalogThrottle $throttle) {}

    public function status(): array
    {
        return [
            'total' => Anime::count(),
            'synced' => Anime::whereNotNull('metadata_synced_at')->count(),
            'pending' => $this->pending()->count(),
            'failed' => Anime::whereNotNull('metadata_sync_error')->count(),
        ];
    }

    /** @return array{state: string, retry_after: int, anime?: string} */
    public function next(): array
    {
        if (($pause = $this->throttle->remainingPause()) > 0) {
            return ['state' => 'paused', 'retry_after' => $pause];
        }
        $anime = $this->pending()->orderBy('id')->first();
        if (! $anime) {
            return ['state' => 'done', 'retry_after' => 0];
        }
        $interval = $this->throttle->interval();
        if (! $this->throttle->acquireSlot()) {
            return ['state' => 'wait', 'retry_after' => $interval];
        }

        try {
            $result = $this->provider->find($anime->mal_id);
        } catch (CatalogRateLimited $exception) {
            $this->throttle->pause($exception->retryAfter);

            return ['state' => 'paused', 'retry_after' => $exception->retryAfter];
        } catch (Throwable $exception) {
            Log::warning('Anime metadata sync failed.', ['anime_id' => $anime->id, 'exception' => $exception::class, 'code' => $exception->getCode()]);
            // A client error will not improve on retry; anything else may be temporary, so the anime stays pending.
            if ($exception instanceof RuntimeException && $exception->getCode() >= 400 && $exception->getCode() < 500) {
                $anime->update(['metadata_sync_error' => 'invalid']);

                return ['state' => 'failed', 'retry_after' => $interval, 'anime' => $anime->title];
            }

            return ['state' => 'unavailable', 'retry_after' => 30, 'anime' => $anime->title];
        }

        if (! $result) {
            $anime->update(['metadata_sync_error' => 'not_found']);

            return ['state' => 'failed', 'retry_after' => $interval, 'anime' => $anime->title];
        }
        $this->apply($anime, $result);

        return ['state' => 'synced', 'retry_after' => $interval, 'anime' => $anime->title];
    }

    public function retryFailed(): int
    {
        return Anime::whereNotNull('metadata_sync_error')->update(['metadata_sync_error' => null]);
    }

    /** Stores new metadata and only fills blanks in older fields, so curated titles and synopses are never replaced. */
    public function apply(Anime $anime, CatalogAnime $data): void
    {
        DB::transaction(function () use ($anime, $data) {
            $anime->fill([
                'media_type' => $data->mediaType, 'source_material' => $data->sourceMaterial,
                'aired_from' => $data->airedFrom, 'aired_to' => $data->airedTo, 'aired_precision' => $data->airedPrecision,
                'season' => $data->season, 'season_year' => $data->seasonYear,
                'duration' => $data->duration, 'age_rating' => $data->ageRating,
                'studios' => $data->studios === [] ? null : mb_substr(implode(', ', $data->studios), 0, 500),
                'metadata_synced_at' => now(), 'metadata_sync_error' => null,
            ]);
            $anime->episodes ??= $data->episodes;
            $anime->synopsis = filled($anime->synopsis) ? $anime->synopsis : $data->synopsis;
            $anime->anime_status = filled($anime->anime_status) ? $anime->anime_status : $data->status;
            $anime->genres = filled($anime->genres) ? $anime->genres : ($data->genres === [] ? null : implode(', ', $data->genres));
            if (blank($anime->image) && mb_strlen($data->imageUrl) <= 255) {
                $anime->image = $data->imageUrl;
            }
            $anime->save();
            $anime->titles()->delete();
            $anime->titles()->createMany($this->alternativeTitles($anime, $data));
        });
    }

    /** The catalog's main title counts as an alternative whenever the local title was changed. */
    private function alternativeTitles(Anime $anime, CatalogAnime $data): array
    {
        return collect([['type' => 'Default', 'title' => $data->title], ...$data->alternativeTitles])
            ->reject(fn ($title) => mb_strtolower($title['title']) === mb_strtolower($anime->title))
            ->unique(fn ($title) => mb_strtolower($title['title']))
            ->values()->all();
    }

    private function pending()
    {
        return Anime::whereNull('metadata_synced_at')->whereNull('metadata_sync_error');
    }
}
