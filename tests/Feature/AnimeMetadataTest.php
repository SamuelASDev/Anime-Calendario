<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\WatchPlan;
use App\Services\AnimeSearchService;
use App\Services\Catalog\AnimeMetadataSync;
use App\Services\Planning\PlanManager;
use Illuminate\Support\Facades\Http;

function tenraiItem(array $overrides = []): array
{
    return array_merge([
        'mal_id' => 16498, 'title' => 'Shingeki no Kyojin', 'title_english' => 'Attack on Titan', 'title_japanese' => '進撃の巨人',
        'title_synonyms' => ['AoT', 'SnK'],
        'titles' => [['type' => 'Default', 'title' => 'Shingeki no Kyojin'], ['type' => 'English', 'title' => 'Attack on Titan'], ['type' => 'German', 'title' => 'Attack on Titan'], ['type' => 'Spanish', 'title' => 'Ataque a los Titanes']],
        'images' => ['jpg' => ['image_url' => 'https://example.test/new.jpg']],
        'episodes' => 25, 'status' => 'Finished Airing', 'synopsis' => 'Sinopse da API', 'type' => 'TV', 'source' => 'Manga',
        'aired' => ['from' => '2013-04-07T00:00:00+00:00', 'to' => '2013-09-29T00:00:00+00:00', 'prop' => ['from' => ['day' => 7, 'month' => 4, 'year' => 2013]]],
        'season' => 'spring', 'year' => 2013, 'duration' => '24 min per ep', 'rating' => 'R - 17+ (violence & profanity)',
        'studios' => [['name' => 'Wit Studio']], 'genres' => [['name' => 'Action']],
    ], $overrides);
}

function metadataAdmin(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    return $admin;
}

function metadataAnime(array $attributes = []): Anime
{
    return Anime::create(array_merge(['mal_id' => fake()->unique()->numberBetween(1, 2000000000), 'title' => 'Antigo', 'episodes' => 12], $attributes));
}

test('sync stores new metadata and never replaces curated fields or plans', function () {
    Http::fake(['api.tenrai.org/*' => Http::response(['data' => tenraiItem()])]);
    $anime = metadataAnime(['mal_id' => 16498, 'title' => 'Ataque dos Titãs', 'episodes' => 99, 'synopsis' => 'Curada', 'synopsis_pt' => 'Em português', 'image' => 'https://example.test/old.jpg', 'genres' => 'Drama', 'anime_status' => 'Currently Airing']);
    $plan = WatchPlan::create(['user_id' => User::factory()->create()->id, 'anime_id' => $anime->id, 'watch_status' => 'assistindo', 'start_date' => today()->toDateString(), 'episodes_watched' => 4]);
    $before = $plan->fresh()->toArray();

    expect(app(AnimeMetadataSync::class)->next())->toMatchArray(['state' => 'synced', 'anime' => 'Ataque dos Titãs']);

    $anime->refresh();
    expect($anime->only('title', 'episodes', 'synopsis', 'synopsis_pt', 'image', 'genres', 'anime_status'))->toBe(['title' => 'Ataque dos Titãs', 'episodes' => 99, 'synopsis' => 'Curada', 'synopsis_pt' => 'Em português', 'image' => 'https://example.test/old.jpg', 'genres' => 'Drama', 'anime_status' => 'Currently Airing'])
        ->and($anime->aired_from->toDateString())->toBe('2013-04-07')
        ->and($anime->airedLabel())->toBe('07/04/2013 a 29/09/2013')
        ->and($anime->seasonLabel())->toBe('Primavera de 2013')
        ->and($anime->studios)->toBe('Wit Studio')
        ->and($anime->metadata_synced_at)->not->toBeNull()
        ->and($plan->fresh()->toArray())->toBe($before);
    // Includes the original main title, since the local one differs, and removes repeated names.
    expect($anime->titles()->pluck('title')->all())->toEqualCanonicalizing(['Shingeki no Kyojin', 'Attack on Titan', 'Ataque a los Titanes', '進撃の巨人', 'AoT', 'SnK']);
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/anime/16498'));
});

