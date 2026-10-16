@props(['anime', 'status' => null])
<article {{ $attributes->class(['anime-card', 'poster-card']) }} data-anime-card>
    <div class="poster-wrap">
        @if($anime->image)<img loading="lazy" src="{{ $anime->image }}" alt="Capa de {{ $anime->title }}" width="300" height="400">@else<div class="h-full flex items-center justify-center text-violet-300"><x-icon name="book" class="w-12 h-12" /></div>@endif
        <div class="poster-gradient"></div>
        @if($status)<div class="poster-badge"><x-status-badge :status="$status" /></div>@endif
        @if($anime->public_rating !== null)<span class="rating-tag"><x-icon name="star" />{{ number_format($anime->public_rating, 1) }}</span>@endif
    </div>
    <div class="card-body">
        <h3 class="card-title"><a href="{{ route('anime.show', $anime) }}" class="card-link">{{ $anime->title }}</a></h3>
        <p class="card-subtitle">{{ $anime->episodes ? $anime->episodes.' episódios' : 'Total indefinido' }}<span aria-hidden="true"> · </span>{{ ['Currently Airing' => 'Em exibição', 'Finished Airing' => 'Finalizado', 'Not yet aired' => 'Em breve'][$anime->anime_status] ?? ($anime->anime_status ?: 'Anime') }}</p>
        @if(isset($actions))<div class="card-actions">{{ $actions }}</div>@endif
        {{ $slot }}
    </div>
</article>
