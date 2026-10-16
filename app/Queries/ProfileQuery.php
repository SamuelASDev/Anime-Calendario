<?php

namespace App\Queries;

use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;
use App\Services\Library\CollectionOrder;

class ProfileQuery
{
    public function sections(User $user, bool $owner = false, array $filters = [], array $only = []): array
    {
        $needs = fn (string $block) => $only === [] || in_array($block, $only, true);
        $plans = function (string $status) use ($user, $filters) {
            $query = WatchPlan::ownedBy($user->id)->where('watch_status', $status)
                ->whereHas('anime', fn ($anime) => app(AnimeFilters::class)->apply($anime, $filters))
                ->with('user')->with(['anime.userMeta' => fn ($q) => $q->where('user_id', auth()->id())]);
            if ($status === 'concluido') {
                $query->leftJoinSub(app(CollectionOrder::class)->entries($user->id, 'completed'), 'collection_order', 'collection_order.anime_id', '=', 'watch_plans.anime_id')
                    ->select('watch_plans.*')->orderBy('collection_order.sort_rank');
            }

            return $query->orderByDesc('watch_plans.id')->paginate(12, ['*'], $status.'_page')->withQueryString()->fragment(match ($status) {
                'assistindo' => 'watching', 'concluido' => 'completed', default => 'planned'
            });
        };
        $meta = fn () => UserAnimeMeta::where('user_id', $user->id)->with('anime')
            ->whereHas('anime', fn ($anime) => app(AnimeFilters::class)->apply($anime, $filters));
        $counts = ! $needs('stats') ? collect() : WatchPlan::ownedBy($user->id)->selectRaw('watch_status, COUNT(*) as total')->groupBy('watch_status')->pluck('total', 'watch_status');
        $stats = [
            'favorites' => $needs('stats') && ($owner || $user->show_favorites_public) ? UserAnimeMeta::where('user_id', $user->id)->where('is_favorite', true)->count() : null,
            'watching' => $owner || $user->show_watching_public ? (int) ($counts['assistindo'] ?? 0) : null,
            'completed' => $owner || $user->show_completed_public ? (int) ($counts['concluido'] ?? 0) : null,
            'planned' => $owner ? (int) ($counts['pretendo_assistir'] ?? 0) : null,
        ];
        $favorites = $needs('favorites') && ($user->show_favorites_public || $owner) ? $meta()->where('is_favorite', true)->orderByRaw(CollectionOrder::FAVORITE_RANK)->orderBy('id')->paginate(12, ['*'], 'favorites_page')->withQueryString()->fragment('favorites') : collect();
        $top10 = ($needs('top10') || $needs('nav')) && ($user->show_top10_public || $owner) ? $meta()->whereNotNull('top_position')->orderBy('top_position')->limit(10)->get() : collect();
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
            'stats' => $stats,
            'planned' => $needs('planned') && $owner ? $plans('pretendo_assistir') : collect(),
            'watching' => $needs('watching') && ($user->show_watching_public || $owner) ? $plans('assistindo') : collect(),
            'completed' => $needs('completed') && ($user->show_completed_public || $owner) ? $plans('concluido') : collect(),
            'favorites' => $favorites, 'top10' => $top10,
            'reviews' => $needs('reviews') && ($user->show_reviews_public || $owner) ? $meta()->where(fn ($q) => $q->whereNotNull('rating')->orWhereNotNull('comment'))
                ->latest()->paginate(12, ['*'], 'reviews_page')->withQueryString()->fragment('reviews') : collect(),
        ];
    }
}
