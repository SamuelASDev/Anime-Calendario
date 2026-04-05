<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\WatchPlan;
use App\Models\WatchPlanDay;
use App\Models\WatchLog;
use Illuminate\Http\Request;

class PersonalWatchPlanController extends Controller
{

    public function index()
    {
        $plans = WatchPlan::with('anime', 'days', 'logs')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->get();

        $activePlans = collect();
        $pausedPlans = collect();
        $completedPlans = collect();

        foreach ($plans as $plan) {
            $plan->current_episode_calculated = $this->calculateCurrentEpisode($plan);

            $totalEpisodes = $plan->anime->episodes;

            $isFinishedByEpisodes = !empty($totalEpisodes)
                && $plan->current_episode_calculated >= $totalEpisodes;

            if ($isFinishedByEpisodes && $plan->watch_status !== 'concluido') {
                $plan->update([
                    'watch_status' => 'concluido',
                    'episodes_watched' => $totalEpisodes,
                ]);

                $plan->watch_status = 'concluido';
                $plan->episodes_watched = $totalEpisodes;
                $plan->current_episode_calculated = $totalEpisodes;
            }

            if ($plan->watch_status === 'concluido') {
                $completedPlans->push($plan);
            } elseif ($plan->watch_status === 'pausado') {
                $pausedPlans->push($plan);
            } else {
                $activePlans->push($plan);
            }
        }

        return view('personal.index', compact('activePlans', 'pausedPlans', 'completedPlans'));
    }

    private function calculateCurrentEpisode(WatchPlan $plan, ?string $testDate = null): int
    {
        $startDate = \Carbon\Carbon::parse($plan->start_date)->startOfDay();
        $today = $testDate
            ? \Carbon\Carbon::parse($testDate)->startOfDay()
            : \Carbon\Carbon::today();

        $daysMap = $plan->days->keyBy('day_of_week');
        $logsMap = $plan->logs->keyBy(function ($log) {
            return \Carbon\Carbon::parse($log->watched_date)->format('Y-m-d');
        });

        $currentEpisode = (int) $plan->episodes_watched;
        $date = $startDate->copy();

        while ($date->lt($today)) {
            $dateKey = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek;

            $dayConfig = $daysMap[$dayOfWeek] ?? null;
            $log = $logsMap[$dateKey] ?? null;

            if ($log) {
                $currentEpisode += (int) $log->episodes_watched_today;
            } elseif ($dayConfig) {
                if (!$dayConfig->is_variable && (int) $dayConfig->episodes_planned > 0) {
                    $currentEpisode += (int) $dayConfig->episodes_planned;
                }
            }

            if (!empty($plan->anime->episodes) && $currentEpisode >= $plan->anime->episodes) {
                return (int) $plan->anime->episodes;
            }

            $date->addDay();
        }

        return $currentEpisode;
    }

        public function edit(WatchPlan $plan)
    {
        $plan = WatchPlan::with('anime', 'days')
            ->where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('personal.edit', compact('plan'));
    }

