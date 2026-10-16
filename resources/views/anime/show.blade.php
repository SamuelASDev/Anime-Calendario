<x-app-layout>
    <x-slot:title>{{ $anime->title }} · Anime Tracker</x-slot:title>
    <div class="page-wrap">
        <div class="flex items-center justify-between mb-6"><a class="btn btn-quiet btn-sm" href="{{ route('anime.index.all') }}">← Voltar ao catálogo</a>@if(auth()->user()->isAdmin())<a class="btn btn-secondary btn-sm" href="{{ route('anime.synopsis.edit', $anime) }}"><x-icon name="settings" />Editar sinopse</a>@endif</div>
        @if(session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <section class="detail-hero">
            @if($anime->image)<img class="detail-poster" src="{{ $anime->image }}" alt="Capa de {{ $anime->title }}" width="300" height="420">@endif
            <div class="detail-info">
                <p class="eyebrow">CADA ANIME, UM NOVO MUNDO</p><h1>{{ $anime->title }}</h1>
                <div class="genre-list">@foreach(array_filter(array_map('trim', explode(',', $anime->genres ?? ''))) as $genre)<a href="{{ route('anime.index.all', ['genre' => $genre]) }}">{{ $genre }}</a>@endforeach</div>
                <div class="detail-meta"><div>Episódios<strong>{{ $anime->episodes ?? 'Ainda não definido' }}</strong></div><div>Exibição<strong>{{ ['Currently Airing' => 'Em exibição', 'Finished Airing' => 'Finalizado', 'Not yet aired' => 'Ainda não estreou'][$anime->anime_status] ?? ($anime->anime_status ?: 'Não informado') }}</strong></div>@if($myPlanStatus)<div>Na sua lista<strong><x-status-badge :status="$myPlanStatus" /></strong></div>@endif</div>
                <div class="hero-actions">
                    @if($myPlanStatus === null || $myPlanStatus === 'pretendo_assistir')<a class="btn btn-primary" href="{{ route('personal.watch-plans.create', $anime) }}"><x-icon name="play" />Começar a assistir</a><x-watch-later-button :anime="$anime" :status="$myPlanStatus" />
                    @elseif($myPlanStatus === 'concluido')<a class="btn btn-primary" href="{{ route('personal.completed.review.create', $anime) }}"><x-icon name="star" />Escrever avaliação</a>
                    @else<a class="btn btn-primary" href="{{ route('personal.animes.index', ['q' => $anime->title]) }}"><x-icon name="book" />Ver na minha biblioteca</a>@endif
                    @if($myPlanStatus !== 'concluido')<form method="POST" action="{{ route('completed.mark-watched', $anime) }}">@csrf<button class="btn btn-secondary"><x-icon name="check" />Já assisti</button></form>@endif
                </div>
                <h2 class="text-sm font-semibold mt-8">Sinopse</h2><p class="detail-synopsis">{{ $anime->synopsis_pt ?: ($anime->synopsis ?: 'Esta história ainda não tem uma sinopse cadastrada.') }}</p>
            </div>
        </section>
        <section id="reviews">
            <div class="section-heading"><div><h2><x-icon name="star" />O que a comunidade achou<span class="section-count">{{ $reviews->total() }}</span></h2><p>Notas e impressões de quem viveu essa história.</p></div></div>
            <div class="horizontal-grid">@forelse($reviews as $meta)<article class="review-card"><div class="review-header">@if($meta->user->profile_photo_url)<img src="{{ $meta->user->profile_photo_url }}" alt="">@else<span class="review-avatar">{{ mb_strtoupper(mb_substr($meta->user->name, 0, 1)) }}</span>@endif<div><a class="review-user" href="{{ $meta->user->username ? route('profile.show', $meta->user->username) : route('users.index') }}">{{ $meta->user->name }}</a><div class="review-date">{{ $meta->updated_at->format('d/m/Y') }}</div></div>@if($meta->rating)<span class="status-tag ml-auto text-amber-200"><x-icon name="star" class="w-3 h-3" />{{ $meta->rating }}/10</span>@endif</div>@if($meta->comment)<p>{{ $meta->comment }}</p>@endif</article>@empty<div class="col-span-full"><x-empty-state title="Essa história ainda não tem avaliações" description="Depois de concluir o anime, compartilhe suas impressões com a comunidade." icon="star" /></div>@endforelse</div>
            <div class="mt-7">{{ $reviews->links() }}</div>
        </section>
    </div>
</x-app-layout>
