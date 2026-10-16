<x-app-layout>
    <div class="page-wrap">
        <section class="dashboard-hero">
            <div><p class="eyebrow">BEM-VINDO DE VOLTA, {{ mb_strtoupper(explode(' ', auth()->user()->name)[0]) }}</p><h1>Sempre existe<br>uma <em>próxima história.</em></h1><p>Retome de onde parou, organize sua semana ou descubra aquele anime que vai ficar na memória.</p><div class="hero-actions"><a class="btn btn-primary" href="{{ route('anime.index.all') }}"><x-icon name="search" />Explorar animes</a><a class="btn btn-secondary" href="{{ route('personal.calendar') }}"><x-icon name="calendar" />Minha agenda</a></div></div>
            <div class="dashboard-stats">
                @foreach(['assistindo' => ['Assistindo', 'play', 'watching'], 'concluido' => ['Concluídos', 'check', 'completed'], 'pretendo_assistir' => ['Em espera', 'bookmark', 'planned'], 'pausado' => ['Pausados', 'pause', 'favorites']] as $status => [$label, $icon, $tone])
                    <a class="stat-{{ $tone }}" href="{{ route('personal.animes.index', ['status' => $status]) }}"><x-icon :name="$icon" /><strong>{{ $libraryCounts[$status] ?? 0 }}</strong><span>{{ $label }}</span></a>
                @endforeach
            </div>
        </section>
        @if(session('success'))<p role="status" class="flash mt-6">{{ session('success') }}</p>@endif
        <section>
            <div class="section-heading"><div><h2><x-icon name="play" />Continue sua jornada</h2><p>Os animes que você está acompanhando.</p></div><a href="{{ route('personal.animes.index') }}">Minha biblioteca <x-icon name="arrow" class="w-3 h-3" /></a></div>
            <div class="horizontal-grid">@forelse($personalPlans as $plan)<x-plan-card :plan="$plan" route-prefix="personal.animes" />@empty<div class="col-span-full"><x-empty-state title="Qual vai ser sua primeira história?" description="Encontre um anime no catálogo e monte uma agenda que combine com você."><a class="btn btn-primary" href="{{ route('anime.index.all') }}">Explorar catálogo</a></x-empty-state></div>@endforelse</div>
        </section>
        <section>
            <div class="section-heading"><div><h2><x-icon name="users" />No ritmo da comunidade</h2><p>Histórias que estamos assistindo juntos.</p></div><a href="{{ route('calendar') }}">Calendário da comunidade <x-icon name="arrow" class="w-3 h-3" /></a></div>
            <div class="horizontal-grid">@forelse($liveWatchingAnimes as $plan)<x-plan-card :plan="$plan" route-prefix="admin.animes" :editable="false" />@empty<div class="col-span-full"><x-empty-state title="Uma pausa entre histórias" description="Quando a comunidade começar um novo anime, ele aparece aqui." icon="users" /></div>@endforelse</div>
        </section>
        <div class="surface mt-9 p-6 flex flex-wrap items-center justify-between gap-5"><div><p class="eyebrow">PARA ASSISTIR DEPOIS</p><h2 class="text-lg font-semibold">Não deixe uma boa indicação se perder.</h2><p class="text-sm text-slate-400 mt-2">Guarde suas próximas escolhas em Pretendo assistir.</p></div><a class="btn btn-secondary" href="{{ route('personal.watch-later.index') }}"><x-icon name="bookmark" />Ver minha lista de espera</a></div>
    </div>
</x-app-layout>
