<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmHistoryRequest;
use App\Http\Requests\ListingFiltersRequest;
use App\Http\Requests\SaveWatchPlanRequest;
use App\Http\Requests\WatchLogRequest;
use App\Models\Anime;
use App\Models\WatchPlan;
use App\Queries\WatchPlanQuery;
use App\Services\Planning\PlanManager;
use App\Services\Planning\PlanProjection;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

abstract class PlanController extends Controller
{
    protected bool $personal = false;

    public function __construct(protected PlanManager $manager, protected PlanProjection $projection, protected WatchPlanQuery $plans) {}

    protected function ownerId(): ?int
    {
        return $this->personal ? auth()->id() : null;
    }

    protected function scoped(WatchPlan $plan): WatchPlan
    {
        abort_unless($plan->user_id === $this->ownerId(), 404);
        Gate::authorize('update', $plan);

        return $plan;
    }

    protected function indexRoute(): string
    {
        return $this->personal ? 'personal.animes.index' : 'admin.animes.index';
    }

    public function index(ListingFiltersRequest $request)
    {
        $pages = $this->plans->statusPages($this->ownerId(), filters: $request->validated());

        return view($this->personal ? 'personal.index' : 'admin.animes.index', [
            'plans' => $pages['active'], 'activePlans' => $pages['active'],
            'pausedPlans' => $pages['paused'], 'completedPlans' => $pages['completed'], 'testDate' => null,
            'plannedPlans' => $pages['planned'],
        ]);
    }

    public function create(Anime $anime)
    {
        abort_unless($this->personal || auth()->user()->isAdmin(), 403);

        $startingPlan = $this->plans->forOwner($this->ownerId())->where('anime_id', $anime->id)
            ->where('watch_status', 'pretendo_assistir')->with('days')->first();

        return view($this->personal ? 'personal.create' : 'watch-plans.create', compact('anime', 'startingPlan'));
    }

    public function store(SaveWatchPlanRequest $request, Anime $anime)
    {
        abort_unless($this->personal || $request->user()->isAdmin(), 403);
        $this->manager->save($anime, $this->ownerId(), $request->validated());

        return redirect()->route($this->indexRoute())->with('success', 'Plano salvo com sucesso.');
    }

    public function edit(WatchPlan $plan)
    {
        $this->scoped($plan)->load('anime', 'days');

        return view($this->personal ? 'personal.edit' : 'admin.animes.edit', compact('plan'));
    }

    public function update(SaveWatchPlanRequest $request, WatchPlan $plan)
    {
        $this->scoped($plan);
        $this->manager->save($plan->anime, $this->ownerId(), $request->validated(), $plan);

        return redirect()->route($this->indexRoute())->with('success', 'Plano atualizado com sucesso.');
    }

    public function createLog(WatchPlan $plan)
    {
        $this->scoped($plan)->load('anime');

        return view($this->personal ? 'personal.log' : 'admin.animes.log', compact('plan'));
    }

    public function storeLog(WatchLogRequest $request, WatchPlan $plan)
    {
        $this->manager->log($this->scoped($plan), $request->validated());

        return redirect()->route($this->indexRoute())->with('success', 'Registro salvo com sucesso.');
    }

    public function pause(WatchPlan $plan)
    {
        return $this->transition($plan, 'pausado');
    }

    public function resume(WatchPlan $plan)
    {
        return $this->transition($plan, 'assistindo');
    }

    public function complete(WatchPlan $plan)
    {
        return $this->transition($plan, 'concluido');
    }

    private function transition(WatchPlan $plan, string $status)
    {
        $this->manager->transition($this->scoped($plan), $status);

        return redirect()->route($this->indexRoute())->with('success', 'Status atualizado com sucesso.');
    }

    public function destroy(WatchPlan $plan)
    {
        $this->scoped($plan)->delete();

        return redirect()->route($this->indexRoute())->with('success', 'Plano removido. Avaliações e favoritos foram preservados.');
    }

    public function calendar(Request $request)
    {
        $data = $request->validate(['test_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1970-01-01', 'before_or_equal:2100-12-31']]);
        $at = isset($data['test_date']) ? CarbonImmutable::parse($data['test_date']) : null;
        $plans = $this->plans->loaded($this->ownerId())->where('watch_status', 'assistindo')->get();
        $calendarData = $this->projection->grouped($plans, at: $at);

        return view($this->personal ? 'personal.calendar' : 'calendar.index', compact('calendarData'));
    }

    public function history(Request $request)
    {
        $range = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1970-01-01', 'before_or_equal:today'],
            'until' => ['nullable', 'date_format:Y-m-d', 'before:today'],
        ]);
        $from = CarbonImmutable::parse($range['from'] ?? today()->subDays(90)->toDateString());
        $until = CarbonImmutable::parse($range['until'] ?? today()->subDay()->toDateString());
        if ($until->lt($from) || $from->diffInDays($until) > 366) {
            throw ValidationException::withMessages(['from' => 'Escolha um período de até 366 dias, com início anterior ao fim.']);
        }
        $plans = $this->plans->loaded($this->ownerId())->get();
        $historyData = $this->projection->grouped($plans, history: true, at: $until->addDay(), from: $from);

        return view($this->personal ? 'personal.history' : 'history.index', compact('historyData'));
    }

    public function confirm(ConfirmHistoryRequest $request)
    {
        $plan = $this->plans->forOwner($this->ownerId())->findOrFail($request->integer('plan_id'));
        $this->scoped($plan);
        $this->manager->log($plan, [
            'watched_date' => $request->validated('date'),
            'episodes_watched_today' => $request->input('action') === 'missed' ? 0 : $request->integer('episodes'),
            'notes' => 'Registro confirmado pelo histórico.',
        ]);

        return redirect()->route($this->personal ? 'personal.history' : 'history')->with('success', 'Histórico atualizado.');
    }

    public function completed(ListingFiltersRequest $request)
    {
        $plans = $this->plans->filtered($this->ownerId(), $request->validated())->with(['anime' => fn ($q) => $q
            ->with('latestPublicReview.user')
            ->with(['userMeta' => fn ($meta) => $meta->where('user_id', auth()->id())])
            ->withAvg(['userMeta as public_rating' => fn ($meta) => $meta->publiclyVisible()], 'rating')])
            ->where('watch_status', 'concluido')->paginate(12)->withQueryString();
        $myCompletedAnimeIds = WatchPlan::ownedBy(auth()->id())->where('watch_status', 'concluido')->pluck('anime_id')->all();

        return view($this->personal ? 'personal.completed' : 'completed.index', compact('plans', 'myCompletedAnimeIds'));
    }

    public function followFromGlobal(WatchPlan $plan)
    {
        Gate::authorize('copy', $plan);
        $this->manager->copy($plan, auth()->user());

        return redirect()->route('personal.animes.index')->with('success', 'Plano disponível na sua biblioteca.');
    }
}
