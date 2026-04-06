<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\WatchPlan;
use App\Models\WatchPlanDay;
use App\Models\UserAnimeMeta;
use Illuminate\Http\Request;

class WatchPlanController extends Controller
{
    public function create(Anime $anime)
    {
        return view('watch-plans.create', compact('anime'));
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
            ['anime_id' => $anime->id],
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
            1 => [
                'episodes_planned' => $request->monday,
                'is_variable' => $request->has('monday_variable'),
            ],
            2 => [
                'episodes_planned' => $request->tuesday,
                'is_variable' => $request->has('tuesday_variable'),
            ],
            3 => [
                'episodes_planned' => $request->wednesday,
                'is_variable' => $request->has('wednesday_variable'),
            ],
            4 => [
                'episodes_planned' => $request->thursday,
                'is_variable' => $request->has('thursday_variable'),
            ],
            5 => [
                'episodes_planned' => $request->friday,
                'is_variable' => $request->has('friday_variable'),
            ],
            6 => [
                'episodes_planned' => $request->saturday,
                'is_variable' => $request->has('saturday_variable'),
            ],
            0 => [
                'episodes_planned' => $request->sunday,
                'is_variable' => $request->has('sunday_variable'),
            ],
        ];

        foreach ($daysMap as $dayOfWeek => $data) {
            WatchPlanDay::create([
                'watch_plan_id' => $plan->id,
                'day_of_week' => $dayOfWeek,
                'episodes_planned' => $data['episodes_planned'],
                'is_variable' => $data['is_variable'],
            ]);
        }

