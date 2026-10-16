<?php

namespace App\Queries;

use App\Models\Anime;

class AnimeCatalogQuery
{
    public function paginate(array $filters, int $userId)
    {
        $query = app(AnimeFilters::class)->apply(Anime::query(), $filters)
            ->with('latestPublicReview.user')
            ->withAvg(['userMeta as public_rating' => fn ($q) => $q->publiclyVisible()], 'rating');
        if ($status = $filters['status'] ?? null) {
            $scope = fn ($plans) => $plans->ownedBy($userId);
            $status === 'unlisted' ? $query->whereDoesntHave('watchPlans', $scope)
                : $query->whereHas('watchPlans', fn ($plans) => $plans->ownedBy($userId)->where('watch_status', $status));
        }
        if (! empty($filters['favorite'])) {
            $query->whereHas('userMeta', fn ($meta) => $meta->where('user_id', $userId)->where('is_favorite', true));
        }
        match ($filters['sort'] ?? 'recent') {
            'title' => $query->orderBy('title'),
            'rating' => $query->orderByDesc('public_rating'),
            'episodes' => $query->orderByDesc('episodes'),
            default => $query->latest(),
        };

        return $query->orderByDesc('id')->paginate(24)->withQueryString();
    }
}
