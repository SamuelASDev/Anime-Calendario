<?php

use App\Contracts\AnimeCatalogProvider;
use App\Data\CatalogAnime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Queries\ProfileQuery;
use App\Services\AnimeSearchService;
use App\Services\Library\CollectionOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('profile mutations return only a JSON acknowledgement and requested fragments retain filters', function () {
    $user = User::factory()->create();
    $anime = collectionAnime('Fragmento');
    UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'is_favorite' => true]);
    $this->actingAs($user)->patchJson(route('personal.animes.collection-order', $anime), ['collection' => 'favorites', 'direction' => 'up'])
        ->assertOk()->assertExactJson(['message' => 'Ordem da coleção atualizada.', 'blocks' => ['favorites']]);
    $response = $this->getJson(route('profile.show', ['username' => $user->username, 'blocks' => ['favorites']]))->assertOk();
    expect(array_keys($response->json('fragments')))->toBe(['favorites']);
    expect($response->json('fragments.favorites'))->toContain('Fragmento')->not->toContain('<html')->not->toContain('profile-hero');
    expect($response->json('fragments.favorites'))->not->toContain('blocks%5B');
    $filtered = $this->getJson(route('profile.show', ['username' => $user->username, 'blocks' => ['favorites'], 'q' => 'inexistente']))->assertOk();
    expect($filtered->json('fragments.favorites'))->not->toContain('Capa de Fragmento');
});

test('Tenrai normalizes nullable fields genres and invalid entries into catalog DTOs', function () {
    Http::fake(['api.tenrai.org/*' => Http::response(['data' => [
        ['mal_id' => 42, 'title' => ' Normalizado ', 'episodes' => 0, 'synopsis' => ['invalid'], 'status' => null,
            'images' => ['jpg' => ['image_url' => 'https://example.test/small.jpg', 'large_image_url' => 'https://example.test/large.jpg']],
            'genres' => [['name' => 'Drama']], 'themes' => [['name' => 'Drama'], ['name' => 'School']], 'demographics' => null],
        ['mal_id' => -1, 'title' => 'Inválido', 'images' => ['jpg' => ['image_url' => 'https://example.test/image.jpg']]],
        ['mal_id' => 43, 'title' => 'Inválido', 'images' => ['jpg' => ['image_url' => 'javascript:alert(1)']]],
    ]])]);
    $items = app(AnimeSearchService::class)->search('normalizado');
    expect($items)->toHaveCount(1)->and($items[0])->toBeInstanceOf(CatalogAnime::class)
        ->and($items[0]->malId)->toBe(42)->and($items[0]->title)->toBe('Normalizado')
        ->and($items[0]->episodes)->toBeNull()->and($items[0]->synopsis)->toBeNull()
        ->and($items[0]->genres)->toBe(['Drama', 'School'])
        ->and($items[0]->imageUrl)->toBe('https://example.test/large.jpg');
});

test('fragment endpoint enforces privacy and an explicit block allowlist', function () {
    $owner = User::factory()->create(['show_favorites_public' => false]);
    $anime = collectionAnime('Favorito secreto');
    UserAnimeMeta::create(['user_id' => $owner->id, 'anime_id' => $anime->id, 'is_favorite' => true]);
    $this->actingAs(User::factory()->create())->getJson(route('profile.show', ['username' => $owner->username, 'blocks' => ['favorites', 'planned']]))
        ->assertOk()->assertExactJson(['fragments' => ['favorites' => '', 'planned' => '']]);
    $this->getJson(route('profile.show', ['username' => $owner->username, 'blocks' => ['../edit']]))->assertUnprocessable();
    $owner->update(['is_public' => false]);
    $this->getJson(route('profile.show', ['username' => $owner->username, 'blocks' => ['stats']]))->assertForbidden();
});

test('moving a large collection writes only two records and preserves untouched positions', function () {
    $user = User::factory()->create();
    $rows = [];
    foreach (range(1, 120) as $index) {
        $anime = collectionAnime('Volume '.$index);
        $rows[] = ['user_id' => $user->id, 'anime_id' => $anime->id, 'is_favorite' => true];
    }
    UserAnimeMeta::insert($rows);
    $writes = [];
    DB::listen(function ($query) use (&$writes) {
        if (preg_match('/^(update|insert|delete) /i', $query->sql)) {
            $writes[] = $query->sql;
        }
    });
    app(CollectionOrder::class)->move($user, $rows[60]['anime_id'], 'favorites', 'up');
    expect($writes)->toHaveCount(2)
        ->and(UserAnimeMeta::where('user_id', $user->id)->whereNotNull('favorite_position')->count())->toBe(2);
    $favorites = app(ProfileQuery::class)->sections($user, true, [], ['favorites'])['favorites'];
    expect($favorites->total())->toBe(120);
});

test('catalog renders a different provider through the internal DTO without Tenrai fields', function () {
    Http::preventStrayRequests();
    $this->app->instance(AnimeCatalogProvider::class, new class implements AnimeCatalogProvider
    {
        public function sourceName(): string
        {
            return 'Outro provedor';
        }

        public function cacheIdentity(): string
        {
            return 'alternative:test';
        }

        public function search(string $query): array
        {
            return [new CatalogAnime(222, 'Outro catálogo', 'https://example.test/poster.jpg', 12, 'Sinopse', 'Finalizado', ['Drama'])];
        }
    });
    $this->actingAs(User::factory()->create())->get(route('anime.search', ['q' => 'outro']))
        ->assertOk()->assertSee('Outro catálogo')->assertSee('https://example.test/poster.jpg', false)->assertSee('name="mal_id" value="222"', false);
    expect(app(AnimeSearchService::class)->search('outro')[0])->toBeInstanceOf(CatalogAnime::class);
    Http::assertNothingSent();
});
