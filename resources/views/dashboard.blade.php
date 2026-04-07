<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- HERO / APRESENTAÇÃO --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="max-w-3xl">
                            <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-3">
                                🎌 Anime Tracker
                            </h3>

                            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                                O Anime Tracker é um sistema para organizar animes, acompanhar progresso de episódios,
                                criar planejamentos de visualização, registrar históricos e salvar reviews em um só lugar.
                            </p>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white mb-1">📺 Live / Global</p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        Veja os animes que estão sendo acompanhados na live, com calendário, histórico
                                        e concluídos globais.
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white mb-1">👤 Meu espaço</p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        Monte seu próprio espaço pessoal para acompanhar animes, concluir, favoritar,
                                        ranquear no Top 10 e fazer reviews.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 min-w-[240px]">
                            <a href="{{ route('anime.search') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 text-sm font-medium transition text-center">
                                🔍 Buscar anime
                            </a>

                            <a href="{{ route('anime.index.all') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 text-sm font-medium transition text-center">
                                📚 Todos os animes
                            </a>

                            <a href="{{ route('personal.calendar') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 text-sm font-medium transition text-center">
                                📅 Meu calendário
                            </a>

                            <a href="{{ route('profile.show', auth()->user()->username) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 text-sm font-medium transition text-center">
                                👤 Meu perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COMO FUNCIONA --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg sm:text-xl text-gray-900 dark:text-white font-semibold mb-4">
                    🧭 Como funciona o sistema
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-base font-semibold text-gray-900 dark:text-white mb-2">1. Encontre um anime</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Use a busca para encontrar um anime e adicionar ele ao seu espaço pessoal.
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-base font-semibold text-gray-900 dark:text-white mb-2">2. Acompanhe seu progresso</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Crie planos, acompanhe episódios, veja histórico, calendário e marque quando concluir.
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-base font-semibold text-gray-900 dark:text-white mb-2">3. Avalie e organize</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Faça reviews, favorite animes, monte seu Top 10 e deixe seu perfil público do jeito que quiser.
                        </p>
                    </div>
                </div>
            </div>

            {{-- RECURSOS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg sm:text-xl text-gray-900 dark:text-white font-semibold mb-4">
                    ✨ O que você pode fazer aqui
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 text-gray-700 dark:text-gray-300">
                        ✔️ Buscar animes e adicionar ao sistema
                    </div>
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 text-gray-700 dark:text-gray-300">
                        ✔️ Criar planos de acompanhamento
                    </div>
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 text-gray-700 dark:text-gray-300">
                        ✔️ Ver calendário global e pessoal
                    </div>
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 text-gray-700 dark:text-gray-300">
                        ✔️ Registrar histórico de episódios assistidos
                    </div>
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 text-gray-700 dark:text-gray-300">
                        ✔️ Marcar animes como pausados ou concluídos
                    </div>
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 text-gray-700 dark:text-gray-300">
                        ✔️ Escrever reviews, favoritar e montar Top 10
                    </div>
                </div>
            </div>

            {{-- TWITCH / LIVE --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg text-gray-900 dark:text-white font-semibold mb-2 flex items-center">
                    <span class="mr-2">📺</span> Transmissões ao vivo
                </h3>

                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                    O sistema também acompanha o que está sendo assistido na live. Você pode ver os animes atuais,
                    acompanhar o calendário global e, se quiser, puxar esses animes para o seu espaço pessoal.
                </p>

                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                    Canal:
                    <a href="https://www.twitch.tv/astaroth_frog" target="_blank" class="text-purple-500 dark:text-purple-400 hover:text-purple-400 dark:hover:text-purple-300 font-medium underline">
                        www.twitch.tv/astaroth_frog
                    </a>
                </p>
            </div>

            {{-- ANIMES DA LIVE --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg text-gray-900 dark:text-white font-semibold">
                            🔥 Animes assistidos na live no momento
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Estes são os animes globais atualmente em acompanhamento.
                        </p>
                    </div>

                    <a href="{{ route('calendar') }}"
                       class="text-sm text-indigo-500 dark:text-indigo-400 hover:text-indigo-400 dark:hover:text-indigo-300 transition">
                        Ver calendário
                    </a>
                </div>

                @if(isset($liveWatchingAnimes) && $liveWatchingAnimes->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($liveWatchingAnimes as $plan)
                            <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex gap-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ $plan->anime->image }}"
                                         alt="{{ $plan->anime->title }}"
                                         class="w-20 h-28 object-cover rounded-md shadow">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-gray-900 dark:text-white font-semibold text-sm mb-2 line-clamp-2">
                                        {{ $plan->anime->title }}
                                    </h4>

                                    <div class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                                        <p>
                                            <span class="text-gray-500 dark:text-gray-400">Episódios:</span>
                                            {{ $plan->anime->episodes ?? 'Não definido' }}
                                        </p>

                                        <p>
                                            <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                            {{ $plan->anime->anime_status ?? 'Não definido' }}
                                        </p>

                                        <p>
                                            <span class="text-gray-500 dark:text-gray-400">Assistidos:</span>
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
                    <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-sm text-gray-600 dark:text-gray-300">
                        Nenhum anime em exibição na live no momento.
                    </div>
                @endif
            </div>

            {{-- AÇÕES RÁPIDAS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg text-gray-900 dark:text-white font-semibold mb-4">🚀 Ações rápidas</h3>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('anime.search') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        🔍 Buscar anime
                    </a>

                    <a href="{{ route('anime.index.all') }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        📚 Todos os animes
                    </a>

                    <a href="{{ route('calendar') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        📅 Ver calendário global
                    </a>

                    <a href="{{ route('personal.calendar') }}"
                       class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        👤 Ver meu calendário
                    </a>

                    @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('admin.animes.index') }}"
                           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
                            ⚙️ Gerenciar animes
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>