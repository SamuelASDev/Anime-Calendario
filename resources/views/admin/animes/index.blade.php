<x-app-layout>
    <div x-data="{ pausedOpen: false, completedOpen: false }" class="max-w-6xl mx-auto p-4 sm:p-6 text-white">
        <h1 class="text-xl sm:text-2xl font-bold mb-6">Painel Admin - Animes</h1>

        @if (session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- EM ANDAMENTO --}}
        <h2 class="text-lg sm:text-xl font-semibold mb-4 text-green-300">Em andamento</h2>

        <div class="space-y-4 mb-6">
            @forelse ($plans as $plan)
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row gap-4">

                        <img src="{{ $plan->anime->image }}"
                             alt="{{ $plan->anime->title }}"
                             class="w-28 h-40 sm:w-20 sm:h-28 object-cover rounded shrink-0">

                        <div class="flex-1 min-w-0">
                            <h2 class="text-base sm:text-xl font-semibold break-words">
                                {{ $plan->anime->title }}
                            </h2>

                            <p class="text-sm text-gray-300">
                                Anime: {{ $plan->anime->anime_status }}
                            </p>

                            <p class="text-sm text-gray-300">
                                Progresso atual: episódio {{ $plan->current_episode_calculated }}
                            </p>

                            <p class="text-sm text-gray-400">
                                Base salva: episódio {{ $plan->episodes_watched }}
                            </p>

                            <p class="text-sm text-gray-400">
                                Logs: {{ $plan->logs->sum('episodes_watched_today') }}
                            </p>

                            <p class="text-sm text-gray-400">
                                Status: {{ $plan->watch_status }}
                            </p>

                            <div class="mt-4 grid grid-cols-1 sm:flex sm:flex-wrap gap-2">

                                <a href="{{ route('admin.animes.edit', $plan->id) }}"
                                   class="bg-yellow-600 hover:bg-yellow-700 px-3 py-2 rounded text-sm text-center">
                                    Editar
                                </a>

                                <a href="{{ route('admin.animes.log.create', $plan->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded text-sm text-center">
                                    Registrar hoje
                                </a>

                                <form method="POST" action="{{ route('admin.animes.pause', $plan->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-orange-600 hover:bg-orange-700 px-3 py-2 rounded text-sm w-full sm:w-auto">
                                        ⏸ Pausar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.animes.complete', $plan->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-green-600 hover:bg-green-700 px-3 py-2 rounded text-sm w-full sm:w-auto">
                                        ✔ Concluir
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.animes.destroy', $plan->id) }}"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este anime?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="bg-red-600 hover:bg-red-700 px-3 py-2 rounded text-sm w-full sm:w-auto">
                                        Excluir
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                    <p class="text-gray-300">Nenhum anime em andamento.</p>
                </div>
            @endforelse
        </div>

        <div class="mb-10 overflow-x-auto">
            {{ $plans->appends(request()->except('active_page'))->links() }}
        </div>

        {{-- PAUSADOS --}}
        <div class="mb-4">
            <button
                @click="pausedOpen = !pausedOpen"
                class="w-full flex items-center justify-between bg-gray-800 border border-gray-700 rounded-lg p-4 text-left"
            >
                <span class="text-lg sm:text-xl font-semibold text-yellow-300">
                    Pausados
                </span>
                <span x-text="pausedOpen ? '−' : '+'"></span>
            </button>
        </div>

        <div x-show="pausedOpen" x-transition class="space-y-4 mb-10">
            @forelse ($pausedPlans as $plan)
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 opacity-90">
                    <div class="flex flex-col sm:flex-row gap-4">

                        <img src="{{ $plan->anime->image }}"
                             alt="{{ $plan->anime->title }}"
                             class="w-28 h-40 sm:w-20 sm:h-28 object-cover rounded shrink-0">

                        <div class="flex-1 min-w-0">
                            <h2 class="text-base sm:text-xl font-semibold break-words">
                                {{ $plan->anime->title }}
                            </h2>

                            <p class="text-sm text-gray-300">
                                Anime: {{ $plan->anime->anime_status }}
                            </p>

                            <p class="text-sm text-gray-300">
                                Pausado no episódio {{ $plan->current_episode_calculated }}
                            </p>

                            <p class="text-sm text-gray-400">
                                Status: {{ $plan->watch_status }}
                            </p>

                            <div class="mt-4 grid grid-cols-1 sm:flex gap-2">

                                <form method="POST" action="{{ route('admin.animes.resume', $plan->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button class="bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded text-sm w-full sm:w-auto">
                                        ▶ Retomar
                                    </button>
                                </form>

                                <a href="{{ route('admin.animes.edit', $plan->id) }}"
                                class="bg-yellow-600 hover:bg-yellow-700 px-3 py-2 rounded text-sm text-center">
                                    Editar
                                </a>

                                <form method="POST" action="{{ route('admin.animes.destroy', $plan->id) }}"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este anime?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="bg-red-600 hover:bg-red-700 px-3 py-2 rounded text-sm w-full sm:w-auto">
                                        Excluir
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                    <p class="text-gray-300">Nenhum anime pausado.</p>
                </div>
            @endforelse

            <div class="overflow-x-auto">
                {{ $pausedPlans->appends(request()->except('paused_page'))->links() }}
            </div>
        </div>

        {{-- CONCLUÍDOS --}}
        <div class="mb-4">
            <button
                @click="completedOpen = !completedOpen"
                class="w-full flex items-center justify-between bg-gray-800 border border-gray-700 rounded-lg p-4 text-left"
            >
                <span class="text-lg sm:text-xl font-semibold text-blue-300">
                    Concluídos
                </span>
                <span x-text="completedOpen ? '−' : '+'"></span>
            </button>
        </div>

        <div x-show="completedOpen" x-transition class="space-y-4">
            @forelse ($completedPlans as $plan)
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 opacity-80">
                    <div class="flex flex-col sm:flex-row gap-4">

                        <img src="{{ $plan->anime->image }}"
                             alt="{{ $plan->anime->title }}"
                             class="w-28 h-40 sm:w-20 sm:h-28 object-cover rounded shrink-0">

                        <div class="flex-1 min-w-0">
                            <h2 class="text-base sm:text-xl font-semibold break-words">
                                {{ $plan->anime->title }}
                            </h2>

                            <p class="text-sm text-gray-300">
                                Anime: {{ $plan->anime->anime_status }}
                            </p>

                            <p class="text-sm text-gray-300">
                                Finalizado no episódio {{ $plan->current_episode_calculated }}
                            </p>

                            <p class="text-sm text-gray-400">
                                Status: {{ $plan->watch_status }}
                            </p>
                            
                            <div class="mt-4 grid grid-cols-1 sm:flex gap-2">

                                <a href="{{ route('admin.animes.edit', $plan->id) }}"
                                class="bg-yellow-600 hover:bg-yellow-700 px-3 py-2 rounded text-sm text-center">
                                    Ver / Editar
                                </a>

                                <form method="POST" action="{{ route('admin.animes.destroy', $plan->id) }}"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este anime?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="bg-red-600 hover:bg-red-700 px-3 py-2 rounded text-sm w-full sm:w-auto">
                                        Excluir
                                    </button>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                    <p class="text-gray-300">Nenhum anime concluído ainda.</p>
                </div>
            @endforelse

            <div class="overflow-x-auto">
                {{ $completedPlans->appends(request()->except('completed_page'))->links() }}
            </div>
        </div>
    </div>
</x-app-layout>