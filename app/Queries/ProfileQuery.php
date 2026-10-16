<?php

namespace App\Queries;

use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;

class ProfileQuery
{
    public function sections(User $user, bool $owner = false, array $filters = []): array
    {
        $plans = function (string $status) use ($user, $filters) {
            $query = WatchPlan::ownedBy($user->id)->where('watch_status', $status)
                ->whereHas('anime', fn ($anime) => app(AnimeFilters::class)->apply($anime, $filters))
                ->with('user')->with(['anime.userMeta' => fn ($q) => $q->where('user_id', auth()->id())]);
            if ($status === 'concluido') {
                $query->orderByRaw('(SELECT completed_position FROM user_anime_meta WHERE user_anime_meta.user_id = watch_plans.user_id AND user_anime_meta.anime_id = watch_plans.anime_id) IS NULL')
                    ->orderBy(UserAnimeMeta::select('completed_position')->whereColumn('user_id', 'watch_plans.user_id')->whereColumn('anime_id', 'watch_plans.anime_id')->limit(1));
            }

            return $query->orderByDesc('watch_plans.id')->paginate(12, ['*'], $status.'_page')->withQueryString()->fragment(match ($status) {
                'assistindo' => 'watching', 'concluido' => 'completed', default => 'planned'
            });
        };
        $meta = fn () => UserAnimeMeta::where('user_id', $user->id)->with('anime')
            ->whereHas('anime', fn ($anime) => app(AnimeFilters::class)->apply($anime, $filters));
        $counts = WatchPlan::ownedBy($user->id)->selectRaw('watch_status, COUNT(*) as total')->groupBy('watch_status')->pluck('total', 'watch_status');
        $stats = [
            'favorites' => $owner || $user->show_favorites_public ? UserAnimeMeta::where('user_id', $user->id)->where('is_favorite', true)->count() : null,
            'watching' => $owner || $user->show_watching_public ? (int) ($counts['assistindo'] ?? 0) : null,
            'completed' => $owner || $user->show_completed_public ? (int) ($counts['concluido'] ?? 0) : null,
            'planned' => $owner ? (int) ($counts['pretendo_assistir'] ?? 0) : null,
        ];
        $favorites = $user->show_favorites_public || $owner ? $meta()->where('is_favorite', true)->orderByRaw('favorite_position IS NULL')->orderBy('favorite_position')->orderBy('id')->paginate(12, ['*'], 'favorites_page')->withQueryString()->fragment('favorites') : collect();
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
            'stats' => $stats,
            'planned' => $owner ? $plans('pretendo_assistir') : collect(),
            'watching' => $user->show_watching_public || $owner ? $plans('assistindo') : collect(),
            'completed' => $user->show_completed_public || $owner ? $plans('concluido') : collect(),
            'favorites' => $favorites, 'top10' => $top10,
            'reviews' => $user->show_reviews_public || $owner ? $meta()->where(fn ($q) => $q->whereNotNull('rating')->orWhereNotNull('comment'))
                ->latest()->paginate(12, ['*'], 'reviews_page')->withQueryString()->fragment('reviews') : collect(),
        ];
    }
}