    public function update(Request $request, WatchPlan $plan)
    {
        $plan = WatchPlan::where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'start_date' => ['required', 'date'],
            'episodes_watched' => ['required', 'integer', 'min:0'],
            'watch_status' => ['required', 'string'],

            'monday' => ['required', 'integer', 'min:0'],
            'tuesday' => ['required', 'integer', 'min:0'],
            'wednesday' => ['required', 'integer', 'min:0'],
            'thursday' => ['required', 'integer', 'min:0'],
            'friday' => ['required', 'integer', 'min:0'],
            'saturday' => ['required', 'integer', 'min:0'],
            'sunday' => ['required', 'integer', 'min:0'],
        ]);

        $plan->update([
            'start_date' => $request->start_date,
            'episodes_watched' => $request->episodes_watched,
            'watch_status' => $request->watch_status,
        ]);

        $plan->days()->delete();

        $daysMap = [
            1 => ['episodes_planned' => $request->monday, 'is_variable' => $request->has('monday_variable')],
            2 => ['episodes_planned' => $request->tuesday, 'is_variable' => $request->has('tuesday_variable')],
            3 => ['episodes_planned' => $request->wednesday, 'is_variable' => $request->has('wednesday_variable')],
            4 => ['episodes_planned' => $request->thursday, 'is_variable' => $request->has('thursday_variable')],
            5 => ['episodes_planned' => $request->friday, 'is_variable' => $request->has('friday_variable')],
            6 => ['episodes_planned' => $request->saturday, 'is_variable' => $request->has('saturday_variable')],
            0 => ['episodes_planned' => $request->sunday, 'is_variable' => $request->has('sunday_variable')],
        ];

        foreach ($daysMap as $dayOfWeek => $data) {
            WatchPlanDay::create([
                'watch_plan_id' => $plan->id,
                'day_of_week' => $dayOfWeek,
                'episodes_planned' => $data['episodes_planned'],
                'is_variable' => $data['is_variable'],
            ]);
        }

        return redirect()->route('personal.animes.index')
            ->with('success', 'Plano pessoal atualizado com sucesso.');
    }

    public function createLog(WatchPlan $plan)
    {
        $plan = WatchPlan::with('anime')
            ->where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('personal.log', compact('plan'));
    }

    public function storeLog(Request $request, WatchPlan $plan)
    {
        $plan = WatchPlan::where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'watched_date' => ['required', 'date'],
            'episodes_watched_today' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        WatchLog::updateOrCreate(
            [
                'watch_plan_id' => $plan->id,
                'watched_date' => $request->watched_date,
            ],
            [
                'episodes_watched_today' => $request->episodes_watched_today,
                'notes' => $request->notes,
            ]
        );

        return redirect()->route('personal.animes.index')
            ->with('success', 'Registro pessoal salvo com sucesso.');
    }

    public function pause(WatchPlan $plan)
    {
        $plan = WatchPlan::where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $plan->update([
            'watch_status' => 'pausado',
        ]);

        return redirect()->route('personal.animes.index')
            ->with('success', 'Anime pausado com sucesso.');
    }

    public function resume(WatchPlan $plan)
    {
        $plan = WatchPlan::where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $plan->update([
            'watch_status' => 'assistindo',
        ]);

        return redirect()->route('personal.animes.index')
            ->with('success', 'Anime retomado com sucesso.');
    }

    public function complete(WatchPlan $plan)
    {
        $plan = WatchPlan::with('anime', 'logs')
            ->where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $lastEpisode = $plan->anime->episodes;

        if ($lastEpisode !== null) {
            $currentWatched = (int) $plan->episodes_watched;
            $lastEpisode = (int) $lastEpisode;

            $episodesWatchedToday = $lastEpisode - $currentWatched;

            if ($episodesWatchedToday > 0) {
                $today = \Carbon\Carbon::today()->format('Y-m-d');

                $log = $plan->logs()->firstOrNew([
                    'watched_date' => $today,
                ]);

                $alreadyLoggedToday = (int) ($log->episodes_watched_today ?? 0);
                $log->episodes_watched_today = $alreadyLoggedToday + $episodesWatchedToday;

                if (empty($log->notes)) {
                    $log->notes = 'Anime concluído manualmente.';
                }

                $log->save();
            }

            $plan->episodes_watched = $lastEpisode;
        }

        $plan->watch_status = 'concluido';
        $plan->save();

        return redirect()->route('personal.animes.index')
            ->with('success', 'Anime marcado como concluído.');
    }

    public function destroy(WatchPlan $plan)
    {
        $plan = WatchPlan::where('id', $plan->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $plan->delete();

        return redirect()->route('personal.animes.index')
            ->with('success', 'Anime removido do seu espaço.');
    }

    public function calendar()
    {
        $plans = WatchPlan::with('anime', 'days', 'logs')
            ->where('user_id', auth()->id())
            ->where('watch_status', 'assistindo')
            ->get();

        $allSchedules = [];

        foreach ($plans as $plan) {
            $schedule = app(\App\Http\Controllers\WatchPlanController::class)->generateSchedule($plan);

            foreach ($schedule as $entry) {
                $allSchedules[] = $entry;
            }
        }

        usort($allSchedules, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $calendarData = [];

        foreach ($allSchedules as $entry) {
            $calendarData[$entry['date_formatted']][] = $entry;
        }

        return view('personal.calendar', compact('calendarData'));
    }

    public function completed()
    {
        $plans = WatchPlan::with([
                'anime',
                'logs',
                'anime.userMeta.user'
            ])
            ->where('user_id', auth()->id())
            ->where('watch_status', 'concluido')
            ->orderByDesc('updated_at')
            ->paginate(12);

        return view('personal.completed', compact('plans'));
    }

    public function history()
    {
        $plans = WatchPlan::with('anime', 'days', 'logs')
            ->where('user_id', auth()->id())
            ->whereIn('watch_status', ['assistindo', 'concluido'])
            ->get();

        $allHistory = [];
        $yesterday = \Carbon\Carbon::yesterday()->startOfDay();

        foreach ($plans as $plan) {
            if (!$plan->anime) {
                continue;
            }

            $startDate = \Carbon\Carbon::parse($plan->start_date)->startOfDay();

            if ($startDate->gt($yesterday)) {
                continue;
            }

            $daysMap = $plan->days->keyBy('day_of_week');
            $logsMap = $plan->logs->keyBy(function ($log) {
                return \Carbon\Carbon::parse($log->watched_date)->format('Y-m-d');
            });

            $currentEpisode = $plan->episodes_watched + 1;
            $date = $startDate->copy();

            while ($date->lte($yesterday)) {
                $dateKey = $date->format('Y-m-d');
                $dayOfWeek = $date->dayOfWeek;

                $dayConfig = $daysMap[$dayOfWeek] ?? null;
                $log = $logsMap[$dateKey] ?? null;

                if (!empty($plan->anime->episodes) && $currentEpisode > $plan->anime->episodes) {
                    $currentEpisode = $plan->anime->episodes;
                }

                if ($log) {
                    $episodesToday = (int) $log->episodes_watched_today;

                    if ($episodesToday > 0) {
                        $from = $currentEpisode;
                        $to = $currentEpisode + $episodesToday - 1;

                        if (!empty($plan->anime->episodes)) {
                            $to = min($to, $plan->anime->episodes);
                        }

                        $allHistory[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => "Episódios: {$from} ao {$to}",
                            'type' => 'logged',
                            'notes' => $log->notes,
                            'plan_id' => $plan->id,
                            'episodes_to_confirm' => $episodesToday,
                            'can_confirm' => false,
                        ];

                        $currentEpisode += $episodesToday;
                    } else {
                        $allHistory[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => 'Não assistimos neste dia',
                            'type' => 'missed',
                            'notes' => $log->notes,
                            'plan_id' => $plan->id,
                            'episodes_to_confirm' => 0,
                            'can_confirm' => false,
                        ];
                    }
                } elseif ($dayConfig) {
                    if ($dayConfig->is_variable) {
                        $allHistory[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => "Ep atual: {$currentEpisode} - A definir",
                            'type' => 'variable',
                            'notes' => null,
                            'plan_id' => $plan->id,
                            'episodes_to_confirm' => null,
                            'can_confirm' => false,
                        ];
                    } elseif ((int) $dayConfig->episodes_planned > 0) {
                        $episodesToday = (int) $dayConfig->episodes_planned;

                        $from = $currentEpisode;
                        $to = $currentEpisode + $episodesToday - 1;

                        if (!empty($plan->anime->episodes)) {
                            $to = min($to, $plan->anime->episodes);
                        }

                        $allHistory[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => "Episódios: {$from} ao {$to}",
                            'type' => 'planned',
                            'notes' => null,
                            'plan_id' => $plan->id,
                            'episodes_to_confirm' => $episodesToday,
                            'can_confirm' => true,
                        ];

                        $currentEpisode += $episodesToday;
                    }
                }

                $date->addDay();
            }
        }

        usort($allHistory, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        $historyData = [];

        foreach ($allHistory as $entry) {
            $historyData[$entry['date_formatted']][] = $entry;
        }

        return view('personal.history', compact('historyData'));
    }

    public function create(Anime $anime)
    {
        return view('personal.create', compact('anime'));
    }

    public function store(Request $request, Anime $anime)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'monday' => ['required', 'integer', 'min:0'],
            'tuesday' => ['required', 'integer', 'min:0'],
            'wednesday' => ['required', 'integer', 'min:0'],
            'thursday' => ['required', 'integer', 'min:0'],
            'friday' => ['required', 'integer', 'min:0'],
            'saturday' => ['required', 'integer', 'min:0'],
            'sunday' => ['required', 'integer', 'min:0'],
            'episodes_watched' => ['required', 'integer', 'min:0'],
        ]);

        $plan = WatchPlan::updateOrCreate(
            [
                'anime_id' => $anime->id,
                'user_id' => auth()->id(),
            ],
            [
                'episodes_watched' => $request->has('is_completed')
                    ? ($anime->episodes ?? $request->episodes_watched)
                    : $request->episodes_watched,
                'start_date' => $request->start_date,
                'watch_status' => $request->has('is_completed') ? 'concluido' : 'assistindo',
            ]
        );

        $plan->days()->delete();

        $daysMap = [
            1 => ['episodes_planned' => $request->monday, 'is_variable' => $request->has('monday_variable')],
            2 => ['episodes_planned' => $request->tuesday, 'is_variable' => $request->has('tuesday_variable')],
            3 => ['episodes_planned' => $request->wednesday, 'is_variable' => $request->has('wednesday_variable')],
            4 => ['episodes_planned' => $request->thursday, 'is_variable' => $request->has('thursday_variable')],
            5 => ['episodes_planned' => $request->friday, 'is_variable' => $request->has('friday_variable')],
            6 => ['episodes_planned' => $request->saturday, 'is_variable' => $request->has('saturday_variable')],
            0 => ['episodes_planned' => $request->sunday, 'is_variable' => $request->has('sunday_variable')],
        ];

        foreach ($daysMap as $dayOfWeek => $data) {
            WatchPlanDay::create([
                'watch_plan_id' => $plan->id,
                'day_of_week' => $dayOfWeek,
                'episodes_planned' => $data['episodes_planned'],
                'is_variable' => $data['is_variable'],
            ]);
        }

        return redirect()->route('personal.calendar')
            ->with('success', 'Anime adicionado ao seu espaço com sucesso!');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:watch_plans,id'],
            'date' => ['required', 'date'],
            'episodes' => ['nullable', 'integer', 'min:0'],
            'action' => ['required', 'in:confirm,missed'],
        ]);

        $plan = WatchPlan::where('id', $request->plan_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $episodesWatched = $request->action === 'missed' ? 0 : (int) $request->episodes;

        WatchLog::updateOrCreate(
            [
                'watch_plan_id' => $plan->id,
                'watched_date' => $request->date,
            ],
            [
                'episodes_watched_today' => $episodesWatched,
                'notes' => $request->action === 'missed'
                    ? 'Marcado no meu espaço como não assistido.'
                    : 'Confirmado no meu espaço.',
            ]
        );

        return redirect()->route('personal.history')
            ->with('success', 'Histórico pessoal atualizado com sucesso!');
    }
}