test('sync fills only blank legacy fields', function () {
    Http::fake(['api.tenrai.org/*' => Http::response(['data' => tenraiItem()])]);
    $anime = metadataAnime(['mal_id' => 16498, 'episodes' => null]);
    app(AnimeMetadataSync::class)->next();
    expect($anime->fresh()->only('episodes', 'synopsis', 'image', 'genres', 'anime_status'))
        ->toBe(['episodes' => 25, 'synopsis' => 'Sinopse da API', 'image' => 'https://example.test/new.jpg', 'genres' => 'Action', 'anime_status' => 'Finished Airing']);
});

test('partial and missing release dates are stored without inventing precision', function () {
    Http::fakeSequence('api.tenrai.org/*')
        ->push(['data' => tenraiItem(['aired' => ['from' => null, 'to' => null, 'prop' => ['from' => ['day' => null, 'month' => null, 'year' => 2027]]]])])
        ->push(['data' => tenraiItem(['aired' => ['from' => null, 'to' => null], 'year' => null, 'season' => null])]);
    $partial = metadataAnime(['mal_id' => 1]);
    $missing = metadataAnime(['mal_id' => 2]);
    $sync = app(AnimeMetadataSync::class);
    $sync->next();
    $this->travel(5)->seconds();
    $sync->next();
    expect($partial->fresh()->airedLabel())->toBe('2027')
        ->and($missing->fresh()->aired_from)->toBeNull()
        ->and($missing->fresh()->airedLabel())->toBeNull()
        ->and($missing->fresh()->metadata_synced_at)->not->toBeNull();
});

test('consecutive calls respect the interval and send a single request', function () {
    Http::fake(['api.tenrai.org/*' => Http::response(['data' => tenraiItem()])]);
    metadataAnime(['mal_id' => 1]);
    metadataAnime(['mal_id' => 2]);
    $sync = app(AnimeMetadataSync::class);
    expect($sync->next()['state'])->toBe('synced')->and($sync->next()['state'])->toBe('wait');
    Http::assertSentCount(1);
    $this->travel(3)->seconds();
    expect($sync->next()['state'])->toBe('synced')->and($sync->next()['state'])->toBe('done');
    Http::assertSentCount(2);
});

test('rate limit honors Retry-After, keeps the anime pending and stops all requests during the pause', function () {
    Http::fake(['api.tenrai.org/*' => Http::response([], 429, ['Retry-After' => '120'])]);
    $anime = metadataAnime();
    $sync = app(AnimeMetadataSync::class);
    expect($sync->next())->toMatchArray(['state' => 'paused', 'retry_after' => 120]);
    $this->travel(10)->seconds();
    expect($sync->next()['state'])->toBe('paused');
    Http::assertSentCount(1);
    expect($anime->fresh()->metadata_sync_error)->toBeNull()->and($sync->status()['pending'])->toBe(1);
});

test('a rate limit hit by a user search also pauses the background sync', function () {
    Http::fake(['api.tenrai.org/*' => Http::response([], 429, ['Retry-After' => '60'])]);
    metadataAnime();
    expect(fn () => app(AnimeSearchService::class)->search('qualquer'))->toThrow(RuntimeException::class);
    expect(app(AnimeMetadataSync::class)->next()['state'])->toBe('paused');
    Http::assertSentCount(1);
});

test('unknown animes fail permanently without blocking the queue and can be retried', function () {
    Http::fakeSequence('api.tenrai.org/*')->push([], 404)->push(['data' => tenraiItem()])->push(['data' => tenraiItem()]);
    $missing = metadataAnime(['mal_id' => 1]);
    $next = metadataAnime(['mal_id' => 2]);
    $sync = app(AnimeMetadataSync::class);
    expect($sync->next()['state'])->toBe('failed')->and($missing->fresh()->metadata_sync_error)->toBe('not_found');
    $this->travel(3)->seconds();
    expect($sync->next()['state'])->toBe('synced')->and($next->fresh()->metadata_synced_at)->not->toBeNull();
    expect($sync->status())->toMatchArray(['total' => 2, 'synced' => 1, 'pending' => 0, 'failed' => 1]);
    expect($sync->retryFailed())->toBe(1)->and($sync->status()['pending'])->toBe(1);
});

