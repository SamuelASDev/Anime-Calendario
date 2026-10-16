<x-app-layout>
    <x-slot:title>{{ $user->name }} · Anime Tracker</x-slot:title>
    @php($owner = auth()->id() === $user->id)
    <div class="page-wrap">
        <header class="profile-hero">
            <div class="profile-banner">
                @if($user->profile_banner_url)<img src="{{ $user->profile_banner_url }}" alt="Banner de {{ $user->name }}">@endif
                <span class="profile-banner-label">MINHA COLEÇÃO DE HISTÓRIAS</span>
            </div>
            <div class="profile-overview">
                <div class="profile-identity">
                    <div class="profile-avatar">@if($user->profile_photo_url)<img src="{{ $user->profile_photo_url }}" alt="Foto de {{ $user->name }}">@else{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}@endif</div>
                    <div class="profile-name">
                        <h1>{{ $user->name }}</h1><p class="profile-handle">{{ '@'.$user->username }}</p>
                        @if($user->bio)<p class="profile-bio">{{ $user->bio }}</p>@endif
                        <div class="profile-tools">
                            @if($owner)<a class="btn btn-secondary btn-sm" href="{{ route('profile.edit') }}"><x-icon name="settings" />Editar perfil</a>@endif
                            @if(! $user->is_public)<span class="status-tag"><x-icon name="shield" class="w-3 h-3" />Perfil privado</span>@endif
                            <span class="text-xs text-slate-500">Na comunidade desde {{ $user->created_at->format('m/Y') }}</span>
                        </div>
                    </div>
                </div>
                <div data-async-summary="stats" class="profile-stats" aria-label="Estatísticas da coleção">
                    <x-profile-stat label="Favoritos" :value="$stats['favorites']" icon="star" section="favorites" tone="favorites" />
                    <x-profile-stat label="Assistindo" :value="$stats['watching']" icon="play" section="watching" tone="watching" />
                    <x-profile-stat label="Concluídos" :value="$stats['completed']" icon="check" section="completed" tone="completed" />
                    <x-profile-stat label="Em espera" :value="$stats['planned']" icon="bookmark" section="planned" tone="planned" />
                </div>
            </div>
        </header>
        @if(session('success'))<p role="status" class="flash">{{ session('success') }}</p>@endif
        <nav data-async-summary="nav" class="profile-nav" aria-label="Seções do perfil">
            @if(($owner || $user->show_top10_public) && $top10->count())<a href="#top10">Top 10</a>@endif
            @if($owner || $user->show_favorites_public)<a href="#favorites">Favoritos</a>@endif
            @if($owner || $user->show_watching_public)<a href="#watching">Assistindo</a>@endif
            @if($owner || $user->show_completed_public)<a href="#completed">Concluídos</a>@endif
            @if($owner)<a href="#planned">Em espera</a>@endif
            @if($owner || $user->show_reviews_public)<a href="#reviews">Avaliações</a>@endif
        </nav>
        <p data-async-feedback role="status" aria-live="polite"></p><x-validation-summary /><x-list-filters scope="profile" />
        @if(($owner || $user->show_top10_public) && $top10->count())
            <section data-async-collection id="top10" class="mb-10">
                <div class="section-heading"><div><h2><x-icon name="spark" />Top 10</h2><p>As histórias que ficaram com {{ $owner ? 'você' : $user->name }}.</p></div><span class="eyebrow mb-0">FAVORITOS EM ORDEM</span></div>
                <div class="top-grid">@foreach($top10 as $item)<x-profile-anime-card :anime="$item->anime" :meta="$item" :owner="$owner" collection="top10" />@endforeach</div>
            </section>
        @endif
        @if($owner || $user->show_favorites_public)
            <section data-async-collection id="favorites" class="mb-10">
                <div class="section-heading"><div><h2><x-icon name="star" />Favoritos<span class="section-count">{{ $favorites->total() }}</span></h2><p>Aqueles animes para guardar por perto.</p></div></div>
                <div class="favorite-grid">@forelse($favorites as $item)<x-profile-anime-card :anime="$item->anime" :meta="$item" :owner="$owner" :poster="true" collection="favorites" />@empty<div class="col-span-full"><x-empty-state title="Nenhum favorito encontrado" description="Os animes marcados como favoritos aparecem aqui." icon="star" /></div>@endforelse</div>
                <div class="mt-5">{{ $favorites->links() }}</div>
            </section>
        @endif
        @foreach(['watching' => ['Assistindo', $watching, $user->show_watching_public, 'play', 'As histórias que estão acontecendo agora.'], 'completed' => ['Concluídos', $completed, $user->show_completed_public, 'check', 'Jornadas completas, memórias que ficam.']] as $id => [$title, $items, $visible, $icon, $description])
            @if($owner || $visible)
                <section data-async-collection id="{{ $id }}" class="mb-10">
                    <div class="section-heading"><div><h2><x-icon :name="$icon" />{{ $title }}<span class="section-count">{{ $items->total() }}</span></h2><p>{{ $description }}</p></div>@if($owner)<a href="{{ route('personal.animes.index', ['status' => $id === 'watching' ? 'assistindo' : 'concluido']) }}">Gerenciar lista <x-icon name="arrow" class="w-3 h-3" /></a>@endif</div>
                    <div class="horizontal-grid">@forelse($items as $plan)<x-profile-anime-card :anime="$plan->anime" :plan="$plan" :owner="$owner" :meta="$owner ? $plan->anime->userMeta->first() : null" :collection="$id === 'completed' ? 'completed' : null" />@empty<div class="col-span-full"><x-empty-state title="Nenhum anime nesta seção" description="Explore o catálogo ou ajuste os filtros para encontrar mais histórias." :icon="$icon" /></div>@endforelse</div>
                    <div class="mt-5">{{ $items->links() }}</div>
                </section>
            @endif
        @endforeach
        @if($owner)
            <section data-async-collection id="planned" class="mb-10">
                <div class="section-heading"><div><h2><x-icon name="bookmark" />Pretendo assistir<span class="section-count">{{ $planned->total() }}</span></h2><p>Seu próximo capítulo. Esta seção é visível apenas para você.</p></div><a href="{{ route('personal.watch-later.index') }}">Gerenciar minha lista <x-icon name="arrow" class="w-3 h-3" /></a></div>
                <div class="horizontal-grid">@forelse($planned as $plan)<x-plan-card :plan="$plan" route-prefix="personal.animes" />@empty<div class="col-span-full"><x-empty-state title="Sua próxima história está lá fora" description="Guarde animes com Pretendo assistir para encontrá-los aqui." icon="bookmark" /></div>@endforelse</div>
                <div class="mt-5">{{ $planned->links() }}</div>
            </section>
        @endif
        @if($owner || $user->show_reviews_public)
            <section data-async-collection id="reviews">
                <div class="section-heading"><div><h2><x-icon name="star" />Avaliações</h2><p>Impressões sobre cada jornada.</p></div></div>
                <div class="horizontal-grid">@forelse($reviews as $item)<x-profile-anime-card :anime="$item->anime" :meta="$item" />@empty<div class="col-span-full"><x-empty-state title="Ainda sem avaliações" description="As notas e impressões sobre os animes aparecem aqui." /></div>@endforelse</div>
                <div class="mt-5">{{ $reviews->links() }}</div>
            </section>
        @endif
    </div>
</x-app-layout>
