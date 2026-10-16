<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchLog;
use App\Models\WatchPlan;
use App\Services\Library\RankingManager;
use App\Services\Planning\PlanManager;
use App\Services\Planning\PlanProjection;
use Carbon\Carbon;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

function planningAnime(array $attributes = []): Anime
{
    return Anime::create(array_merge(['mal_id' => fake()->unique()->numberBetween(1, 2000000000), 'title' => 'Anime de teste', 'episodes' => 24], $attributes));
}

function planningPlan(?User $user, array $attributes = [], ?Anime $anime = null): WatchPlan
{
    $plan = WatchPlan::create(array_merge([
        'anime_id' => ($anime ?? planningAnime())->id, 'user_id' => $user?->id,
        'episodes_watched' => 0, 'episodes_per_day' => 1, 'start_date' => today()->subDays(3)->toDateString(), 'watch_status' => 'assistindo',
    ], $attributes));
    foreach (range(0, 6) as $day) {
        $plan->days()->create(['day_of_week' => $day, 'episodes_planned' => 1, 'is_variable' => false]);
    }

    return $plan;
}

function planningPayload(): array
{
    return array_merge(['start_date' => today()->toDateString(), 'episodes_watched' => 0], array_fill_keys(PlanManager::WEEK, 1));
}

test('maintenance is not exposed over HTTP', function () {
    $this->get('/arruma-tudo-hosting')->assertNotFound();
});

test('ordinary accounts cannot write global plans history or synopsis', function () {
    $user = User::factory()->create();
    $plan = planningPlan(null);
    $this->actingAs($user)->post(route('watch-plans.store', $plan->anime), planningPayload())->assertForbidden();
    $this->post(route('history.confirm'), ['plan_id' => $plan->id, 'date' => today()->toDateString(), 'action' => 'confirm', 'episodes' => 2])->assertForbidden();
    $this->patch(route('anime.synopsis.update', $plan->anime), ['synopsis' => 'alterado'])->assertForbidden();
    expect($plan->fresh()->episodes_watched)->toBe(0)->and($plan->logs()->count())->toBe(0);
});

test('admin global operations cannot modify a personal plan', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $personal = planningPlan(User::factory()->create());
    $this->actingAs($admin)->delete(route('admin.animes.destroy', $personal))->assertNotFound();
    $this->post(route('history.confirm'), ['plan_id' => $personal->id, 'date' => today()->toDateString(), 'action' => 'confirm', 'episodes' => 2])->assertNotFound();
    expect($personal->fresh())->not->toBeNull();
});

test('global creation does not overwrite an existing personal plan', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $personal = planningPlan(User::factory()->create());
    $this->actingAs($admin)->post(route('watch-plans.store', $personal->anime), planningPayload())->assertRedirect();
    expect(WatchPlan::global()->where('anime_id', $personal->anime_id)->count())->toBe(1)
        ->and($personal->fresh()->user_id)->toBe($personal->user_id)
        ->and(WatchPlan::count())->toBe(2);
});

test('personal mutation endpoints isolate owners', function () {
    $owner = User::factory()->create();
    $plan = planningPlan($owner);
    $this->actingAs(User::factory()->create());
    $this->get(route('personal.animes.edit', $plan))->assertNotFound();
    $this->put(route('personal.animes.update', $plan), planningPayload())->assertNotFound();
    $this->patch(route('personal.animes.pause', $plan))->assertNotFound();
    $this->patch(route('personal.animes.complete', $plan))->assertNotFound();
    $this->delete(route('personal.animes.destroy', $plan))->assertNotFound();
    $this->post(route('personal.history.confirm'), ['plan_id' => $plan->id, 'date' => today()->toDateString(), 'action' => 'confirm', 'episodes' => 1])->assertNotFound();
});

test('ordinary users can import for themselves but cannot import globally', function () {
    $this->actingAs(User::factory()->create());
    $payload = ['mal_id' => 1234, 'title' => 'Importado', 'target' => 'personal', 'episodes' => 12];
    $this->post(route('anime.store'), $payload)->assertRedirect(route('personal.watch-plans.create', Anime::first()));
    $this->post(route('anime.store'), array_merge($payload, ['mal_id' => 1235, 'target' => 'global']))->assertForbidden();
    expect(Anime::count())->toBe(1);
});