test('temporary API failures keep the anime pending', function () {
    Http::fake(['api.tenrai.org/*' => Http::response([], 503)]);
    $anime = metadataAnime();
    expect(app(AnimeMetadataSync::class)->next())->toMatchArray(['state' => 'unavailable', 'retry_after' => 30]);
    expect($anime->fresh()->metadata_sync_error)->toBeNull()->and($anime->fresh()->metadata_synced_at)->toBeNull();
});

test('only admins reach the metadata tools', function () {
    Http::fake();
    $this->post(route('admin.animes.metadata.sync'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create());
    $this->get(route('admin.animes.metadata'))->assertForbidden();
    $this->post(route('admin.animes.metadata.sync'))->assertForbidden();
    $this->post(route('admin.animes.metadata.retry'))->assertForbidden();
    Http::assertNothingSent();
});

test('admin page shows progress and the endpoint reports state as JSON', function () {
    Http::fake(['api.tenrai.org/*' => Http::response(['data' => tenraiItem()])]);
    metadataAnime(['title' => 'Falhou', 'metadata_sync_error' => 'not_found']);
    metadataAnime();
    $this->actingAs(metadataAdmin());
    $this->get(route('admin.animes.index'))->assertOk()->assertSee('Completar dados');
    $this->get(route('admin.animes.metadata'))->assertOk()->assertSee('data-metadata-sync', false)->assertSee('Falhou')->assertSee('Não encontrado na API');
    $this->postJson(route('admin.animes.metadata.sync'))->assertOk()->assertJson(['state' => 'synced', 'status' => ['total' => 2, 'synced' => 1, 'pending' => 0, 'failed' => 1]]);
    $this->post(route('admin.animes.metadata.retry'))->assertRedirect(route('admin.animes.metadata'));
    expect(Anime::whereNotNull('metadata_sync_error')->count())->toBe(0);
});

test('import reuses the server search result without another request and ignores form metadata', function () {
    Http::fake(['api.tenrai.org/*' => Http::response(['data' => [tenraiItem()]])]);
    $this->actingAs(User::factory()->create());
    $this->get(route('anime.search', ['q' => 'titan']))->assertOk();
    $this->post(route('anime.store'), ['mal_id' => 16498, 'title' => 'Shingeki no Kyojin', 'target' => 'later', 'aired_from' => '1900-01-01'])->assertRedirect();
    Http::assertSentCount(1);
    $anime = Anime::firstWhere('mal_id', 16498);
    expect($anime->aired_from->toDateString())->toBe('2013-04-07')->and($anime->metadata_synced_at)->not->toBeNull()->and($anime->titles()->count())->toBeGreaterThan(0);
});

test('import without a recent search stays queued and makes no request', function () {
    Http::fake();
    $this->actingAs(User::factory()->create());
    $this->post(route('anime.store'), ['mal_id' => 7, 'title' => 'Sem busca', 'target' => 'later'])->assertRedirect();
    Http::assertNothingSent();
    expect(app(AnimeMetadataSync::class)->status()['pending'])->toBe(1);
});

test('text filter finds alternative names on every listing and keeps other filters combined', function () {
    $user = User::factory()->create();
    $anime = metadataAnime(['title' => 'Shingeki no Kyojin', 'genres' => 'Action']);
    $anime->titles()->createMany([['type' => 'Synonym', 'title' => 'SnK'], ['type' => 'Japanese', 'title' => '進撃の巨人']]);
    metadataAnime(['title' => 'Outro anime', 'genres' => 'Action']);
    WatchPlan::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'watch_status' => 'pretendo_assistir', 'start_date' => today()->toDateString(), 'episodes_watched' => 0]);
    $this->actingAs($user);
    foreach (['SnK', '進撃', 'Shingeki'] as $term) {
        $this->get(route('anime.index.all', ['q' => $term]))->assertViewHas('animes', fn ($page) => $page->total() === 1 && $page->first()->id === $anime->id);
    }
    // The OR must stay grouped: an alternative name cannot bypass another filter.
    $this->get(route('anime.index.all', ['q' => 'SnK', 'genre' => 'Romance']))->assertViewHas('animes', fn ($page) => $page->total() === 0);
    $this->get(route('personal.watch-later.index', ['q' => 'SnK']))->assertViewHas('plans', fn ($page) => $page->total() === 1);
    $this->get(route('personal.watch-later.index', ['q' => 'inexistente']))->assertViewHas('plans', fn ($page) => $page->total() === 0);
});

