<?php

namespace App\Queries;

use App\Models\Anime;
use App\Models\WatchPlan;
use Illuminate\Database\Eloquent\Builder;

class WatchPlanQuery
{
    public function forOwner(?int $userId): Builder
    {
        return WatchPlan::query()->when($userId === null, fn ($q) => $q->global(), fn ($q) => $q->ownedBy($userId));
    }

    public function loaded(?int $userId): Builder
    {
        return $this->forOwner($userId)->with('anime', 'days', 'logs', 'pauses');
    }

    public function filtered(?int $userId, array $filters = []): Builder
    {
        $query = $this->loaded($userId)->whereHas('anime', fn ($anime) => app(AnimeFilters::class)->apply($anime, $filters));
        if (! empty($filters['favorite'])) {
            $query->whereHas('anime.userMeta', fn ($meta) => $meta->where('user_id', auth()->id())->where('is_favorite', true));
        }
        match ($filters['sort'] ?? 'recent') {
            'title' => $query->orderBy(Anime::select('title')->whereColumn('animes.id', 'watch_plans.anime_id')),
            'start_date' => $query->orderBy('start_date'),
            default => $query->latest('updated_at'),
        };

        return $query->orderByDesc('id');
    }

    public function statusPages(?int $userId, int $perPage = 12, array $filters = []): array
    {
        $pages = [];
        foreach (['planned' => 'pretendo_assistir', 'active' => 'assistindo', 'paused' => 'pausado', 'completed' => 'concluido'] as $key => $status) {
            $pages[$key] = $this->filtered($userId, $filters)->where('watch_status', $status)
                ->paginate($perPage, ['*'], $key.'_page')->withQueryString()->fragment('section-'.$status);
        }

        return $pages;
    }
}
