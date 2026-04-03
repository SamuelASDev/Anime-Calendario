<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WatchPlanAdminController extends Controller
{

    public function index()
    {
        $testDate = request('test_date');

        $plans = \App\Models\WatchPlan::with('anime', 'days', 'logs')
            ->orderByDesc('updated_at')
            ->get();

        $activePlans = collect();
        $pausedPlans = collect();
        $completedPlans = collect();

        foreach ($plans as $plan) {
            $plan->current_episode_calculated = $this->calculateCurrentEpisode($plan, $testDate);

            $totalEpisodes = $plan->anime->episodes;

            $isFinishedByEpisodes = !empty($totalEpisodes) && $plan->current_episode_calculated >= $totalEpisodes;

            if ($isFinishedByEpisodes && $plan->watch_status !== 'concluido') {
                \App\Models\WatchPlan::where('id', $plan->id)->update([
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

        $perPage = 5;

        $activePage = request()->get('active_page', 1);
        $pausedPage = request()->get('paused_page', 1);
        $completedPage = request()->get('completed_page', 1);

        $activePlansPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $activePlans->forPage($activePage, $perPage)->values(),
            $activePlans->count(),
            $perPage,
            $activePage,
            [
                'path' => request()->url(),
                'pageName' => 'active_page',
            ]
        );

        $pausedPlansPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $pausedPlans->forPage($pausedPage, $perPage)->values(),
            $pausedPlans->count(),
            $perPage,
            $pausedPage,
            [
                'path' => request()->url(),
                'pageName' => 'paused_page',
            ]
        );

        $completedPlansPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $completedPlans->forPage($completedPage, $perPage)->values(),
            $completedPlans->count(),
            $perPage,
            $completedPage,
            [
                'path' => request()->url(),
                'pageName' => 'completed_page',
            ]
        );

        return view('admin.animes.index', [
            'plans' => $activePlansPaginated,
            'pausedPlans' => $pausedPlansPaginated,
            'completedPlans' => $completedPlansPaginated,
            'testDate' => $testDate,
        ]);
    }

    public function createLog(\App\Models\WatchPlan $plan)
    {
        return view('admin.animes.log', compact('plan'));
    }

    public function storeLog(\Illuminate\Http\Request $request, \App\Models\WatchPlan $plan)
    {
        $request->validate([
            'watched_date' => ['required', 'date'],
            'episodes_watched_today' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        \App\Models\WatchLog::updateOrCreate(
            [
                'watch_plan_id' => $plan->id,
                'watched_date' => $request->watched_date,
            ],
            [
                'episodes_watched_today' => $request->episodes_watched_today,
                'notes' => $request->notes,
            ]
        );

        return redirect()->route('admin.animes.index')->with('success', 'Registro salvo com sucesso.');
    }

    public function edit(\App\Models\WatchPlan $plan)
    {
        $plan->load('anime', 'days');

        return view('admin.animes.edit', compact('plan'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\WatchPlan $plan)
    {
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
            \App\Models\WatchPlanDay::create([
                'watch_plan_id' => $plan->id,
                'day_of_week' => $dayOfWeek,
                'episodes_planned' => $data['episodes_planned'],
                'is_variable' => $data['is_variable'],
            ]);
        }

        return redirect()->route('admin.animes.index')->with('success', 'Plano atualizado com sucesso.');
    }

    private function calculateCurrentEpisode(\App\Models\WatchPlan $plan, ?string $testDate = null): int
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


    public function destroy(\App\Models\WatchPlan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.animes.index')
            ->with('success', 'Anime removido com sucesso.');
    }

    public function complete(\App\Models\WatchPlan $plan)
    {
        $lastEpisode = $plan->anime->episodes;

        if ($lastEpisode !== null) {
            $plan->episodes_watched = $lastEpisode;
        }

        $plan->watch_status = 'concluido';
        $plan->save();

        return redirect()->route('admin.animes.index')
            ->with('success', 'Anime marcado como concluído!');
    }

    public function pause(\App\Models\WatchPlan $plan)
    {
        $plan->update([
            'watch_status' => 'pausado',
        ]);

        return redirect()->route('admin.animes.index')
            ->with('success', 'Anime pausado com sucesso!');
    }

    public function resume(\App\Models\WatchPlan $plan)
    {
        $plan->update([
            'watch_status' => 'assistindo',
        ]);

        return redirect()->route('admin.animes.index')
            ->with('success', 'Anime retomado!');
    }

}
