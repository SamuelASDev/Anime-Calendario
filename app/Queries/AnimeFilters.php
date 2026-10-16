<?php

namespace App\Queries;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Builder;

class AnimeFilters
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where('title', 'like', '%'.trim($value).'%'))
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
        ];
    }
}
