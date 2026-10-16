@props(['anime', 'meta' => null, 'plan' => null, 'owner' => false, 'poster' => false, 'collection' => null])

<article class="anime-card {{ $poster ? 'poster-card favorite-card' : 'profile-list-card' }}" data-anime-card>
    <div class="{{ $poster ? 'poster-wrap' : 'plan-cover' }}">
        @if ($anime->image)
            <img loading="lazy" src="{{ $anime->image }}" alt="Capa de {{ $anime->title }}">
        @endif
    </div>

    @if ($meta?->top_position && $collection === 'top10')
        <span class="rank-badge">#{{ $meta->top_position }}</span>
    @endif

    <div class="{{ $poster ? 'card-body' : 'plan-content' }}">
        <h3 class="card-title">
            <a href="{{ route('anime.show', $anime) }}" class="card-link">{{ $anime->title }}</a>
        </h3>
        @if ($plan)
            <div><x-status-badge :status="$plan->watch_status" /></div>
        @endif
        @if ($meta?->rating)
            <p class="text-xs text-amber-300 flex items-center gap-1">
                <x-icon name="star" class="w-3 h-3" />{{ $meta->rating }}/10
            </p>
        @endif
        @if ($meta?->comment && ! $poster)
            <p class="card-note line-clamp-2">{{ $meta->comment }}</p>
        @endif

        <div class="card-actions">
            @if ($owner)
                <form data-collection-action method="POST" action="{{ route('personal.animes.favorite', $anime) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-secondary btn-sm" aria-pressed="{{ $meta?->is_favorite ? 'true' : 'false' }}">
                        <x-icon name="star" />{{ $meta?->is_favorite ? 'Favorito' : 'Favoritar' }}
                    </button>
                </form>

                @if (in_array($collection, ['favorites', 'completed']) || ($collection === 'top10' && $meta?->top_position))
                    @foreach (['up', 'down'] as $direction)
                        <x-collection-order-button :anime="$anime" :collection="$collection" :direction="$direction" />
                    @endforeach
                @endif

                @if ($meta?->top_position && $collection === 'top10')
                    <form data-collection-action method="POST" action="{{ route('personal.animes.removeTop', $anime) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-danger btn-sm" title="Remover do Top 10" aria-label="Remover {{ $anime->title }} do Top 10">
                            <x-icon name="close" />
                        </button>
                    </form>
                @elseif (! $meta?->top_position)
                    <form data-collection-action method="POST" action="{{ route('personal.animes.addTop', $anime) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-quiet btn-sm"><x-icon name="plus" />Top 10</button>
                    </form>
                @endif
            @else
                <x-watch-later-button :anime="$anime" />
                @if ($plan)
                    @can('copy', $plan)
                        <form method="POST" action="{{ route('personal.watch-plans.follow', $plan) }}">
                            @csrf
                            <button class="btn btn-secondary btn-sm">Copiar agenda</button>
                        </form>
                    @endcan
                @endif
            @endif
        </div>
    </div>
</article>
