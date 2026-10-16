<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WatchPlan;

class WatchPlanPolicy
{
    public function update(User $user, WatchPlan $plan): bool
    {
        return $plan->user_id === null ? $user->isAdmin() : $plan->user_id === $user->id;
    }

    public function copy(User $user, WatchPlan $plan): bool
    {
        if ($plan->user_id === null || $plan->user_id === $user->id) {
            return true;
        }

        return (bool) ($plan->user?->is_public && match ($plan->watch_status) {
            'assistindo' => $plan->user->show_watching_public,
            'concluido' => $plan->user->show_completed_public,
            default => false,
        });
    }
}
