<x-app-layout>
    <div class="page-wrap">
        <x-page-heading eyebrow="DESCUBRA SUA PRÓXIMA HISTÓRIA" title="Explorar animes" description="Um catálogo de possibilidades. Encontre algo para começar hoje ou guardar para depois.">
            <x-slot:actions><a class="btn btn-secondary" href="{{ route('anime.search') }}"><x-icon name="search" />Buscar novos animes</a></x-slot:actions>
        </x-page-heading>
        @if(session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <x-validation-summary />
        <x-list-filters scope="catalog" :statuses="true" />
        <div class="section-heading"><h2>Catálogo <span class="section-count">{{ $animes->total() }} animes</span></h2><a href="{{ route('personal.animes.index') }}">Minha lista <x-icon name="arrow" class="w-3 h-3" /></a></div>
        <div class="anime-grid">
            @forelse($animes as $anime)
                @php($status = $myPlanStatuses[$anime->id] ?? null)
                <x-anime-poster :anime="$anime" :status="$status">
                    <x-slot:actions>
                        @if($status === null || $status === 'pretendo_assistir')
                            <a class="btn btn-primary btn-sm" href="{{ route('personal.watch-plans.create', $anime) }}"><x-icon name="play" />{{ $status === 'pretendo_assistir' ? 'Começar a assistir' : 'Acompanhar' }}</a>
                            <x-watch-later-button :anime="$anime" :status="$status" />
                        @elseif($status === 'concluido')
                            <a class="btn btn-secondary btn-sm" href="{{ route('personal.completed.review.create', $anime) }}"><x-icon name="star" />Avaliar anime</a>
                        @else
                            <a class="btn btn-secondary btn-sm" href="{{ route('personal.animes.index', ['q' => $anime->title]) }}">Na minha lista <x-icon name="arrow" /></a>
                        @endif
                    </x-slot:actions>
                </x-anime-poster>
            @empty
                <div class="col-span-full"><x-empty-state title="Nenhum anime encontrado" description="Tente outro título ou ajuste os filtros para descobrir mais histórias."><a class="btn btn-secondary" href="{{ route('anime.index.all') }}">Limpar filtros</a></x-empty-state></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $animes->links() }}</div>
    </div>
</x-app-layout>
