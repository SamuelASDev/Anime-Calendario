<x-app-layout>
    <div class="page-wrap">
        <x-page-heading eyebrow="EXPANDA SUA COLEÇÃO" title="Buscar novos animes"
            description="Explore títulos do MyAnimeList e traga suas próximas escolhas para a biblioteca."><x-slot:actions><a class="btn btn-secondary" href="{{ route('anime.index.all') }}">Voltar ao
                    catálogo</a></x-slot:actions></x-page-heading>
        @if (session('success'))
            <p class="flash" role="status">{{ session('success') }}</p>
        @endif
        <x-validation-summary />
        @if ($searchError ?? null)
            <p role="alert" class="flash border-amber-500/30 text-amber-200">{{ $searchError }}</p>
        @endif
        <form method="GET" class="filter-panel" role="search">
            <div class="filter-primary">
                <div class="search-field"><x-icon name="search" /><input type="search" name="q"
                        value="{{ request('q') }}" maxlength="200" placeholder="Qual história você procura?"
                        aria-label="Título do anime" required></div><button class="btn btn-primary"><x-icon
                        name="search" />Buscar animes</button>
            </div>
        </form>
        @if ($results)
            <div class="section-heading">
                <h2>Resultados para “{{ request('q') }}”</h2><span class="text-xs text-slate-500">Fonte: {{ $catalogSource }}</span>
            </div>
        @endif
        <div class="anime-grid">
            @forelse($results as $anime)
                @php($localId = $localAnimes[$anime->malId] ?? null)
                <article class="anime-card poster-card" data-anime-card>
                    <div class="poster-wrap"><img loading="lazy" src="{{ $anime->imageUrl }}"
                            alt="Capa de {{ $anime->title }}">
                        <div class="poster-gradient"></div>
                    </div>
                    <div class="card-body">
                        <h2 class="card-title">
                            @if ($localId)
                                <a class="card-link"
                                href="{{ route('anime.show', $localId) }}">{{ $anime->title }}</a>@else<a
                                    class="card-link" href="https://myanimelist.net/anime/{{ (int) $anime->malId }}"
                                    target="_blank" rel="noopener noreferrer">{{ $anime->title }}</a>
                            @endif
                        </h2>
                        <p class="card-subtitle">{{ $anime->episodes ?? '?' }}
                            episódios{{ $localId ? ' · No catálogo' : ' · Ver no MyAnimeList ↗' }}</p>
                        <div class="card-actions"><x-anime-import-form :anime="$anime"
                                target="personal" /><x-anime-import-form :anime="$anime" target="later" />
                            @if (auth()->user()->isAdmin())
                                <x-anime-import-form :anime="$anime" target="global" />
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                @if (!$searchError)
                    <div class="col-span-full"><x-empty-state :title="request('q') ? 'Nenhum anime encontrado' : 'Sua próxima descoberta começa por um nome'" :description="request('q')
                        ? 'Tente outra grafia ou o título original do anime.'
                        : 'Digite um título para buscar novas histórias no MyAnimeList.'" icon="search" />
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</x-app-layout>
