<?php

namespace App\Services\Library;

use App\Models\User;
use App\Models\UserAnimeMeta;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CollectionOrder
{
    // Unpositioned entries retain the historical ID order without a bulk initialization.
    public const FAVORITE_RANK = 'COALESCE(user_anime_meta.favorite_position, 1000000000000 + user_anime_meta.id)';

    public const COMPLETED_RANK = 'COALESCE(collection_meta.completed_position, 2000000000000 - watch_plans.id)';

    public function entries(int $userId, string $collection): Builder
    {
        if ($collection === 'favorites') {
            return DB::table('user_anime_meta')->where('user_id', $userId)->where('is_favorite', true)
                ->select('anime_id')->selectRaw(self::FAVORITE_RANK.' AS sort_rank');
        }

        return DB::table('watch_plans')->leftJoin('user_anime_meta as collection_meta', function ($join) {
            $join->on('collection_meta.user_id', '=', 'watch_plans.user_id')->on('collection_meta.anime_id', '=', 'watch_plans.anime_id');
        })->where('watch_plans.user_id', $userId)->where('watch_status', 'concluido')
            ->select('watch_plans.anime_id')->selectRaw(self::COMPLETED_RANK.' AS sort_rank');
    }

    public function move(User $user, int $animeId, string $collection, string $direction): void
    {
        abort_unless(in_array($collection, ['favorites', 'completed'], true) && in_array($direction, ['up', 'down'], true), 422);
        DB::transaction(function () use ($user, $animeId, $collection, $direction) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $entries = fn () => DB::query()->fromSub($this->entries($user->id, $collection), 'entries');
            $current = $entries()->where('anime_id', $animeId)->first();
            abort_unless($current, 404);
            $up = $direction === 'up';
            $neighbor = $entries()->where('sort_rank', $up ? '<' : '>', $current->sort_rank)
                ->orderBy('sort_rank', $up ? 'desc' : 'asc')->first();
            if (! $neighbor) {
                return;
            }
            $column = $collection === 'favorites' ? 'favorite_position' : 'completed_position';
            foreach ([[$animeId, $neighbor->sort_rank], [$neighbor->anime_id, $current->sort_rank]] as [$id, $rank]) {
                UserAnimeMeta::updateOrCreate(['user_id' => $user->id, 'anime_id' => $id], [$column => $rank]);
            }
        });
    }
}
