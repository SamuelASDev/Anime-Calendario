<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;
use App\Queries\ProfileQuery;

function collectionAnime(string $title, array $data = []): Anime
{
    return Anime::create(array_merge(['mal_id' => fake()->unique()->numberBetween(1, 2000000000), 'title' => $title,
        'genres' => 'Action, Fantasy', 'episodes' => 12, 'anime_status' => 'Finished Airing'], $data));
}

function collectionPlan(User $user, Anime $anime, string $status = 'pretendo_assistir'): WatchPlan
{
    return WatchPlan::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'watch_status' => $status,
        'start_date' => today()->toDateString(), 'episodes_watched' => 0]);
}

test('catalog combines filters using only the current users library and favorites', function () {
    $user = User::factory()->create();
    $target = collectionAnime('Brisa de Verao', ['genres' => 'Romance, Drama']);
    collectionPlan($user, $target);
    UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $target->id, 'is_favorite' => true]);
    collectionPlan(User::factory()->create(), collectionAnime('Brisa de Inverno', ['genres' => 'Romance']), 'concluido');
    $this->actingAs($user)->get(route('anime.index.all', ['q' => 'Brisa', 'genre' => 'Romance', 'status' => 'pretendo_assistir',
        'airing' => 'Finished Airing', 'length' => 'short', 'favorite' => 1, 'sort' => 'title']))
        ->assertOk()->assertViewHas('animes', fn ($page) => $page->total() === 1 && $page->first()->id === $target->id);
});

test('catalog sorts by title public rating and episodes and finds unlisted anime', function () {
    $user = User::factory()->create();
    $alpha = collectionAnime('Alpha', ['episodes' => 48]);
    $beta = collectionAnime('Beta', ['episodes' => 24]);
    $gamma = collectionAnime('Gamma', ['episodes' => null]);
    collectionPlan($user, $alpha, 'assistindo');
    UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $beta->id, 'rating' => 9]);
    UserAnimeMeta::create(['user_id' => User::factory()->create(['show_reviews_public' => false])->id, 'anime_id' => $alpha->id, 'rating' => 10]);
    $this->actingAs($user)->get(route('anime.index.all', ['sort' => 'title']))->assertOk()
        ->assertViewHas('animes', fn ($page) => $page->pluck('title')->all() === ['Alpha', 'Beta', 'Gamma']);
    $this->get(route('anime.index.all', ['sort' => 'rating']))->assertOk()->assertViewHas('animes', fn ($page) => $page->first()->id === $beta->id);
    $this->get(route('anime.index.all', ['sort' => 'episodes']))->assertOk()->assertViewHas('animes', fn ($page) => $page->first()->id === $alpha->id);
    $this->get(route('anime.index.all', ['status' => 'unlisted', 'length' => 'unknown']))->assertOk()
        ->assertViewHas('animes', fn ($page) => $page->total() === 1 && $page->first()->id === $gamma->id);
});

test('library and completed filters preserve ownership and favorite selection', function () {
    $user = User::factory()->create();
    $saved = collectionPlan($user, collectionAnime('Minha aventura'));
    $done = collectionPlan($user, collectionAnime('Minha aventura final'), 'concluido');
    collectionPlan(User::factory()->create(), collectionAnime('Minha aventura secreta'), 'concluido');
    UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $saved->anime_id, 'is_favorite' => true]);
    UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $done->anime_id, 'is_favorite' => true]);
    $this->actingAs($user)->get(route('personal.animes.index', ['q' => 'aventura', 'genre' => 'Action', 'favorite' => 1, 'status' => 'pretendo_assistir']))
        ->assertOk()->assertViewHas('plannedPlans', fn ($page) => $page->total() === 1 && $page->first()->id === $saved->id);
    $this->get(route('personal.completed', ['q' => 'final', 'genre' => 'Fantasy', 'favorite' => 1]))->assertOk()
        ->assertViewHas('plans', fn ($page) => $page->total() === 1 && $page->first()->id === $done->id);
});

test('watch later filtering preserves parameters in pagination links', function () {
    $user = User::factory()->create();
    foreach (range(1, 14) as $index) {
        collectionPlan($user, collectionAnime('Alpha '.$index));
    }
    collectionPlan($user, collectionAnime('Outro anime', ['genres' => 'Romance']));
    $this->actingAs($user)->get(route('personal.watch-later.index', ['q' => 'Alpha', 'genre' => 'Action', 'sort' => 'title']))
        ->assertOk()->assertViewHas('plans', fn ($page) => $page->total() === 14 && $page->count() === 12
            && str_contains($page->nextPageUrl(), 'genre=Action') && str_contains($page->nextPageUrl(), 'q=Alpha'));
});

