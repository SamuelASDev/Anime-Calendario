<?php

use App\Models\Anime;
use App\Models\User;

it('shows registration to visitors and supports an empty catalog', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Montar minha coleção')
        ->assertSee('Já tenho uma conta')
        ->assertSee('Toda coleção começa');
});

it('shows collection actions instead of login invitations to signed in users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Continuar minha coleção')
        ->assertSee('Explorar animes')
        ->assertDontSee('Já tenho uma conta')
        ->assertDontSee('Criar conta');
});

it('presents at most four catalog covers with escaped titles', function () {
    foreach (range(1, 5) as $number) {
        Anime::create(['mal_id' => $number, 'title' => $number === 5 ? '<script>Anime</script>' : 'Anime '.$number, 'image' => '/cover-'.$number.'.jpg']);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/cover-5.jpg')
        ->assertSee('&lt;script&gt;Anime&lt;/script&gt;', false)
        ->assertDontSee('/cover-1.jpg')
        ->assertDontSee('<script>Anime</script>', false);
});
