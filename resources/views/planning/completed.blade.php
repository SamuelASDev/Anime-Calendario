<x-app-layout>
    <div class="page-wrap">
        <x-page-heading :eyebrow="$personal ? 'CAPÍTULOS COMPLETOS' : 'ASSISTIDOS EM COMUNIDADE'" :title="$personal ? 'Meus animes concluídos' : 'Concluídos da comunidade'" description="Uma coleção de histórias vividas. Conte o que achou e descubra o que assistir depois.">
            <x-slot:actions><a class="btn btn-secondary" href="{{ route($personal ? 'personal.animes.index' : 'calendar') }}"><x-icon name="arrow" />{{ $personal ? 'Minha biblioteca' : 'Ver calendário' }}</a></x-slot:actions>
        </x-page-heading>
        @if(session('success'))<p role="status" class="flash">{{ session('success') }}</p>@endif
        <x-validation-summary /><x-list-filters />
        <div class="section-heading"><h2><x-icon name="check" />Coleção concluída<span class="section-count">{{ $plans->total() }}</span></h2></div>
        <div class="anime-grid">
            @forelse($plans as $plan)
                @php($anime = $plan->anime)
                @php($myMeta = $anime->userMeta->first())
                <x-anime-poster :anime="$anime" status="concluido">
                    <x-slot:actions>
                        @if(in_array($anime->id, $myCompletedAnimeIds))<a class="btn btn-primary btn-sm" href="{{ route('personal.completed.review.create', $anime) }}"><x-icon name="star" />Avaliar anime</a>
                        @else<x-watch-later-button :anime="$anime" /><form method="POST" action="{{ route('completed.mark-watched', $anime) }}">@csrf<button class="btn btn-secondary btn-sm">Já assisti</button></form>@endif
                        @if($personal)<form method="POST" action="{{ route('personal.animes.favorite', $anime) }}">@csrf @method('PATCH')<button class="btn btn-quiet btn-sm" aria-pressed="{{ $myMeta?->is_favorite ? 'true' : 'false' }}"><x-icon name="star" />{{ $myMeta?->is_favorite ? 'Favorito' : 'Favoritar' }}</button></form><form method="POST" action="{{ route('personal.animes.addTop', $anime) }}">@csrf @method('PATCH')<button class="btn btn-quiet btn-sm"><x-icon name="plus" />Top 10</button></form>@endif
                    </x-slot:actions>
                    @if($personal && $myMeta?->comment)<p class="card-note line-clamp-2 mt-3">{{ $myMeta->comment }}</p>@endif
                </x-anime-poster>
            @empty<div class="col-span-full"><x-empty-state title="Nenhum anime encontrado" description="Os animes concluídos aparecem aqui. Você pode ajustar os filtros ou continuar uma história." icon="check" /></div>@endforelse
        </div>
        <div class="mt-8">{{ $plans->links() }}</div>
    </div>
</x-app-layout>