test('projection respects fixed days variable days and real zero logs', function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->startOfDay());
    $plan = planningPlan(User::factory()->create(), ['episodes_watched' => 2]);
    $plan->logs()->create(['watched_date' => today()->subDays(2)->toDateString(), 'episodes_watched_today' => 0]);
    $plan->days()->where('day_of_week', today()->subDay()->dayOfWeek)->update(['is_variable' => true]);
    expect(app(PlanProjection::class)->currentEpisode($plan))->toBe(3);
    $plan->logs()->create(['watched_date' => today()->toDateString(), 'episodes_watched_today' => 2]);
    expect(app(PlanProjection::class)->currentEpisode($plan->fresh()))->toBe(5);
});

test('pause freezes automatic progress and resume preserves the paused interval', function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->startOfDay());
    $plan = planningPlan(User::factory()->create());
    $manager = app(PlanManager::class);
    $manager->transition($plan, 'pausado');
    $this->travel(5)->days();
    expect($plan->fresh()->current_episode_calculated)->toBe(3);
    $manager->transition($plan, 'assistindo');
    $this->travel(2)->days();
    expect($plan->fresh()->current_episode_calculated)->toBe(5)
        ->and($plan->pauses()->count())->toBe(1);
});

test('manual completion is idempotent and includes existing logs today', function () {
    $plan = planningPlan(User::factory()->create());
    $plan->logs()->create(['watched_date' => today()->toDateString(), 'episodes_watched_today' => 2]);
    $manager = app(PlanManager::class);
    $manager->transition($plan, 'concluido');
    $count = $plan->logs()->sum('episodes_watched_today');
    $manager->transition($plan, 'concluido');
    expect($plan->fresh()->current_episode_calculated)->toBe(24)
        ->and($plan->logs()->sum('episodes_watched_today'))->toEqual($count)
        ->and((int) $plan->logs()->whereDate('watched_date', today())->value('episodes_watched_today'))->toBe(21);
});

test('unknown episode totals remain usable and completion stops projection', function () {
    $plan = planningPlan(User::factory()->create(), anime: planningAnime(['episodes' => null]));
    app(PlanManager::class)->transition($plan, 'concluido');
    $this->travel(5)->days();
    expect($plan->fresh()->current_episode_calculated)->toBe(3);
});

test('reading personal and admin pages never persists completion even with test date', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $plan = planningPlan($admin, ['episodes_watched' => 24]);
    planningPlan(null);
    $before = DB::table('watch_plans')->orderBy('id')->get()->toJson();
    $this->actingAs($admin)->get(route('personal.animes.index'))->assertOk();
    $this->get(route('admin.animes.index', ['test_date' => '2099-01-01']))->assertOk();
    expect(DB::table('watch_plans')->orderBy('id')->get()->toJson())->toBe($before);
});

test('copy respects private profiles and never overwrites an existing plan', function () {
    $owner = User::factory()->create(['is_public' => false]);
    $source = planningPlan($owner);
    $user = User::factory()->create();
    expect(Gate::forUser($user)->allows('copy', $source))->toBeFalse();
    $this->actingAs($user)->post(route('personal.watch-plans.follow', $source))->assertForbidden();
    $owner->update(['is_public' => true]);
    $existing = planningPlan($user, ['episodes_watched' => 8], $source->anime);
    $this->post(route('personal.watch-plans.follow', $source))->assertRedirect();
    expect($existing->fresh()->episodes_watched)->toBe(8);
});

test('deleting an account cascades personal plans instead of publishing them', function () {
    $user = User::factory()->create();
    planningPlan($user);
    $global = planningPlan(null);
    $user->delete();
    expect(WatchPlan::count())->toBe(1)->and(WatchPlan::first()->id)->toBe($global->id);
});

