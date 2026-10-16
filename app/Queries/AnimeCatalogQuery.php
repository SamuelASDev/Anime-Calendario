<?php

namespace App\Queries;

use App\Models\Anime;

class AnimeCatalogQuery
{
    public function paginate(?string $search)
    {
        return Anime::with('latestPublicReview.user')
            ->withAvg(['userMeta as public_rating' => fn ($q) => $q->publiclyVisible()], 'rating')
            ->when($search, fn ($q) => $q->where('title', 'like', '%'.$search.'%'))
            ->latest()->paginate(24)->withQueryString();
    }
}