test('release range includes its limits, accepts open ends and hides animes without a date', function () {
    $first = metadataAnime(['title' => 'A', 'aired_from' => '2013-04-07']);
    $second = metadataAnime(['title' => 'B', 'aired_from' => '2020-01-01']);
    metadataAnime(['title' => 'Sem data']);
    $this->actingAs(User::factory()->create());
    $ids = fn (array $query) => $this->get(route('anime.index.all', $query))->assertOk()->viewData('animes')->pluck('id')->sort()->values()->all();
    expect($ids(['aired_from' => '2013-04-07', 'aired_until' => '2013-04-07']))->toBe([$first->id])
        ->and($ids(['aired_from' => '2014-01-01']))->toBe([$second->id])
        ->and($ids(['aired_until' => '2020-01-01']))->toBe([$first->id, $second->id]);
    $this->get(route('anime.index.all'))->assertSee('name="aired_from"', false)->assertSee('name="aired_until"', false);
});

test('invalid release ranges are rejected with a clear message', function () {
    $this->actingAs(User::factory()->create());
    $this->get(route('anime.index.all', ['aired_from' => '2020-01-02', 'aired_until' => '2020-01-01']))
        ->assertSessionHasErrors(['aired_until' => 'A data final do lançamento deve ser igual ou posterior à inicial.']);
    $this->get(route('anime.index.all', ['aired_from' => 'ontem']))->assertSessionHasErrors('aired_from');
});

test('anime details show new data only when it exists', function () {
    $anime = metadataAnime(['aired_from' => '2013-04-07', 'aired_precision' => 'day', 'studios' => 'Wit Studio', 'media_type' => 'Movie']);
    $anime->titles()->create(['type' => 'Synonym', 'title' => 'SnK']);
    $legacy = metadataAnime(['title' => 'Legado']);
    $this->actingAs(User::factory()->create());
    $this->get(route('anime.show', $anime))->assertOk()->assertSee('07/04/2013')->assertSee('Wit Studio')->assertSee('Filme')->assertSee('Também conhecido como')->assertSee('SnK');
    $this->get(route('anime.show', $legacy))->assertOk()->assertDontSee('Lançamento')->assertDontSee('Também conhecido como');
});

function globalPlan(int $watched, array $attributes = []): WatchPlan
{
    $plan = WatchPlan::create(array_merge(['anime_id' => metadataAnime(['episodes' => 24])->id, 'user_id' => null, 'episodes_watched' => $watched, 'episodes_per_day' => 1, 'start_date' => today()->subDays(3)->toDateString(), 'watch_status' => 'assistindo'], $attributes));
    foreach (range(0, 6) as $day) {
        $plan->days()->create(['day_of_week' => $day, 'episodes_planned' => 1, 'is_variable' => false]);
    }

    return $plan;
}

test('following copies the community plan to a personal one at the same episode and they stay together', function () {
    $this->travelTo(now()->setDate(2026, 11, 5)->startOfDay());
    $source = globalPlan(2);
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('personal.watch-plans.follow', $source))
        ->assertRedirect(route('personal.animes.index'))->assertSessionHas('success', fn ($message) => str_contains($message, 'episódio 5'));
    $copy = WatchPlan::ownedBy($user->id)->firstOrFail();
    expect($copy->current_episode_calculated)->toBe(5)
        ->and($copy->days()->count())->toBe(7)
        ->and(WatchPlan::global()->count())->toBe(1)
        ->and($source->fresh()->episodes_watched)->toBe(2);
    $this->travel(4)->days();
    expect($copy->fresh()->current_episode_calculated)->toBe($source->fresh()->current_episode_calculated);
});