test('database rejects duplicate global plans and daily logs', function () {
    $plan = planningPlan(null);
    expect(fn () => WatchPlan::create($plan->only('anime_id', 'user_id', 'start_date', 'watch_status')))->toThrow(QueryException::class);
    $data = ['watched_date' => today()->toDateString(), 'episodes_watched_today' => 1];
    $plan->logs()->create($data);
    expect(fn () => $plan->logs()->create($data))->toThrow(QueryException::class);
    expect(fn () => $plan->days()->create(['day_of_week' => 0, 'episodes_planned' => 1]))->toThrow(QueryException::class);
    $meta = ['user_id' => User::factory()->create()->id, 'anime_id' => $plan->anime_id];
    UserAnimeMeta::create($meta);
    expect(fn () => UserAnimeMeta::create($meta))->toThrow(QueryException::class);
});

test('ranking operations keep contiguous unique positions with a maximum of ten', function () {
    $user = User::factory()->create();
    $ranking = app(RankingManager::class);
    $animes = collect(range(1, 12))->map(fn () => planningAnime());
    foreach ($animes as $anime) {
        $ranking->change($user, $anime->id, 'add');
    }
    $ranking->change($user, $animes->last()->id, 'down');
    $ranking->change($user, $animes->last()->id, 'remove');
    $positions = UserAnimeMeta::where('user_id', $user->id)->whereNotNull('top_position')->orderBy('top_position')->pluck('top_position')->all();
    expect($positions)->toBe(range(1, 9));
});

test('logs reject future dates and dates before the plan begins', function () {
    $user = User::factory()->create();
    $plan = planningPlan($user);
    $this->actingAs($user)->post(route('personal.animes.log.store', $plan), ['watched_date' => today()->addDay()->toDateString(), 'episodes_watched_today' => 1])->assertSessionHasErrors('watched_date');
    $this->post(route('personal.animes.log.store', $plan), ['watched_date' => today()->subDays(10)->toDateString(), 'episodes_watched_today' => 1])->assertSessionHasErrors('watched_date');
});

test('planning screens render for both scopes and validation preserves the database', function () {
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $personal = planningPlan($user);
    $global = planningPlan(null);
    $this->actingAs($user);
    foreach (['personal.watch-plans.create', 'watch-plans.create'] as $route) {
        $this->get(route($route, $global->anime))->assertOk();
    }
    foreach (['personal.animes.edit', 'personal.animes.log.create'] as $route) {
        $this->get(route($route, $personal))->assertOk();
    }
    foreach (['admin.animes.edit', 'admin.animes.log.create'] as $route) {
        $this->get(route($route, $global))->assertOk();
    }
    foreach (['personal.calendar', 'calendar', 'personal.history', 'history', 'personal.completed', 'completed.animes'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->put(route('personal.animes.update', $personal), array_merge(planningPayload(), ['watch_status' => 'invalido']))->assertSessionHasErrors('watch_status');
    expect($personal->fresh()->watch_status)->toBe('assistindo');
});

test('windowed calendar reconstruction agrees with progress and ignores paused forecasts', function () {
    $plan = planningPlan(User::factory()->create(), ['start_date' => today()->subDays(1000)->toDateString()], planningAnime(['episodes' => null]));
    $plan->pauses()->create(['started_on' => today()->subDays(10), 'resumed_on' => today()->subDays(5)]);
    $plan->logs()->create(['watched_date' => today()->subDay()->toDateString(), 'episodes_watched_today' => 0]);
    $projection = app(PlanProjection::class);
    $entries = $projection->entries($plan, today()->addDays(2), today());
    expect($projection->currentEpisode($plan))->toBe(994)
        ->and($entries)->toHaveCount(3)
        ->and($entries[0]['label'])->toBe('Episódios: 995 ao 995');
});

test('review creation requires a completed personal plan even through the global route', function () {
    $user = User::factory()->create();
    $plan = planningPlan($user);
    $this->actingAs($user)->post(route('completed.review.store', $plan->anime), ['rating' => 8])->assertForbidden();
    app(PlanManager::class)->transition($plan, 'concluido');
    $this->post(route('completed.review.store', $plan->anime), ['rating' => 8])->assertRedirect();
    expect(UserAnimeMeta::where('user_id', $user->id)->value('rating'))->toBe(8);
});

test('demo seeding is repeatable and preserves the original credentials', function () {
    Storage::fake('public');
    $original = User::factory()->create();
    $password = $original->password;
    $seeder = new DemoSeeder;
    $seeder->run();
    $counts = fn () => [User::count(), Anime::count(), WatchPlan::count(), UserAnimeMeta::count(), WatchLog::count()];
    $before = $counts();
    $seeder->run();
    expect($counts())->toBe($before)->and($original->fresh()->password)->toBe($password)
        ->and($original->fresh()->role)->toBe('admin');
    expect(WatchPlan::where('watch_status', 'pausado')->whereDoesntHave('pauses')->count())->toBe(0);
});

test('arithmetic projection matches daily rules across weeks pauses and overrides', function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->startOfDay());
    $plan = planningPlan(User::factory()->create(), ['start_date' => today()->subDays(80)->toDateString(), 'episodes_watched' => 5], planningAnime(['episodes' => null]));
    $plan->days()->whereIn('day_of_week', [0, 6])->update(['is_variable' => true]);
    $plan->days()->where('day_of_week', 2)->update(['episodes_planned' => 3]);
    $plan->pauses()->create(['started_on' => today()->subDays(40), 'resumed_on' => today()->subDays(33)]);
    $plan->pauses()->create(['started_on' => today()->subDays(11), 'resumed_on' => today()->subDays(7)]);
    foreach ([0 => 2, 3 => 0, 10 => 4, 35 => 2, 50 => 3] as $days => $count) {
        $plan->logs()->create(['watched_date' => today()->subDays($days)->toDateString(), 'episodes_watched_today' => $count]);
    }
    $plan->load('anime', 'days', 'logs', 'pauses');
    foreach ([70, 35, 10, 0] as $offset) {
        $at = today()->subDays($offset);
        $expected = 5;
        for ($date = Carbon::parse($plan->start_date); $date->lte($at); $date->addDay()) {
            $log = $plan->logs->firstWhere('watched_date', $date->toDateString());
            $paused = $plan->pauses->contains(fn ($pause) => $date->gte($pause->started_on) && $date->lt($pause->resumed_on));
            $day = $plan->days->firstWhere('day_of_week', $date->dayOfWeek);
            if ($log) {
                $expected += $log->episodes_watched_today;
            } elseif ($date->lt($at) && ! $paused && ! $day->is_variable) {
                $expected += $day->episodes_planned;
            }
        }
        expect(app(PlanProjection::class)->currentEpisode($plan, $at))->toBe($expected);
    }
});

