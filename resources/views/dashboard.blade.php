<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- BOAS VINDAS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-2">👋 Bem-vindo ao Anime Tracker</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Organize seus animes, acompanhe seu progresso e registre suas reviews em um só lugar.
                </p>
            </div>

            {{-- CANAL DA TWITCH --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-2 flex items-center">
                    <span class="mr-2">📺</span> Transmissões ao vivo
                </h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Acompanhe nossas lives diretamente no canal:
                    <a href="https://www.twitch.tv/astaroth_frog" target="_blank" class="text-purple-400 hover:text-purple-300 font-medium underline">
                        www.twitch.tv/astaroth_frog
                    </a>
                </p>
            </div>

            {{-- ANIMES ASSISTIDOS NA LIVE NO MOMENTO --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg text-white font-semibold">🔥 Animes assistidos na live no momento</h3>

                    <a href="{{ route('calendar') }}"
                       class="text-sm text-indigo-400 hover:text-indigo-300 transition">
                        Ver calendário
                    </a>
                </div>

                @if(isset($liveWatchingAnimes) && $liveWatchingAnimes->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($liveWatchingAnimes as $plan)
                            <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 flex gap-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ $plan->anime->image }}"
                                         alt="{{ $plan->anime->title }}"
                                         class="w-20 h-28 object-cover rounded-md shadow">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-white font-semibold text-sm mb-2 line-clamp-2">
                                        {{ $plan->anime->title }}
                                    </h4>

                                    <div class="space-y-1 text-xs text-gray-300">
                                        <p>
                                            <span class="text-gray-400">Episódios:</span>
                                            {{ $plan->anime->episodes ?? 'Não definido' }}
                                        </p>

                                        <p>
                                            <span class="text-gray-400">Status:</span>
                                            {{ $plan->anime->anime_status ?? 'Não definido' }}
                                        </p>

                                        <p>
                                            <span class="text-gray-400">Assistidos:</span>
                                            {{ $plan->current_episode_calculated }}
                                        </p>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <a href="{{ route('anime.show', $plan->anime->id) }}"
                                        class="inline-flex items-center justify-center rounded bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 text-xs font-medium transition">
                                            Ver anime
                                        </a>

                                        <form method="POST" action="{{ route('personal.watch-plans.follow', $plan->id) }}" class="w-full">
                                            @csrf
                                            <button type="submit"
                                                class="w-full inline-flex items-center justify-center rounded bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-xs font-medium transition">
                                                Acompanhar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('completed.mark-watched', $plan->anime->id) }}" class="col-span-2 w-full">
                                            @csrf
                                            <button type="submit"
                                                class="w-full inline-flex items-center justify-center rounded bg-green-600 hover:bg-green-700 text-white px-3 py-2 text-xs font-medium transition">
                                                Já assisti
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 text-sm text-gray-300">
                        Nenhum anime em exibição na live no momento.
                    </div>
                @endif
            </div>

            {{-- AÇÕES RÁPIDAS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-4">🚀 Ações rápidas</h3>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('anime.search') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition">
                        🔍 Buscar anime
                    </a>

                    <a href="{{ route('calendar') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition">
                        📅 Ver calendário
                    </a>

                    @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('admin.animes.index') }}"
                           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm transition">
                            ⚙️ Gerenciar animes
                        </a>
                    @endif
                </div>
            </div>

            {{-- SOBRE O SISTEMA --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-3">📌 Sobre o sistema</h3>

                <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                    <li>✔️ Acompanhe episódios assistidos</li>
                    <li>✔️ Crie planos de visualização</li>
                    <li>✔️ Marque animes como concluídos ou pausados</li>
                    <li>✔️ Avalie e escreva reviews</li>
                    <li>✔️ Visualize seu progresso no calendário</li>
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>