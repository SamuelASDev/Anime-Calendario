<?php

namespace App\Queries;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Builder;

class AnimeFilters
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function ($q, $value) {
                $term = '%'.trim($value).'%';
                $q->where(fn ($match) => $match->where('title', 'like', $term)
                    ->orWhereHas('titles', fn ($titles) => $titles->where('title', 'like', $term)));
            })
            // Studios are stored comma-separated: match whole names so "Bones" never returns "Bones Film".
            ->when($filters['studio'] ?? null, fn ($q, $value) => $q->where(fn ($match) => $match->where('studios', $value)
                ->orWhere('studios', 'like', $value.', %')->orWhere('studios', 'like', '%, '.$value)->orWhere('studios', 'like', '%, '.$value.', %')))
            ->when($filters['source'] ?? null, fn ($q, $value) => $q->where('source_material', $value))
            ->when($filters['season'] ?? null, function ($q, $value) {
                [$season, $year] = explode('-', $value, 2);
                $q->where('season', $season)->where('season_year', (int) $year);
            })
            ->when($filters['aired_from'] ?? null, fn ($q, $value) => $q->whereDate('aired_from', '>=', $value))
            ->when($filters['aired_until'] ?? null, fn ($q, $value) => $q->whereDate('aired_from', '<=', $value))
            ->when($filters['genre'] ?? null, fn ($q, $value) => $q->where('genres', 'like', '%'.$value.'%'))
            ->when($filters['airing'] ?? null, fn ($q, $value) => $q->where('anime_status', $value))
            ->when($filters['length'] ?? null, fn ($q, $value) => match ($value) {
                'short' => $q->whereBetween('episodes', [1, 13]),
                'medium' => $q->whereBetween('episodes', [14, 26]),
                'long' => $q->where('episodes', '>', 26),
                'unknown' => $q->whereNull('episodes'),
                default => $q,
            });
    }

    public function options(): array
    {
        return [
            'genres' => Anime::whereNotNull('genres')->pluck('genres')->flatMap(fn ($value) => explode(',', $value))
                ->map(fn ($value) => trim($value))->filter()->unique()->sort()->values(),
            'airingStatuses' => Anime::whereNotNull('anime_status')->where('anime_status', '!=', '')->distinct()->orderBy('anime_status')->pluck('anime_status'),
            'studios' => Anime::whereNotNull('studios')->distinct()->pluck('studios')->flatMap(fn ($value) => explode(',', $value))
                ->map(fn ($value) => trim($value))->filter()->unique()->sort()->values(),
            'sourceMaterials' => Anime::whereNotNull('source_material')->distinct()->pluck('source_material')
                ->mapWithKeys(fn ($value) => [$value => Anime::sourceLabelFor($value)])->sort(),
            'seasons' => Anime::whereNotNull('season')->whereNotNull('season_year')->select('season', 'season_year')->distinct()->get()
                ->filter(fn ($anime) => $anime->seasonLabel() !== null)
                ->sortByDesc(fn ($anime) => $anime->season_year * 10 + array_search($anime->season, Anime::SEASONS, true))
                ->mapWithKeys(fn ($anime) => [$anime->season.'-'.$anime->season_year => $anime->seasonLabel()]),
        ];
    }
}
