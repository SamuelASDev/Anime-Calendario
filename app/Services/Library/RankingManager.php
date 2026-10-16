<?php

namespace App\Services\Library;

use App\Models\User;
use App\Models\UserAnimeMeta;
use Illuminate\Support\Facades\DB;

class RankingManager
{
    public function change(User $user, int $animeId, string $action, ?int $position = null): void
    {
        DB::transaction(function () use ($user, $animeId, $action, $position) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $meta = UserAnimeMeta::firstOrCreate(['user_id' => $user->id, 'anime_id' => $animeId]);
            $ids = UserAnimeMeta::where('user_id', $user->id)->whereNotNull('top_position')
                ->orderBy('top_position')->orderBy('id')->pluck('anime_id')->all();
            $index = array_search($animeId, $ids, true);
            if (in_array($action, ['up', 'down'], true)) {
                if ($index === false) {
                    return;
                }
                $target = $action === 'up' ? $index - 1 : $index + 1;
                if (isset($ids[$target])) {
                    [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
                }
            } else {
                $ids = array_values(array_filter($ids, fn ($id) => $id !== $animeId));
                if ($action === 'add' || ($action === 'position' && $position !== null)) {
                    array_splice($ids, $action === 'add' ? 0 : min($position - 1, count($ids)), 0, [$animeId]);
                }
            }
            UserAnimeMeta::where('user_id', $user->id)->whereNotNull('top_position')->update(['top_position' => null]);
            foreach (array_slice($ids, 0, 10) as $i => $id) {
                UserAnimeMeta::where('user_id', $user->id)->where('anime_id', $id)->update(['top_position' => $i + 1]);
            }
        });
    }
}
