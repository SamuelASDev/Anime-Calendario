<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\WatchPlan;
use App\Services\Planning\PlanManager;
use App\Services\Planning\PlanProjection;
use Illuminate\Support\Facades\Http;

function laterAnime(): Anime
{
    return Anime::create(['mal_id' => fake()->unique()->numberBetween(1, 2000000000), 'title' => 'Minha próxima aventura', 'episodes' => 12]);
}

test('saving for later creates a single personal plan without affecting global data', function () {
    $user = User::factory()->create();
    $anime = laterAnime();
    $global = WatchPlan::create(['anime_id' => $anime->id, 'user_id' => null, 'start_date' => today()->toDateString(), 'watch_status' => 'assistindo']);
    $this->actingAs($user)->from(route('anime.index.all'))->post(route('personal.watch-later.store', $anime))->assertRedirect(route('anime.index.all'));
    $this->post(route('personal.watch-later.store', $anime))->assertRedirect();
    $plan = WatchPlan::ownedBy($user->id)->firstOrFail();
    expect($plan->watch_status)->toBe('pretendo_assistir')->and($plan->episodes_watched)->toBe(0)
        ->and($plan->days()->count())->toBe(0)->and($plan->logs()->count())->toBe(0)
        ->and(WatchPlan::ownedBy($user->id)->count())->toBe(1)->and($global->fresh()->watch_status)->toBe('assistindo');
});

test('watch later never replaces watching paused or completed statuses', function () {
    $user = User::factory()->create();
    foreach (['assistindo', 'pausado', 'concluido'] as $status) {
        $plan = WatchPlan::create(['user_id' => $user->id, 'anime_id' => laterAnime()->id,
            'start_date' => today()->subDays(2)->toDateString(), 'episodes_watched' => 4, 'watch_status' => $status]);
        $this->actingAs($user)->post(route('personal.watch-later.store', $plan->anime))->assertRedirect();
        expect($plan->fresh()->watch_status)->toBe($status)->and($plan->fresh()->episodes_watched)->toBe(4);
    }
});

test('time on the watch later list never advances progress or appears in calendar and history', function () {
    $user = User::factory()->create();
    $plan = app(PlanManager::class)->saveForLater(laterAnime(), $user);
    $plan->days()->create(['day_of_week' => today()->dayOfWeek, 'episodes_planned' => 5]);
    $this->travel(30)->days();
    expect($plan->fresh()->current_episode_calculated)->toBe(0)
        ->and(app(PlanProjection::class)->entries($plan->fresh(), today()->addMonth()))->toBe([]);
    $this->actingAs($user)->get(route('personal.calendar'))->assertOk()->assertDontSee($plan->anime->title);
    $this->get(route('personal.history'))->assertOk()->assertDontSee($plan->anime->title);
    $this->post(route('personal.animes.log.store', $plan), ['watched_date' => today()->toDateString(), 'episodes_watched_today' => 2])->assertSessionHasErrors('watched_date');
});

test('starting a saved anime starts today with a default schedule and remains idempotent', function () {
    $user = User::factory()->create();
    $plan = app(PlanManager::class)->saveForLater(laterAnime(), $user);
    $this->travel(20)->days();
    $this->actingAs($user)->patch(route('personal.animes.resume', $plan))->assertRedirect();
    expect($plan->fresh()->watch_status)->toBe('assistindo')->and($plan->fresh()->start_date)->toBe(today()->toDateString())
        ->and($plan->fresh()->current_episode_calculated)->toBe(0)->and($plan->days()->count())->toBe(7);
    $this->travel(2)->days();
    $this->patch(route('personal.animes.resume', $plan))->assertRedirect();
    expect($plan->fresh()->current_episode_calculated)->toBe(2);
});

test('watch later pages are private and another user cannot start or delete a saved plan', function () {
    $owner = User::factory()->create();
    $plan = app(PlanManager::class)->saveForLater(laterAnime(), $owner);
    $this->actingAs($owner)->get(route('personal.watch-later.index'))->assertOk()->assertSee($plan->anime->title)->assertSee('Começar a assistir');
    $this->get(route('personal.animes.index'))->assertOk()->assertSee($plan->anime->title);
    $this->get(route('profile.show', $owner->username))->assertOk()->assertSee($plan->anime->title);
    $this->actingAs(User::factory()->create())->get(route('personal.watch-later.index'))->assertOk()->assertDontSee($plan->anime->title);
    $this->get(route('profile.show', $owner->username))->assertOk()->assertDontSee($plan->anime->title);
    $this->patch(route('personal.animes.resume', $plan))->assertNotFound();
    $this->delete(route('personal.animes.destroy', $plan))->assertNotFound();
});

test('catalog and details show the saved status and search offers direct save for later', function () {
    $user = User::factory()->create();
    $anime = laterAnime();
    $this->actingAs($user)->get(route('anime.show', $anime))->assertOk()->assertSee(route('personal.watch-later.store', $anime));
    app(PlanManager::class)->saveForLater($anime, $user);
    $this->get(route('anime.index.all'))->assertOk()->assertSee('✓ Pretendo assistir')->assertDontSee('>Assistindo<', false);
    $this->get(route('anime.show', $anime))->assertOk()->assertSee('✓ Pretendo assistir');
    Http::preventStrayRequests();
    Http::fake(['api.jikan.moe/*' => Http::response(['data' => [['mal_id' => 1234, 'title' => 'Futuro', 'images' => ['jpg' => ['image_url' => 'https://example.test/cover.jpg']]]]])]);
    $this->get(route('anime.search', ['q' => 'futuro']))->assertOk()->assertSee('name="target" value="later"', false);
});

