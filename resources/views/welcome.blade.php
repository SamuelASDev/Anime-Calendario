<!DOCTYPE html>
<html class="dark" lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101b2b">
    <meta name="description" content="Seu espaço para descobrir animes, organizar episódios e guardar favoritos. Monte uma coleção com a sua cara.">
    <title>Anime Tracker · Seu próximo episódio começa aqui</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] btn btn-primary">Pular para o conteúdo</a>
    <header class="site-header">
        <div class="nav-wrap">
            <x-brand />
            <nav class="flex gap-2" aria-label="Sua conta">
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Minha coleção <x-icon name="arrow" /></a>
                @else
                    <a class="btn btn-quiet" href="{{ route('login') }}">Entrar</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Criar conta</a>
                @endauth
            </nav>
        </div>
    </header>
    <main id="main-content" class="page-wrap landing-content" tabindex="-1">
        <section class="collection-hero" aria-labelledby="hero-title">
            <div class="collection-intro">
                <p class="edition-label"><span aria-hidden="true">●</span> Para quem sempre diz “só mais um”</p>
                <h1 id="hero-title">A vida entre<br>um episódio<br>e outro.</h1>
                <p class="hero-summary">Tem anime que vira favorito. Tem indicação que não pode se perder. Dê um lugar para cada um na sua coleção.</p>
                <div class="hero-actions">
                    @auth
                        <a class="btn btn-primary" href="{{ route('dashboard') }}"><x-icon name="play" />Continuar minha coleção</a>
                        <a class="btn btn-secondary" href="{{ route('anime.index.all') }}">Explorar animes</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('register') }}"><x-icon name="plus" />Montar minha coleção</a>
                        <a class="btn btn-secondary" href="{{ route('login') }}">Já tenho uma conta</a>
                    @endauth
                </div>
                <p class="hero-footnote"><x-icon name="bookmark" />Sua lista. Seu ritmo. Seu próximo favorito.</p>
            </div>
            <div class="cover-stage">
                <div class="stage-caption"><span>Da abertura aos créditos.</span><span aria-hidden="true">再生 / play</span></div>
                <img class="rooftop-art" src="{{ asset('images/anime-rooftop.webp') }}" alt="Ilustração anime: uma fã de fones de ouvido e seu pequeno sapo contemplam a cidade de um terraço ao entardecer." width="1536" height="1024" fetchpriority="high">
                <div class="cover-shelf {{ $featuredAnimes->isEmpty() ? 'cover-shelf-empty' : '' }}">
                    @forelse($featuredAnimes as $anime)
                        <a class="shelf-cover" href="{{ route('anime.show', $anime) }}">
                            <img src="{{ $anime->image }}" alt="Capa de {{ $anime->title }}" width="260" height="370" @if(!$loop->first) loading="lazy" @endif>
                            <span class="shelf-cover-info"><strong>{{ $anime->title }}</strong><span>{{ $anime->episodes ? $anime->episodes.' episódios' : 'Uma nova história' }}</span></span>
                        </a>
                    @empty
                        <div class="shelf-invitation"><x-icon name="play" /><p>Toda coleção começa<br>com um primeiro anime.</p></div>
                    @endforelse
                </div>
                <div class="episode-ticket"><span class="ticket-icon"><x-icon name="play" /></span><div><strong>O próximo episódio é seu.</strong><p>Descubra. Acompanhe. Favorite.</p></div><span aria-hidden="true" class="ticket-lines">▥</span></div>
            </div>
        </section>
        <section class="collection-features" aria-labelledby="features-title">
            <div class="collection-section-title"><h2 id="features-title">Muito além de uma lista.</h2><p>Um cantinho para o seu lado fã.</p></div>
            <div class="feature-grid">
                @foreach([['book', 'Cada anime, no seu lugar.', 'Assistindo agora, pausado ou guardado para depois. Encontre suas histórias sem depender da memória.'], ['calendar', 'Até o próximo episódio.', 'Escolha os dias e quantos episódios quer assistir. Uma agenda que acompanha o seu ritmo.'], ['star', 'Favoritos dizem muito.', 'Avalie, monte seu Top 10 e descubra o que outras pessoas estão assistindo.']] as [$icon, $title, $text])
                    <article class="feature-item"><x-icon :name="$icon" /><h3>{{ $title }}</h3><p>{{ $text }}</p></article>
                @endforeach
            </div>
        </section>
        <section class="collection-outro"><div><h2>Qual vai ser o próximo?</h2><p>Aquela indicação merece mais do que uma mensagem perdida.</p></div><a class="btn btn-primary" href="{{ auth()->check() ? route('personal.watch-later.index') : route('register') }}">{{ auth()->check() ? 'Ver minha lista de espera' : 'Criar minha coleção' }}<x-icon name="bookmark" /></a></section>
    </main>
    <x-site-footer />
</body>
</html>
