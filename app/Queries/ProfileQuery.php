<?php

namespace App\Queries;

use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;

class ProfileQuery
{
    public function sections(User $user, bool $owner = false): array
    {
        $plans = fn (string $status) => WatchPlan::ownedBy($user->id)->where('watch_status', $status)
            ->with('user')->with(['anime.userMeta' => fn ($q) => $q->where('user_id', auth()->id())])->latest()->paginate(12, ['*'], $status.'_page')->withQueryString();
        $meta = fn () => UserAnimeMeta::where('user_id', $user->id)->with('anime');
        $favorites = $user->show_favorites_public || $owner ? $meta()->where('is_favorite', true)->paginate(12, ['*'], 'favorites_page')->withQueryString() : collect();
        $top10 = $user->show_top10_public || $owner ? $meta()->whereNotNull('top_position')->orderBy('top_position')->limit(10)->get() : collect();
        if (! $owner && ! $user->show_reviews_public) {
            foreach ([$favorites, $top10] as $items) {
                foreach ($items as $item) {
                    $item->rating = null;
                    $item->comment = null;
                }
            }
        }

        return [
            'user' => $user,
            'planned' => $owner ? $plans('pretendo_assistir') : collect(),
            'watching' => $user->show_watching_public || $owner ? $plans('assistindo') : collect(),
            'completed' => $user->show_completed_public || $owner ? $plans('concluido') : collect(),
            'favorites' => $favorites, 'top10' => $top10,
            'reviews' => $user->show_reviews_public || $owner ? $meta()->where(fn ($q) => $q->whereNotNull('rating')->orWhereNotNull('comment'))
                ->latest()->paginate(12, ['*'], 'reviews_page')->withQueryString() : collect(),
        ];
    }
}