test('search import can save directly for later without a scheduling form', function () {
    $this->actingAs(User::factory()->create())->post(route('anime.store'), ['mal_id' => 999, 'title' => 'Para depois', 'episodes' => 24, 'target' => 'later'])
        ->assertRedirect(route('personal.watch-later.index'));
    expect(WatchPlan::first()->watch_status)->toBe('pretendo_assistir');
    $plan = WatchPlan::first();
    app(PlanManager::class)->transition($plan, 'assistindo');
    $this->post(route('anime.store'), ['mal_id' => 999, 'title' => 'Para depois', 'episodes' => 24, 'target' => 'later'])
        ->assertRedirect(route('personal.animes.index'));
    expect($plan->fresh()->watch_status)->toBe('assistindo')->and(WatchPlan::count())->toBe(1);
});

test('watch later accepts the edit status but refuses to discard recorded progress', function () {
    $user = User::factory()->create();
    $plan = app(PlanManager::class)->saveForLater(laterAnime(), $user);
    $payload = array_merge(['start_date' => today()->toDateString(), 'episodes_watched' => 0, 'watch_status' => 'pretendo_assistir'], array_fill_keys(PlanManager::WEEK, 0));
    $this->actingAs($user)->put(route('personal.animes.update', $plan), $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->put(route('personal.animes.update', $plan), array_merge($payload, ['episodes_watched' => 3]))->assertSessionHasErrors('watch_status');
    expect($plan->fresh()->watch_status)->toBe('pretendo_assistir')->and($plan->fresh()->episodes_watched)->toBe(0);
});

test('a saved anime can be removed without affecting another users list', function () {
    $user = User::factory()->create();
    $anime = laterAnime();
    $own = app(PlanManager::class)->saveForLater($anime, $user);
    $other = app(PlanManager::class)->saveForLater($anime, User::factory()->create());
    $this->actingAs($user)->delete(route('personal.animes.destroy', $own))->assertRedirect();
    expect($own->fresh())->toBeNull()->and($other->fresh())->not->toBeNull();
});

test('saved anime uses a configured schedule when started and can be completed without retroactive progress', function () {
    $user = User::factory()->create();
    $manager = app(PlanManager::class);
    $plan = $manager->saveForLater(laterAnime(), $user);
    foreach (range(0, 6) as $day) {
        $plan->days()->create(['day_of_week' => $day, 'episodes_planned' => 2]);
    }
    $this->travel(20)->days();
    $manager->transition($plan, 'assistindo');
    $this->travel(1)->days();
    expect($plan->fresh()->current_episode_calculated)->toBe(2)->and($plan->days()->count())->toBe(7);
    $saved = $manager->saveForLater(laterAnime(), $user);
    $this->travel(20)->days();
    $this->actingAs($user)->post(route('completed.mark-watched', $saved->anime))->assertRedirect();
    expect($saved->fresh()->current_episode_calculated)->toBe(12)->and($saved->fresh()->start_date)->toBe(today()->toDateString())
        ->and($saved->logs()->count())->toBe(1)->and($saved->logs()->value('watched_date'))->toBe(today()->toDateString());
});

test('watch later requires authentication', function () {
    $anime = laterAnime();
    $this->get(route('personal.watch-later.index'))->assertRedirect(route('login'));
    $this->post(route('personal.watch-later.store', $anime))->assertRedirect(route('login'));
    expect(WatchPlan::count())->toBe(0);
});

test('starting from the watch later card opens planning without changing status until a valid save', function () {
    $user = User::factory()->create();
    $plan = app(PlanManager::class)->saveForLater(laterAnime(), $user);
    $plan->days()->create(['day_of_week' => 1, 'episodes_planned' => 3]);
    $formUrl = route('personal.watch-plans.create', $plan->anime);
    $this->actingAs($user)->get(route('personal.watch-later.index'))->assertOk()
        ->assertSee('href="'.$formUrl.'"', false)
        ->assertDontSee('action="'.route('personal.animes.resume', $plan).'"', false);
    $this->get($formUrl)->assertOk()->assertSee('Agenda semanal')
        ->assertViewHas('startingPlan', fn ($draft) => $draft->id === $plan->id && $draft->days->first()->episodes_planned === 3);
    expect($plan->fresh()->watch_status)->toBe('pretendo_assistir');
    $this->post(route('personal.watch-plans.store', $plan->anime), ['start_date' => 'invalido'])->assertSessionHasErrors('start_date');
    expect($plan->fresh()->watch_status)->toBe('pretendo_assistir');
    $start = today()->addDays(5)->toDateString();
    $payload = array_merge(['start_date' => $start, 'episodes_watched' => 0], array_fill_keys(PlanManager::WEEK, 2));
    $this->post(route('personal.watch-plans.store', $plan->anime), $payload)->assertSessionHasNoErrors()->assertRedirect();
    expect($plan->fresh()->watch_status)->toBe('assistindo')->and($plan->fresh()->start_date)->toBe($start)
        ->and($plan->days()->count())->toBe(7)->and($plan->days()->sum('episodes_planned'))->toEqual(14)
        ->and(WatchPlan::ownedBy($user->id)->count())->toBe(1);
});
