<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <h1 class="text-xl sm:text-2xl font-bold">Animes Concluídos</h1>

            @if (session('success'))
                <div class="bg-green-500/20 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <a href="{{ route('calendar') }}"
               class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white text-sm w-full sm:w-auto text-center">
                Voltar ao calendário
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($plans as $plan)
                @php
                    $totalLogged = $plan->logs->sum('episodes_watched_today');
                    $finalEpisode = $plan->episodes_watched + $totalLogged;
                @endphp

                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <img src="{{ $plan->anime->image }}"
                             alt="{{ $plan->anime->title }}"
                             class="w-28 h-40 sm:w-20 sm:h-28 object-cover rounded shrink-0">

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
                                <h2 class="text-base sm:text-lg font-semibold break-words">
                                    {{ $plan->anime->title }}
                                </h2>

                                <span class="text-xs bg-green-600 px-2 py-1 rounded w-fit">
                                    Concluído
                                </span>
                            </div>

                            <p class="text-sm text-gray-300">
                                Status do anime: {{ $plan->anime->anime_status }}
                            </p>

                            <p class="text-sm text-gray-300">
                                Episódios totais: {{ $plan->anime->episodes ?? '?' }}
                            </p>

                            <p class="text-sm text-gray-400 break-words">
                                Episódio final registrado: {{ $finalEpisode }}
                            </p>

                            @php
                                $reviewsWithRating = $plan->anime->userMeta->whereNotNull('rating');
                                $averageRating = $reviewsWithRating->count()
                                    ? number_format($reviewsWithRating->avg('rating'), 1)
                                    : null;
                            @endphp

                            <div class="mt-4">
                                <p class="text-sm text-yellow-300">
                                    Média das reviews:
                                    {{ $averageRating ? $averageRating . '/10' : 'Sem notas ainda' }}
                                </p>
                            </div>
                            
                            <div class="mt-4">
                                @if(in_array($plan->anime->id, $myCompletedAnimeIds ?? []))
                                    <a href="{{ route('completed.review.create', $plan->anime->id) }}"
                                    class="inline-block bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded text-white text-sm">
                                        Fazer review
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('completed.mark-watched', $plan->anime->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-block bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-white text-sm">
                                            Já assisti
                                        </button>
                                    </form>
                                @endif
                            </div>

                        @php
                            $latestReview = $plan->anime->userMeta
                                ->filter(fn ($meta) => $meta->rating || $meta->comment)
                                ->sortByDesc('updated_at')
                                ->first();
                        @endphp

                        @if($latestReview)
                            <div class="mt-4 border-t border-gray-700 pt-4">
                                <h3 class="text-sm font-semibold mb-2 text-blue-300">Última review</h3>

                                <div class="bg-gray-900 border border-gray-700 rounded p-3">
                                    <p class="text-sm font-semibold text-white">
                                        {{ $latestReview->user->name }}
                                    </p>

                                    @if($latestReview->rating)
                                        <p class="text-sm text-yellow-300">
                                            Nota: {{ $latestReview->rating }}/10
                                        </p>
                                    @endif

                                    @if($latestReview->comment)
                                        <p class="text-sm text-gray-300 mt-1 break-words">
                                            {{ $latestReview->comment }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-3">
                                    <a href="{{ route('anime.show', $plan->anime->id) }}"
                                    class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                                        Mais reviews
                                    </a>
                                </div>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-gray-800 border border-gray-700 rounded-lg p-6">
                    <p class="text-gray-300">Nenhum anime concluído ainda.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 overflow-x-auto">
            {{ $plans->links() }}
        </div>
    </div>
</x-app-layout>