test('a community log made today does not push the copy ahead tomorrow', function () {
    $this->travelTo(now()->setDate(2026, 11, 5)->startOfDay());
    $source = globalPlan(2);
    $source->logs()->create(['watched_date' => today()->toDateString(), 'episodes_watched_today' => 3]);
    $user = User::factory()->create();
    $copy = app(PlanManager::class)->copy($source->fresh(), $user)->fresh();
    expect($copy->current_episode_calculated)->toBe($source->fresh()->current_episode_calculated);
    $this->travel(3)->days();
    expect($copy->fresh()->current_episode_calculated)->toBe($source->fresh()->current_episode_calculated);
});

test('following upgrades a watch-later entry but never overwrites real progress', function () {
    $source = globalPlan(2);
    $user = User::factory()->create();
    $manager = app(PlanManager::class);
    $later = $manager->saveForLater($source->anime, $user);
    $manager->copy($source, $user);
    expect($later->fresh()->watch_status)->toBe('assistindo')->and($later->fresh()->current_episode_calculated)->toBe(5)
        ->and(WatchPlan::ownedBy($user->id)->count())->toBe(1);

    $other = globalPlan(10);
    $own = WatchPlan::create(['user_id' => $user->id, 'anime_id' => $other->anime_id, 'watch_status' => 'pausado', 'start_date' => today()->toDateString(), 'episodes_watched' => 1]);
    $this->actingAs($user)->post(route('personal.watch-plans.follow', $other))
        ->assertSessionHas('success', 'Este anime já está na sua biblioteca. Seu progresso foi mantido.');
    expect($own->fresh()->only('watch_status', 'episodes_watched'))->toBe(['watch_status' => 'pausado', 'episodes_watched' => 1]);
});

test('following an already finished community anime goes straight to completed', function () {
    $source = globalPlan(24);
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('personal.watch-plans.follow', $source))->assertSessionHas('success', fn ($message) => str_contains($message, 'concluídos'));
    expect(WatchPlan::ownedBy($user->id)->first()->watch_status)->toBe('concluido');
});

test('home offers follow until the anime is in the library', function () {
    $source = globalPlan(2);
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->get(route('dashboard'))->assertOk()->assertSee(route('personal.watch-plans.follow', $source), false)->assertSee('a partir do episódio 5')->assertDontSee('Na minha lista');
    app(PlanManager::class)->saveForLater($source->anime, $user);
    $this->get(route('dashboard'))->assertSee(route('personal.watch-plans.follow', $source), false);
    $this->post(route('personal.watch-plans.follow', $source));
    $this->get(route('dashboard'))->assertDontSee(route('personal.watch-plans.follow', $source), false)->assertSee('Na minha lista');
});

test('profile watch-later and plan removal answer JSON for live updates and keep the classic flow', function () {
    $user = User::factory()->create();
    $anime = metadataAnime();
    $this->actingAs($user)->postJson(route('personal.watch-later.store', $anime))->assertOk()->assertJsonPath('blocks.0', 'stats');
    $plan = WatchPlan::ownedBy($user->id)->firstOrFail();
    $this->getJson(route('profile.show', ['username' => $user->username, 'blocks' => ['planned']]))->assertOk()->assertJsonPath('fragments.planned', fn ($html) => str_contains($html, 'data-collection-action'));
    $this->deleteJson(route('personal.animes.destroy', $plan))->assertOk()->assertJson(['blocks' => ['stats', 'planned']]);
    expect(WatchPlan::find($plan->id))->toBeNull();
    $this->from(route('anime.index.all'))->post(route('personal.watch-later.store', $anime))->assertRedirect(route('anime.index.all'));
    $this->delete(route('personal.animes.destroy', WatchPlan::ownedBy($user->id)->firstOrFail()))->assertRedirect(route('personal.animes.index'));
});