        return redirect()->route('anime.search')->with('success', 'Plano criado com sucesso!');
    }

    public function generateSchedule($plan)
    {
        if (!$plan->anime) {
            return [];
        }

        $startDate = \Carbon\Carbon::parse($plan->start_date)->startOfDay();
        $today = request('test_date')
        ? \Carbon\Carbon::parse(request('test_date'))
        : \Carbon\Carbon::today();
        $endDate = $today->copy()->addMonth();

        $daysMap = $plan->days->keyBy('day_of_week');
        $logsMap = $plan->logs->keyBy(function ($log) {
            return \Carbon\Carbon::parse($log->watched_date)->format('Y-m-d');
        });

        $schedule = [];

        // episódio base
        $currentEpisode = $plan->episodes_watched + 1;

        // começa do start_date, não de hoje
        $date = $startDate->copy();

        while ($date->lte($endDate)) {
            $dateKey = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek;

            $dayConfig = $daysMap[$dayOfWeek] ?? null;
            $log = $logsMap[$dateKey] ?? null;

            // se já passou do total de episódios, para
            if (!empty($plan->anime->episodes) && $currentEpisode > $plan->anime->episodes) {
                break;
            }

            $isPast = $date->lt($today);
            $isTodayOrFuture = $date->gte($today);

            // 1) se existe log, ele manda em tudo
            if ($log) {
                $episodesToday = (int) $log->episodes_watched_today;

                if ($episodesToday > 0) {
                    $from = $currentEpisode;
                    $to = $currentEpisode + $episodesToday - 1;

                    if (!empty($plan->anime->episodes)) {
                        $to = min($to, $plan->anime->episodes);
                    }

                    if ($isTodayOrFuture) {
                        $schedule[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => "Episódios: {$from} ao {$to}",
                            'type' => 'logged',
                            'notes' => $log->notes,
                        ];
                    }

                    $currentEpisode += $episodesToday;
                } else {
                    if ($isTodayOrFuture) {
                        $schedule[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => 'Sem episódios assistidos',
                            'type' => 'logged',
                            'notes' => $log->notes,
                        ];
                    }
                }
            }

            // 2) sem log: usa a regra semanal
            elseif ($dayConfig) {
                // dia variável
                if ($dayConfig->is_variable) {
                    // passado sem log: não avança episódio
                    // hoje/futuro: mostra "A definir"
                    if ($isTodayOrFuture) {
                        $schedule[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => "Ep atual: {$currentEpisode} - A definir",
                            'type' => 'variable',
                            'notes' => null,
                        ];
                    }
                }

                // dia fixo com episódios planejados
                elseif ($dayConfig->episodes_planned > 0) {
                    $episodesToday = (int) $dayConfig->episodes_planned;

                    $from = $currentEpisode;
                    $to = $currentEpisode + $episodesToday - 1;

                    if (!empty($plan->anime->episodes)) {
                        $to = min($to, $plan->anime->episodes);
                    }

                    if ($isTodayOrFuture) {
                        $schedule[] = [
                            'date' => $dateKey,
                            'date_formatted' => $date->format('d/m/Y'),
                            'anime_title' => $plan->anime->title,
                            'anime_image' => $plan->anime->image,
                            'label' => "Episódios: {$from} ao {$to}",
                            'type' => 'planned',
                            'notes' => null,
                        ];
                    }

                    // mesmo em dia passado sem log, avança automaticamente
                    $currentEpisode += $episodesToday;
                }
            }

            $date->addDay();
        }

        return $schedule;
    }

    public function calendar()
    {
        $plans = \App\Models\WatchPlan::with('anime', 'days', 'logs')
            ->whereNull('user_id')
            ->where('watch_status', 'assistindo')
            ->get();

        $allSchedules = [];

        foreach ($plans as $plan) {
            $schedule = $this->generateSchedule($plan);

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

        return view('calendar.index', compact('calendarData'));
    }

    public function completed()
    {
        $plans = \App\Models\WatchPlan::with([
                'anime',
                'logs',
                'anime.userMeta.user'
            ])
            ->whereNull('user_id')
            ->where('watch_status', 'concluido')
            ->orderByDesc('updated_at')
            ->paginate(12);

        $myCompletedAnimeIds = \App\Models\WatchPlan::where('user_id', auth()->id())
        ->where('watch_status', 'concluido')
        ->pluck('anime_id')
        ->toArray();

        return view('completed.index', compact('plans', 'myCompletedAnimeIds'));
    }

    public function createReview(Anime $anime)
    {
        $anime->load('userMeta.user');

        $myMeta = $anime->userMeta->firstWhere('user_id', auth()->id());

        return view('completed.review', compact('anime', 'myMeta'));
    }

    public function storeReview(Request $request, Anime $anime)
    {
        $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        UserAnimeMeta::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'anime_id' => $anime->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return redirect()->route('completed.animes')
            ->with('success', 'Review salva com sucesso!');
    }

    public function history()
    {
        $plans = \App\Models\WatchPlan::with('anime', 'days', 'logs')
            ->whereNull('user_id')
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
                        break;
                }

                // 1) Se existe log, ele manda
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
                }

                // 2) Sem log: usa regra semanal
                elseif ($dayConfig) {
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

                        // mantém a lógica atual do teu sistema
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

        return view('history.index', compact('historyData'));
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:watch_plans,id'],
            'date' => ['required', 'date'],
            'episodes' => ['nullable', 'integer', 'min:0'],
            'action' => ['required', 'in:confirm,missed'],
        ]);

        $episodesWatched = $request->action === 'missed'
            ? 0
            : (int) $request->episodes;

        \App\Models\WatchLog::updateOrCreate(
            [
                'watch_plan_id' => $request->plan_id,
                'watched_date' => $request->date,
            ],
            [
                'episodes_watched_today' => $episodesWatched,
                'notes' => $request->action === 'missed'
                    ? 'Marcado pelo histórico como não assistido.'
                    : 'Confirmado pelo histórico.',
            ]
        );

        return redirect()->route('history')
            ->with('success', $request->action === 'missed'
                ? 'Dia marcado como não assistido.'
                : 'Episódios confirmados com sucesso.');
    }

    public function markAsWatched(Anime $anime)
    {
        \App\Models\WatchPlan::updateOrCreate(
            [
                'anime_id' => $anime->id,
                'user_id' => auth()->id(),
            ],
            [
                'episodes_watched' => $anime->episodes ?? 0,
                'episodes_per_day' => 0,
                'start_date' => now()->toDateString(),
                'watch_status' => 'concluido',
            ]
        );

        return redirect()
            ->route('completed.review.create', $anime->id)
            ->with('success', 'Anime adicionado aos seus concluídos. Agora você já pode fazer sua review!');
    }
        
}
