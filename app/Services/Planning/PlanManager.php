<?php

namespace App\Services\Planning;

use App\Enums\WatchStatus;
use App\Models\Anime;
use App\Models\User;
use App\Models\WatchPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanManager
{
    public const WEEK = [0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'];

    public function __construct(private PlanProjection $projection) {}

    public function saveForLater(Anime $anime, User $user): WatchPlan
    {
        return DB::transaction(function () use ($anime, $user) {
            Anime::whereKey($anime->id)->lockForUpdate()->firstOrFail();

            return WatchPlan::firstOrCreate(['anime_id' => $anime->id, 'user_id' => $user->id], [
                'episodes_watched' => 0, 'episodes_per_day' => 0,
                'start_date' => today()->toDateString(), 'watch_status' => WatchStatus::Planned->value,
            ]);
        });
    }

    public function save(Anime $anime, ?int $userId, array $data, ?WatchPlan $existing = null): WatchPlan
    {
        return DB::transaction(function () use ($anime, $userId, $data, $existing) {
            // Serialize creation for the same anime, including the nullable global owner.
            Anime::whereKey($anime->id)->lockForUpdate()->firstOrFail();
            $plan = $existing ? WatchPlan::whereKey($existing->id)->lockForUpdate()->firstOrFail()
                : WatchPlan::firstOrNew(['anime_id' => $anime->id, 'user_id' => $userId]);
            if ($plan->anime_id !== $anime->id || $plan->user_id !== $userId) {
                throw new \InvalidArgumentException('Plan owner and anime must match the requested scope.');
            }
            $status = WatchStatus::from($data['watch_status'] ?? (! empty($data['is_completed']) ? 'concluido' : 'assistindo'))->value;
            if ($status === WatchStatus::Planned->value && ((int) $data['episodes_watched'] > 0 || ($plan->exists && $plan->logs()->exists()))) {
                throw ValidationException::withMessages(['watch_status' => 'Animes com episódios registrados devem usar o status Pausado para interromper a agenda.']);
            }
            if ($anime->episodes && $data['episodes_watched'] > $anime->episodes) {
                throw ValidationException::withMessages(['episodes_watched' => 'O progresso inicial não pode ultrapassar o total de episódios.']);
            }
            $plan->fill([
                'episodes_watched' => $data['episodes_watched'],
                'start_date' => $data['start_date'], 'episodes_per_day' => 1,
            ]);
            $plan->watch_status ??= WatchStatus::Watching->value;
            $plan->save();
            foreach (self::WEEK as $number => $name) {
                $plan->days()->updateOrCreate(['day_of_week' => $number], [
                    'episodes_planned' => $data[$name], 'is_variable' => (bool) ($data[$name.'_variable'] ?? false),
                ]);
            }
            $this->transition($plan, $status, $data['start_date']);

            return $plan->fresh();
        });
    }

    public function transition(WatchPlan $plan, string $status, ?string $startDate = null): void
    {
        WatchStatus::from($status);
        DB::transaction(function () use ($plan, $status, $startDate) {
            $plan = WatchPlan::with('anime', 'days', 'logs', 'pauses')->whereKey($plan->id)->lockForUpdate()->firstOrFail();
            if ($plan->watch_status === $status) {
                return;
            }
            if ($status === WatchStatus::Planned->value) {
                if ($plan->episodes_watched > 0 || $plan->logs->isNotEmpty()) {
                    throw ValidationException::withMessages(['watch_status' => 'Use Pausado para guardar um anime que você já começou.']);
                }
            } elseif ($plan->watch_status === WatchStatus::Planned->value) {
                // Time spent on the watch-later list must never become watched episodes.
                $plan->start_date = $startDate ?? today()->toDateString();
                if ($status === WatchStatus::Watching->value && $plan->days->isEmpty()) {
                    foreach (self::WEEK as $day => $name) {
                        $plan->days()->create(['day_of_week' => $day, 'episodes_planned' => 1, 'is_variable' => false]);
                    }
                    $plan->episodes_per_day = 1;
                }
            }
            if ($status === 'concluido') {
                if ($plan->start_date > today()->toDateString()) {
                    $plan->start_date = today()->toDateString();
                }
                $current = $this->projection->currentEpisode($plan);
                $remaining = max(0, ($plan->anime->episodes ?? $current) - $current);
                if ($remaining > 0) {
                    $log = $plan->logs()->firstOrNew(['watched_date' => today()->toDateString()]);
                    $log->episodes_watched_today = (int) $log->episodes_watched_today + $remaining;
                    $log->notes = 'Anime concluído manualmente.';
                    $log->save();
                }
                $plan->completed_at = now();
            } else {
                $plan->completed_at = null;
            }
            if ($status === 'pausado') {
                $plan->pauses()->create(['started_on' => today()]);
            } else {
                $plan->pauses()->whereNull('resumed_on')->update(['resumed_on' => today()->toDateString()]);
            }
            $plan->watch_status = $status;
            $plan->save();
        });
    }

    public function log(WatchPlan $plan, array $data): void
    {
        DB::transaction(function () use ($plan, $data) {
            $plan = WatchPlan::whereKey($plan->id)->lockForUpdate()->firstOrFail();
            if ($plan->watch_status === WatchStatus::Planned->value) {
                throw ValidationException::withMessages(['watched_date' => 'Comece a assistir antes de registrar episódios deste anime.']);
            }
            if ($data['watched_date'] < substr((string) $plan->start_date, 0, 10) || $data['watched_date'] > today()->toDateString()) {
                throw ValidationException::withMessages(['watched_date' => 'A data deve estar entre o início do plano e hoje.']);
            }
            $plan->logs()->updateOrCreate(['watched_date' => $data['watched_date']], [
                'episodes_watched_today' => $data['episodes_watched_today'], 'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function copy(WatchPlan $source, User $user): WatchPlan
    {
        return DB::transaction(function () use ($source, $user) {
            Anime::whereKey($source->anime_id)->lockForUpdate()->firstOrFail();
            $existing = WatchPlan::ownedBy($user->id)->where('anime_id', $source->anime_id)->first();
            if ($existing) {
                return $existing;
            }
            $source->loadMissing('anime', 'days', 'logs', 'pauses');
            $current = $this->projection->currentEpisode($source);
            $completed = $source->anime->episodes && $current >= $source->anime->episodes;
            $copy = WatchPlan::create([
                'user_id' => $user->id, 'anime_id' => $source->anime_id,
                'episodes_watched' => $current, 'episodes_per_day' => $source->episodes_per_day,
                'start_date' => max(today()->toDateString(), substr((string) $source->start_date, 0, 10)),
                'watch_status' => $completed ? 'concluido' : 'assistindo',
            ]);
            if ($completed) {
                $copy->forceFill(['completed_at' => now()])->save();
            }
            foreach ($source->days as $day) {
                $copy->days()->create($day->only('day_of_week', 'episodes_planned', 'is_variable'));
            }

            return $copy;
        });
    }
}
