<?php

namespace App\Services\Planning;

use App\Models\WatchPlan;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/** Read-only projection shared by progress cards, calendars and history. */
class PlanProjection
{
    public function currentEpisode(WatchPlan $plan, ?CarbonInterface $at = null): int
    {
        if ($plan->watch_status === 'pretendo_assistir') {
            return 0;
        }
        $plan->loadMissing('anime', 'days', 'logs', 'pauses');
        if ($plan->watch_status === 'concluido' && $plan->anime?->episodes) {
            return (int) $plan->anime->episodes;
        }
        $at = CarbonImmutable::instance($at ?? today())->startOfDay();
        if ($plan->completed_at && $plan->completed_at->lt($at)) {
            $at = CarbonImmutable::instance($plan->completed_at)->startOfDay();
        }
        $start = CarbonImmutable::parse($plan->start_date)->startOfDay();
        $episode = (int) $plan->episodes_watched;
        $episode += $this->scheduledBetween($plan, $start, $at);
        foreach ($plan->pauses as $pause) {
            $from = CarbonImmutable::instance($pause->started_on)->max($start);
            $until = $pause->resumed_on ? CarbonImmutable::instance($pause->resumed_on)->min($at) : $at;
            $episode -= $this->scheduledBetween($plan, $from, $until);
        }
        $days = $plan->days->keyBy('day_of_week');
        foreach ($plan->logs as $log) {
            $date = CarbonImmutable::parse($log->watched_date)->startOfDay();
            if ($date->lt($start) || $date->gt($at)) {
                continue;
            }
            $paused = $this->pausedOn($plan, $date);
            $day = $days->get($date->dayOfWeek);
            $planned = $date->lt($at) && ! $paused && $day && ! $day->is_variable ? (int) $day->episodes_planned : 0;
            $episode += (int) $log->episodes_watched_today - $planned;
        }

        return $plan->anime?->episodes ? min($episode, $plan->anime->episodes) : $episode;
    }

    private function scheduledBetween(WatchPlan $plan, CarbonImmutable $from, CarbonImmutable $until): int
    {
        if ($until->lte($from)) {
            return 0;
        }
        $length = (int) $from->diffInDays($until);
        $weeks = intdiv($length, 7);
        $remainder = $length % 7;
        $sum = 0;
        foreach ($plan->days as $day) {
            if (! $day->is_variable) {
                $offset = ((int) $day->day_of_week - $from->dayOfWeek + 7) % 7;
                $sum += ($weeks + ($offset < $remainder ? 1 : 0)) * (int) $day->episodes_planned;
            }
        }

        return $sum;
    }

    private function pausedOn(WatchPlan $plan, CarbonInterface $date): bool
    {
        return $plan->pauses->contains(fn ($pause) => $date->gte($pause->started_on)
            && (! $pause->resumed_on || $date->lt($pause->resumed_on)));
    }

    public function entries(WatchPlan $plan, CarbonInterface $until, ?CarbonInterface $from = null): array
    {
        if ($plan->watch_status === 'pretendo_assistir') {
            return [];
        }
        $plan->loadMissing('anime', 'days', 'logs', 'pauses');
        if (! $plan->anime) {
            return [];
        }
        $days = $plan->days->keyBy('day_of_week');
        $logs = $plan->logs->keyBy(fn ($log) => substr((string) $log->watched_date, 0, 10));
        $start = CarbonImmutable::parse($plan->start_date)->startOfDay();
        $from = $from ? CarbonImmutable::instance($from)->startOfDay()->max($start) : $start;
        // Calculate the prefix arithmetically, then iterate only over the requested window.
        $episode = $from->gt($start) ? $this->beforeDay($plan, $from) + 1 : (int) $plan->episodes_watched + 1;
        $total = $plan->anime->episodes;
        $entries = [];
        $end = CarbonImmutable::instance($until)->startOfDay();
        if ($plan->completed_at && $plan->completed_at->lt($end)) {
            $end = CarbonImmutable::instance($plan->completed_at)->startOfDay();
        }
        for ($date = $from; $date->lte($end); $date = $date->addDay()) {
            if ($total && $episode > $total) {
                break;
            }
            $key = $date->toDateString();
            $log = $logs->get($key);
            $day = $days->get($date->dayOfWeek);
            $paused = $this->pausedOn($plan, $date);
            if (! $log && ($paused || ! $day || (! $day->is_variable && ! $day->episodes_planned))) {
                continue;
            }
            $count = $log ? (int) $log->episodes_watched_today : ($day->is_variable ? 0 : (int) $day->episodes_planned);
            $count = $total ? min($count, max(0, $total - $episode + 1)) : $count;
            $type = $log ? ($count > 0 ? 'logged' : 'missed') : ($day->is_variable ? 'variable' : 'planned');
            $entries[] = [
                'date' => $key, 'date_formatted' => $date->format('d/m/Y'),
                'anime_title' => $plan->anime->title, 'anime_image' => $plan->anime->image,
                'label' => match ($type) {
                    'variable' => "Ep atual: {$episode} - A definir",
                    'missed' => 'Sem episódios assistidos',
                    default => 'Episódios: '.$episode.' ao '.($episode + $count - 1),
                },
                'type' => $type, 'notes' => $log?->notes, 'plan_id' => $plan->id,
                'episodes_to_confirm' => $count, 'can_confirm' => $type === 'planned',
            ];
            $episode += $count;
        }

        return $entries;
    }

    private function beforeDay(WatchPlan $plan, CarbonImmutable $date): int
    {
        // Avoid completed-state display shortcuts when reconstructing a historical prefix.
        $projection = clone $plan;
        $projection->watch_status = 'assistindo';
        $projection->completed_at = null;
        $projection->setRelation('logs', $plan->logs->filter(fn ($log) => substr((string) $log->watched_date, 0, 10) < $date->toDateString()));

        return $this->currentEpisode($projection, $date);
    }

    public function grouped(iterable $plans, bool $history = false, ?CarbonInterface $at = null, ?CarbonInterface $from = null): array
    {
        $today = CarbonImmutable::instance($at ?? today())->startOfDay();
        $entries = [];
        foreach ($plans as $plan) {
            foreach ($this->entries($plan, $history ? $today->subDay() : $today->addMonth(), $from ?? ($history ? $today->subDays(90) : $today)) as $entry) {
                if ($history || $entry['date'] >= $today->toDateString()) {
                    $entries[] = $entry;
                }
            }
        }
        usort($entries, fn ($a, $b) => $history ? strcmp($b['date'], $a['date']) : strcmp($a['date'], $b['date']));

        return collect($entries)->groupBy('date_formatted')->all();
    }
}
