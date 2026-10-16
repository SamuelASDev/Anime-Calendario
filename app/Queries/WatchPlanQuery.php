<?php

namespace App\Queries;

use App\Models\WatchPlan;
use Illuminate\Database\Eloquent\Builder;

class WatchPlanQuery
{
    public function forOwner(?int $userId): Builder
    {
        return WatchPlan::query()->when($userId === null, fn ($q) => $q->global(), fn ($q) => $q->ownedBy($userId));
    }

    public function loaded(?int $userId): Builder
    {
        return $this->forOwner($userId)->with('anime', 'days', 'logs', 'pauses');
    }

    public function statusPages(?int $userId, int $perPage = 12): array
    {
        $pages = [];
        foreach (['planned' => 'pretendo_assistir', 'active' => 'assistindo', 'paused' => 'pausado', 'completed' => 'concluido'] as $key => $status) {
            $pages[$key] = $this->loaded($userId)->where('watch_status', $status)->latest('updated_at')
                ->paginate($perPage, ['*'], $key.'_page')->withQueryString();
        }

        return $pages;
    }
}