test('profile statistics reflect the whole collection regardless of filters and pagination', function () {
    $user = User::factory()->create();
    foreach (range(1, 15) as $index) {
        $anime = collectionAnime('Colecao '.$index);
        collectionPlan($user, $anime, 'assistindo');
        UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'is_favorite' => true]);
    }
    collectionPlan($user, collectionAnime('Para depois'));
    collectionPlan($user, collectionAnime('Concluido'), 'concluido');
    $response = $this->actingAs($user)->get(route('profile.show', $user->username));
    $response->assertOk()->assertViewHas('stats', ['favorites' => 15, 'watching' => 15, 'completed' => 1, 'planned' => 1])
        ->assertViewHas('watching', fn ($page) => $page->count() === 12)
        ->assertSee('href="#watching"', false)->assertSee('href="#planned"', false);
    $this->get(route('profile.show', ['username' => $user->username, 'q' => 'sem resultado']))->assertOk()
        ->assertViewHas('stats', ['favorites' => 15, 'watching' => 15, 'completed' => 1, 'planned' => 1])
        ->assertViewHas('watching', fn ($page) => $page->total() === 0);
});

test('profile stats do not reveal hidden list counts to another account', function () {
    $user = User::factory()->create(['show_watching_public' => false, 'show_favorites_public' => false]);
    collectionPlan($user, collectionAnime('Minha espera'));
    collectionPlan($user, collectionAnime('Meu anime'), 'assistindo');
    $this->actingAs(User::factory()->create())->get(route('profile.show', $user->username))->assertOk()
        ->assertViewHas('stats', ['favorites' => null, 'watching' => null, 'completed' => 0, 'planned' => null])
        ->assertDontSee('Minha espera')->assertDontSee('Meu anime')->assertSee('Privado');
});

test('cards use native detail links with independent form actions', function () {
    $user = User::factory()->create();
    $anime = collectionAnime('Card navegavel');
    collectionPlan($user, $anime);
    foreach (['anime.index.all', 'personal.watch-later.index', 'personal.animes.index'] as $route) {
        $response = $this->actingAs($user)->get(route($route))->assertOk();
        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($doc);
        $links = $xpath->query('//article[@data-anime-card]//a[contains(@class,"card-link")]');
        expect($links->length)->toBeGreaterThan(0)->and($links->item(0)->getAttribute('href'))->toBe(route('anime.show', $anime));
        expect($xpath->query('//article[@data-anime-card]//form[ancestor::a]')->length)->toBe(0);
    }
});

test('listing filter validation rejects arrays and invalid sorting', function () {
    $this->actingAs(User::factory()->create())->get(route('anime.index.all', ['sort' => 'not_a_column']))->assertSessionHasErrors('sort');
    $this->get(route('personal.animes.index', ['q' => ['invalid']]))->assertSessionHasErrors('q');
});

test('directory searches by username with or without an at sign and excludes private profiles', function () {
    $user = User::factory()->create(['username' => 'colecionadora', 'name' => 'Ana']);
    User::factory()->create(['username' => 'colecionadora_privada', 'is_public' => false]);
    $this->actingAs($user)->get(route('users.index', ['q' => '@colecionadora']))->assertOk()
        ->assertViewHas('users', fn ($page) => $page->total() === 1 && $page->first()->id === $user->id);
});

test('synopsis editing updates the displayed translation while preserving the original', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $anime = collectionAnime('Uma historia', ['synopsis' => 'Original synopsis', 'synopsis_pt' => 'Sinopse anterior']);
    $this->actingAs($admin)->get(route('anime.synopsis.edit', $anime))->assertOk()->assertSee('Sinopse anterior');
    $this->patch(route('anime.synopsis.update', $anime), ['synopsis' => 'Sinopse revisada'])->assertSessionHasNoErrors()->assertRedirect();
    expect($anime->fresh()->synopsis_pt)->toBe('Sinopse revisada')->and($anime->fresh()->synopsis)->toBe('Original synopsis');
    $this->get(route('anime.show', $anime))->assertOk()->assertSee('Sinopse revisada');
});

