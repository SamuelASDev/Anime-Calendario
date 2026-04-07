<x-app-layout>
    <div class="p-4 sm:p-6">

        @if (session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('anime.search') }}">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Buscar anime..."
                class="w-full mb-4 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-black dark:text-white placeholder-gray-400 px-4 py-3 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($results as $anime)
                <div class="bg-gray-800 border border-gray-700 p-3 sm:p-2 rounded text-white hover:scale-[1.02] sm:hover:scale-105 transition">

                    <div class="overflow-hidden rounded">
                        <img
                            src="{{ $anime['images']['jpg']['image_url'] }}"
                            alt="{{ $anime['title'] }}"
                            class="w-full h-48 sm:h-32 md:h-36 object-cover"
                        >
                    </div>

                    <h2 class="font-semibold text-sm sm:text-xs md:text-sm mt-2 line-clamp-2 min-h-[2.5rem]">
                        {{ $anime['title'] }}
                    </h2>

                    <p class="text-xs sm:text-[10px] text-gray-300 mt-1">
                        Ep: {{ $anime['episodes'] ?? '?' }}
                    </p>

                    <p class="text-xs sm:text-[10px] text-gray-400 line-clamp-1">
                        {{ $anime['status'] }}
                    </p>

                    <div class="mt-2 space-y-2">
                        @if(auth()->user()?->role === 'admin')
                            <form method="POST" action="{{ route('anime.store') }}">
                                @csrf
                                <input type="hidden" name="mal_id" value="{{ $anime['mal_id'] }}">
                                <input type="hidden" name="title" value="{{ $anime['title'] }}">
                                <input type="hidden" name="episodes" value="{{ $anime['episodes'] }}">
                                <input type="hidden" name="synopsis" value="{{ $anime['synopsis'] }}">
                                <input type="hidden" name="status" value="{{ $anime['status'] }}">
                                <input type="hidden" name="image" value="{{ $anime['images']['jpg']['image_url'] }}">
                                <input type="hidden" name="genres" value="{{ collect($anime['genres'] ?? [])->pluck('name')->implode(', ') }}">
                                <input type="hidden" name="target" value="global">

                                <button class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-2 rounded w-full">
                                    ➕ Adicionar ao geral
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('anime.store') }}">
                            @csrf
                            <input type="hidden" name="mal_id" value="{{ $anime['mal_id'] }}">
                            <input type="hidden" name="title" value="{{ $anime['title'] }}">
                            <input type="hidden" name="episodes" value="{{ $anime['episodes'] }}">
                            <input type="hidden" name="synopsis" value="{{ $anime['synopsis'] }}">
                            <input type="hidden" name="status" value="{{ $anime['status'] }}">
                            <input type="hidden" name="image" value="{{ $anime['images']['jpg']['image_url'] }}">
                            <input type="hidden" name="genres" value="{{ collect($anime['genres'] ?? [])->pluck('name')->implode(', ') }}">
                            <input type="hidden" name="target" value="personal">

                            <button class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-3 py-2 rounded w-full">
                                👤 Adicionar ao meu espaço
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-app-layout>