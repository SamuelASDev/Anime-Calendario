<?php

namespace App\Services\Library;

use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;
use Illuminate\Support\Facades\DB;

class CollectionOrder
{
    public function move(User $user, int $animeId, string $collection, string $direction): void
    {
        DB::transaction(function () use ($user, $animeId, $collection, $direction) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $column = $collection === 'favorites' ? 'favorite_position' : 'completed_position';
            $meta = UserAnimeMeta::where('user_id', $user->id);
            if ($collection === 'favorites') {
                $ids = $meta->where('is_favorite', true)->orderByRaw('favorite_position IS NULL')
                    ->orderBy('favorite_position')->orderBy('id')->pluck('anime_id')->all();
            } else {
                $ids = WatchPlan::ownedBy($user->id)->where('watch_status', 'concluido')
                    ->orderByRaw('(SELECT completed_position FROM user_anime_meta WHERE user_anime_meta.user_id = watch_plans.user_id AND user_anime_meta.anime_id = watch_plans.anime_id) IS NULL')
                    ->orderBy(UserAnimeMeta::select('completed_position')->whereColumn('user_id', 'watch_plans.user_id')->whereColumn('anime_id', 'watch_plans.anime_id')->limit(1))
                    ->orderByDesc('watch_plans.id')->pluck('anime_id')->all();
            }
            $index = array_search($animeId, $ids, true);
            abort_if($index === false, 404);
            $target = $direction === 'up' ? $index - 1 : $index + 1;
            if (! isset($ids[$target])) {
                return;
            }
            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
            foreach ($ids as $position => $id) {
                UserAnimeMeta::updateOrCreate(['user_id' => $user->id, 'anime_id' => $id], [$column => $position + 1]);
            }
        });
    }
}
