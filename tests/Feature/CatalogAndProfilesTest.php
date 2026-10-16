<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Services\JikanService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('community catalog never reveals reviews hidden by profile preferences', function () {
    $viewer = User::factory()->create();
    $hidden = User::factory()->create(['show_reviews_public' => false]);
    $anime = Anime::create(['mal_id' => 100, 'title' => 'Catalogo']);
    UserAnimeMeta::create(['anime_id' => $anime->id, 'user_id' => $hidden->id, 'comment' => 'SEGREDO_DE_REVIEW', 'rating' => 10]);
    $this->actingAs($viewer)->get(route('anime.show', $anime))->assertOk()->assertDontSee('SEGREDO_DE_REVIEW');
    $this->get(route('anime.index.all'))->assertOk()->assertDontSee('SEGREDO_DE_REVIEW');
    $this->get(route('profile.show', $hidden->username))->assertOk()->assertDontSee('SEGREDO_DE_REVIEW');
});

test('private profile owner can see their profile while another account cannot', function () {
    $user = User::factory()->create(['is_public' => false]);
    $this->actingAs($user)->get(route('profile.show', $user->username))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('profile.show', $user->username))->assertForbidden();
});

test('legacy username does not break account navigation', function () {
    $user = User::factory()->create();
    $user->forceFill(['username' => null])->save();
    $this->actingAs($user)->get('/profile')->assertOk();
    $this->get('/dashboard')->assertOk();
    $this->get('/users')->assertOk();
});

test('search failures return a friendly message and successful search is cached', function () {
    Http::preventStrayRequests();
    Http::fake(['api.jikan.moe/*' => Http::sequence()->push(['error' => 'unavailable'], 429)
        ->push(['data' => [['mal_id' => 1, 'title' => 'Teste', 'images' => ['jpg' => ['image_url' => 'https://example.test/cover.jpg']]]]])]);
    $this->actingAs(User::factory()->create())->get(route('anime.search', ['q' => 'falha']))
        ->assertOk()->assertSee('A busca está indisponível');
    Cache::flush();
    $service = app(JikanService::class);
    expect($service->search('teste'))->toHaveCount(1)->and($service->search('teste'))->toHaveCount(1);
    Http::assertSentCount(2);
    $this->get(route('anime.search', ['q' => 'teste']))->assertOk()->assertSee('Adicionar à minha biblioteca');
});

test('profile uploads use public storage and preserve a valid image path', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user)->patch(route('profile.public.update'), [
        'username' => $user->username, 'is_public' => 1,
        'profile_photo' => UploadedFile::fake()->createWithContent('avatar.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=')),
    ])->assertSessionHasNoErrors()->assertRedirect();
    Storage::disk('public')->assertExists($user->fresh()->profile_photo);
    expect($user->fresh()->profile_photo_url)->not->toContain('/public/storage/');
});