test('watch later controls use the viewer status even on other profiles', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create(['show_favorites_public' => true]);
    $anime = collectionAnime('Anime compartilhado');
    collectionPlan($viewer, $anime, 'pausado');
    UserAnimeMeta::create(['user_id' => $owner->id, 'anime_id' => $anime->id, 'is_favorite' => true]);
    $this->actingAs($viewer)->get(route('profile.show', $owner->username))->assertOk()
        ->assertDontSee(route('personal.watch-later.store', $anime), false);
});

test('profile ranking controls appear only in sortable sections', function () {
    $user = User::factory()->create();
    $anime = collectionAnime('Anime ordenavel');
    collectionPlan($user, $anime, 'assistindo');
    UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'is_favorite' => true, 'top_position' => 1]);
    $response = $this->actingAs($user)->get(route('profile.show', $user->username))->assertOk();
    $doc = new DOMDocument;
    @$doc->loadHTML($response->getContent());
    $xpath = new DOMXPath($doc);
    expect($xpath->query('//section[@id="watching"]//button[@title="Subir no Top 10"]')->length)->toBe(0)
        ->and($xpath->query('//section[@id="favorites"]//button[@title="Subir em Favoritos"]')->length)->toBe(1)
        ->and($xpath->query('//section[@id="top10"]//form[@data-collection-action]')->length)->toBeGreaterThan(0);
});

test('favorite and completed positions are independent from each other and top ten', function () {
    $user = User::factory()->create();
    $a = collectionAnime('Primeiro');
    $b = collectionAnime('Segundo');
    collectionPlan($user, $a, 'concluido');
    collectionPlan($user, $b, 'concluido');
    foreach ([$a, $b] as $i => $anime) {
        UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'is_favorite' => true, 'top_position' => $i + 1]);
    }
    $this->actingAs($user)->from(route('profile.show', $user->username))
        ->patch(route('personal.animes.collection-order', $b), ['collection' => 'favorites', 'direction' => 'up'])->assertRedirect();
    $query = app(ProfileQuery::class);
    $data = $query->sections($user, true);
    expect($data['favorites']->pluck('anime_id')->all())->toBe([$b->id, $a->id])
        ->and($data['completed']->pluck('anime_id')->all())->toBe([$b->id, $a->id])
        ->and($data['top10']->pluck('anime_id')->all())->toBe([$a->id, $b->id]);
    $this->patch(route('personal.animes.collection-order', $a), ['collection' => 'completed', 'direction' => 'up'])->assertRedirect();
    $data = $query->sections($user, true);
    expect($data['completed']->pluck('anime_id')->all())->toBe([$a->id, $b->id])
        ->and($data['favorites']->pluck('anime_id')->all())->toBe([$b->id, $a->id])
        ->and($data['top10']->pluck('anime_id')->all())->toBe([$a->id, $b->id]);
    $this->patch(route('personal.animes.top.down', $a))->assertRedirect();
    $data = $query->sections($user, true);
    expect($data['top10']->pluck('anime_id')->all())->toBe([$b->id, $a->id])
        ->and($data['completed']->pluck('anime_id')->all())->toBe([$a->id, $b->id])
        ->and($data['favorites']->pluck('anime_id')->all())->toBe([$b->id, $a->id]);
});

test('collection order rejects other accounts entries invalid blocks and invalid directions', function () {
    $owner = User::factory()->create();
    $anime = collectionAnime('Privado');
    collectionPlan($owner, $anime, 'concluido');
    UserAnimeMeta::create(['user_id' => $owner->id, 'anime_id' => $anime->id, 'is_favorite' => true]);
    $this->actingAs(User::factory()->create());
    foreach (['favorites', 'completed'] as $collection) {
        $this->patch(route('personal.animes.collection-order', $anime), ['collection' => $collection, 'direction' => 'up'])->assertNotFound();
    }
    $this->patch(route('personal.animes.collection-order', $anime), ['collection' => 'watching', 'direction' => 'up'])->assertSessionHasErrors('collection');
    $this->patch(route('personal.animes.collection-order', $anime), ['collection' => 'favorites', 'direction' => 'sideways'])->assertSessionHasErrors('direction');
    expect(UserAnimeMeta::where('user_id', $owner->id)->first()->favorite_position)->toBeNull();
});
