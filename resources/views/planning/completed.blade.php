<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white space-y-6">
        <h1 class="text-2xl font-bold">{{ $personal ? 'Meus animes concluídos' : 'Concluídos da comunidade' }}</h1>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($plans as $plan)
                @php($anime = $plan->anime)
                @php($myMeta = $anime->userMeta->first())
                <article class="rounded-xl border border-gray-700 bg-gray-800 p-5 space-y-4">
                    <div class="flex gap-4">
                        <img loading="lazy" src="{{ $anime->image }}" alt="{{ $anime->title }}" class="w-20 h-28 rounded-lg object-cover">
                        <div>
                            <a class="font-semibold hover:text-indigo-300" href="{{ route('anime.show', $anime) }}">{{ $anime->title }}</a>
                            <p class="text-sm text-gray-300 mt-2">Concluído · {{ $plan->current_episode_calculated }} episódios</p>
                            <p class="text-sm text-yellow-300 mt-2">Média pública: {{ $anime->public_rating !== null ? number_format($anime->public_rating, 1).'/10' : 'Sem notas' }}</p>
                        </div>
                    </div>
                    @if(in_array($anime->id, $myCompletedAnimeIds))
                        <a class="inline-block rounded bg-indigo-600 px-3 py-2 text-sm" href="{{ route('personal.completed.review.create', $anime) }}">Escrever avaliação</a>
                    @else
                        <x-watch-later-button :anime="$anime" />
                        <form method="POST" action="{{ route('completed.mark-watched', $anime) }}">@csrf <button class="rounded bg-green-700 px-3 py-2 text-sm">Já assisti</button></form>
                    @endif
                    @if($personal)
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('personal.animes.favorite', $anime) }}">@csrf @method('PATCH') <button class="text-sm text-yellow-300">{{ $myMeta?->is_favorite ? '★ Favorito' : '☆ Favoritar' }}</button></form>
                            <form method="POST" action="{{ route('personal.animes.addTop', $anime) }}">@csrf @method('PATCH') <button class="text-sm text-indigo-300">Adicionar ao Top 10</button></form>
                        </div>
                        @if($myMeta?->comment)<p class="text-sm text-gray-300 whitespace-pre-line">{{ $myMeta->comment }}</p>@endif
                    @elseif($anime->latestPublicReview)
                        <div class="border-t border-gray-700 pt-3 text-sm">
                            <p class="font-semibold">{{ $anime->latestPublicReview->user->name }}</p>
                            <p class="text-gray-300 whitespace-pre-line">{{ $anime->latestPublicReview->comment }}</p>
                        </div>
                    @endif
                </article>
            @empty
                <p class="text-gray-400">Nenhum anime concluído ainda.</p>
            @endforelse
        </div>
        {{ $plans->links() }}
    </div>
</x-app-layout>
