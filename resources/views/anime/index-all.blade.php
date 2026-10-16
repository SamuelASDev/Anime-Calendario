<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Todos os Animes</h1>
                <p class="text-sm text-zinc-400 mt-1">
                    Explore o catálogo e procure pelo nome do anime.
                </p>
            </div>

            <a href="{{ route('calendar') }}"
               class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-white text-sm w-full sm:w-auto text-center shrink-0">
                Voltar ao calendário
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-500/20 border border-green-500 text-green-200 p-3 rounded text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('anime.index.all') }}" class="mb-6">
            <div class="max-w-xl">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar anime pelo nome..."
                    class="w-full rounded-xl border border-zinc-700 bg-zinc-900/80 text-white placeholder:text-zinc-500 px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($animes as $anime)
                @php
                    $isWatching = in_array($anime->id, $myWatchingAnimeIds ?? []);
                    $isCompleted = in_array($anime->id, $myCompletedAnimeIds ?? []);
                    $myStatus = $myPlanStatuses[$anime->id] ?? null;
                    $isPlanned = $myStatus === 'pretendo_assistir';
                    $alreadyInList = $myStatus !== null;

                    $averageRating = $anime->public_rating !== null ? number_format($anime->public_rating, 1) : null;
                    $latestReview = $anime->latestPublicReview;
                @endphp

                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <img src="{{ $anime->image }}"
                             alt="{{ $anime->title }}"
                             class="w-28 h-40 sm:w-20 sm:h-28 object-cover rounded shrink-0">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h2 class="text-base sm:text-lg font-semibold break-words leading-snug flex-1 min-w-0">
                                    {{ $anime->title }}
                                </h2>

                                @if($isPlanned)
                                    <span class="shrink-0 px-3 py-1 rounded-md text-xs font-semibold bg-amber-600 text-white">Pretendo assistir</span>
                                @elseif($isCompleted)
                                    <span class="shrink-0 inline-flex items-center justify-center min-w-[92px] px-3 py-1 rounded-md text-xs font-semibold bg-green-600 text-white text-center">
                                        Concluído
                                    </span>
                                @elseif($isWatching)
                                    <span class="shrink-0 inline-flex items-center justify-center min-w-[92px] px-3 py-1 rounded-md text-xs font-semibold bg-blue-600 text-white text-center">
                                        {{ $myStatus === 'pausado' ? 'Pausado' : 'Assistindo' }}
                                    </span>
                                @else
                                    <span class="shrink-0 inline-flex items-center justify-center min-w-[92px] px-3 py-1 rounded-md text-xs font-semibold bg-gray-600 text-white text-center">
                                        Disponível
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-300">
                                Status do anime: {{ $anime->anime_status ?? 'Não definido' }}
                            </p>

                            <p class="text-sm text-gray-300">
                                Episódios totais: {{ $anime->episodes ?? '?' }}
                            </p>

                            <div class="mt-4">
                                <p class="text-sm text-yellow-300">
                                    Média das reviews:
                                    {{ $averageRating ? $averageRating . '/10' : 'Sem notas ainda' }}
                                </p>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @auth
                                    <x-watch-later-button :anime="$anime" :status="$myStatus" />
                                    @if(!$alreadyInList)
                                        <form method="POST" action="{{ route('completed.mark-watched', $anime->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-block bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-white text-sm">
                                                Já assisti
                                            </button>
                                        </form>

                                        <a href="{{ route('personal.watch-plans.create', $anime) }}" class="inline-block bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white text-sm">Acompanhar</a>
                                    @else
                                        @if($isPlanned)
                                            <a href="{{ route('personal.watch-plans.create', $anime) }}" class="inline-block bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-white text-sm">Começar a assistir</a>
                                        @endif
                                        @if($isCompleted)
                                            <a href="{{ route('personal.completed.review.create', $anime->id) }}"
                                               class="inline-block bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded text-white text-sm">
                                                Fazer review
                                            </a>
                                        @endif

                                        <a href="{{ route('anime.show', $anime->id) }}"
                                           class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                                            Mais reviews
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('anime.show', $anime->id) }}"
                                       class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                                        Ver detalhes
                                    </a>
                                @endauth
                            </div>

                            @if($latestReview)
                                <div class="mt-4 border-t border-gray-700 pt-4">
                                    <h3 class="text-sm font-semibold mb-2 text-blue-300">Última review</h3>

                                    <div class="bg-gray-900 border border-gray-700 rounded p-3">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="h-10 w-10 rounded-full overflow-hidden border border-white/10 bg-zinc-800 shrink-0">
                                                @if($latestReview->user->profile_photo_url)
                                                    <img 
                                                        src="{{ $latestReview->user->profile_photo_url }}"
                                                        alt="{{ $latestReview->user->name }}"
                                                        class="h-full w-full object-cover"
                                                    >
                                                @else
                                                    <div class="h-full w-full flex items-center justify-center bg-violet-500 font-bold text-sm">
                                                        {{ strtoupper(substr($latestReview->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <p class="text-sm font-semibold text-white">
                                                {{ $latestReview->user->name }}
                                            </p>
                                        </div>

                                        @if($latestReview->rating)
                                            <p class="text-sm text-yellow-300">
                                                Nota: {{ $latestReview->rating }}/10
                                            </p>
                                        @endif

                                        @if($latestReview->comment)
                                            <p class="text-sm text-gray-300 mt-1 break-words whitespace-pre-line">
                                                {{ $latestReview->comment }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-gray-800 border border-gray-700 rounded-lg p-6">
                    <p class="text-gray-300">Nenhum anime cadastrado ainda.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 overflow-x-auto">
            {{ $animes->links() }}
        </div>
    </div>
</x-app-layout>