test('studio source and season filters work on every listing and combine with other filters', function () {
    $user = User::factory()->create(['show_watching_public' => true]);
    $target = metadataAnime(['title' => 'Alvo', 'studios' => 'Wit Studio, Bones', 'source_material' => 'Manga', 'season' => 'spring', 'season_year' => 2013, 'genres' => 'Action']);
    $other = metadataAnime(['title' => 'Outro', 'studios' => 'Bones Film', 'source_material' => 'Original', 'season' => 'spring', 'season_year' => 2014, 'genres' => 'Action']);
    metadataAnime(['title' => 'Sem dados']);
    foreach ([$target, $other] as $anime) {
        WatchPlan::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'watch_status' => 'pretendo_assistir', 'start_date' => today()->toDateString(), 'episodes_watched' => 0]);
    }
    $this->actingAs($user);
    $catalog = fn (array $query) => $this->get(route('anime.index.all', $query))->assertOk()->viewData('animes')->pluck('title')->all();

    // Whole names only, in any position of the stored list.
    expect($catalog(['studio' => 'Bones']))->toBe(['Alvo'])
        ->and($catalog(['studio' => 'Wit Studio']))->toBe(['Alvo'])
        ->and($catalog(['studio' => 'Bones Film']))->toBe(['Outro'])
        ->and($catalog(['studio' => 'Wit']))->toBe([])
        ->and($catalog(['source' => 'Manga']))->toBe(['Alvo'])
        ->and($catalog(['season' => 'spring-2014']))->toBe(['Outro'])
        ->and($catalog(['season' => 'fall-2013']))->toBe([])
        ->and($catalog(['studio' => 'Bones', 'source' => 'Original']))->toBe([])
        ->and($catalog(['studio' => 'Bones', 'genre' => 'Action', 'season' => 'spring-2013', 'source' => 'Manga']))->toBe(['Alvo']);

    $this->get(route('personal.watch-later.index', ['source' => 'Manga']))->assertViewHas('plans', fn ($page) => $page->total() === 1);
    $this->get(route('personal.animes.index', ['season' => 'spring-2014']))->assertViewHas('plannedPlans', fn ($page) => $page->total() === 1);
    $fragment = $this->getJson(route('profile.show', ['username' => $user->username, 'blocks' => ['planned'], 'studio' => 'Bones Film']))->assertOk()->json('fragments.planned');
    expect($fragment)->toContain('Outro')->not->toContain('Alvo');
});

test('filter options come from registered data with readable labels in calendar order', function () {
    metadataAnime(['studios' => 'Wit Studio, Bones', 'source_material' => 'Manga', 'season' => 'fall', 'season_year' => 2013]);
    metadataAnime(['studios' => 'Bones', 'source_material' => 'Manga', 'season' => 'spring', 'season_year' => 2013]);
    metadataAnime(['source_material' => 'Light novel', 'season' => 'winter', 'season_year' => 2020]);
    $this->actingAs(User::factory()->create());
    $response = $this->get(route('anime.index.all', ['studio' => 'Bones', 'season' => 'fall-2013']))->assertOk();
    $response->assertSee('<option value="Bones" selected>Bones</option>', false)
        ->assertSee('<option value="Wit Studio" >Wit Studio</option>', false)
        ->assertSee('<option value="Manga" >Mangá</option>', false)
        ->assertSee('<option value="Light novel" >Light novel</option>', false)
        ->assertSee('<option value="fall-2013" selected>Outono de 2013</option>', false)
        ->assertSeeInOrder(['Inverno de 2020', 'Outono de 2013', 'Primavera de 2013'])
        ->assertSee('Limpar filtros');
    expect(substr_count($response->getContent(), 'value="Bones"'))->toBe(1);
});

test('filters render with no metadata yet and reject an invalid season', function () {
    metadataAnime();
    $this->actingAs(User::factory()->create());
    $this->get(route('anime.index.all'))->assertOk()->assertSee('Todos os estúdios')->assertSee('Todas as origens')->assertSee('Todas as temporadas');
    $this->get(route('anime.index.all', ['season' => "spring-2013' or 1=1"]))->assertSessionHasErrors(['season' => 'Escolha uma temporada válida.']);
    $this->get(route('anime.index.all', ['season' => 'spring']))->assertSessionHasErrors('season');
});