test('personal pagination has bounded query count as more plans are added', function () {
    $user = User::factory()->create();
    foreach (range(1, 20) as $i) {
        planningPlan($user);
    }
    DB::enableQueryLog();
    DB::flushQueryLog();
    $response = $this->actingAs($user)->get(route('personal.animes.index'));
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();
    $response->assertOk()->assertViewHas('plans', fn ($page) => $page->count() === 12 && $page->total() === 20);
    expect($queries)->toBeLessThan(25);
});

test('completed profile and community screens respect review visibility', function () {
    $owner = User::factory()->create(['show_reviews_public' => false]);
    $personal = planningPlan($owner, ['watch_status' => 'concluido']);
    planningPlan(null, ['watch_status' => 'concluido'], $personal->anime);
    UserAnimeMeta::create(['user_id' => $owner->id, 'anime_id' => $personal->anime_id,
        'rating' => 10, 'comment' => 'REVIEW_OCULTA', 'top_position' => 1, 'is_favorite' => true]);
    $this->actingAs(User::factory()->create())->get(route('profile.show', $owner->username))->assertOk()->assertDontSee('REVIEW_OCULTA');
    $this->get(route('completed.animes'))->assertOk()->assertDontSee('REVIEW_OCULTA');
    $this->actingAs($owner)->get(route('personal.completed'))->assertOk()->assertSee('REVIEW_OCULTA');
});

test('manual completion before the scheduled start records a valid actual start', function () {
    $plan = planningPlan(User::factory()->create(), ['start_date' => today()->addDays(2)->toDateString()]);
    app(PlanManager::class)->transition($plan, 'concluido');
    expect($plan->fresh()->start_date)->toBe(today()->toDateString())
        ->and($plan->fresh()->current_episode_calculated)->toBe(24);
});
