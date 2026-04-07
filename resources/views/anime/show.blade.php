<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 text-white">

        <div class="mb-6 flex items-center justify-between gap-3">
            <a href="{{ url()->previous() }}"
               class="text-sm text-gray-400 hover:text-white transition">
                ← Voltar
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-700 bg-green-900/40 px-4 py-3 text-sm text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-800 rounded-lg p-4 sm:p-6 shadow-md border border-gray-700">

            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-shrink-0">
                    <img src="{{ $anime->image }}"
                         alt="{{ $anime->title }}"
                         class="w-48 rounded-lg shadow">
                </div>

                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <h1 class="text-2xl sm:text-3xl font-bold">
                            {{ $anime->title }}
                        </h1>

                        @auth
                            <a href="{{ route('anime.synopsis.edit', $anime->id) }}"
                               class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition">
                                Editar sinopse
                            </a>
                        @endauth
                    </div>

                    <div class="mt-3 space-y-2 text-sm text-gray-300">

                        
                        @if($anime->genres)
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $anime->genres) as $genre)
                                    <span class="px-2 py-1 text-xs rounded bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                        {{ trim($genre) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <p>
                            <span class="text-white font-semibold">Episódios:</span>
                            {{ $anime->episodes ?? 'Não definido' }}
                        </p>

                        <p>
                            <span class="text-white font-semibold">Status:</span>
                            {{ $anime->anime_status ?? 'Não definido' }}
                        </p>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <h2 class="text-lg font-semibold text-indigo-300">
                                Sinopse
                            </h2>
                        </div>

                        <div class="bg-gray-900 border border-gray-700 rounded p-4 text-sm text-gray-300 leading-relaxed whitespace-pre-line">
                            {{ $anime->synopsis ?? 'Sem sinopse disponível.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-700 pt-6">
                <h2 class="text-xl font-bold mb-4 text-blue-300">
                    Reviews
                </h2>

                @php
                    $reviews = $anime->userMeta
                        ->filter(fn ($meta) => $meta->rating || $meta->comment)
                        ->sortByDesc('updated_at');
                @endphp

                @if($reviews->count())
                    <div class="space-y-4">
                        @foreach($reviews as $meta)
                            <div class="bg-gray-900 border border-gray-700 rounded p-4">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full overflow-hidden border border-white/10 bg-zinc-800 shrink-0">
                                            @if($meta->user->profile_photo_url)
                                                <img 
                                                    src="{{ $meta->user->profile_photo_url }}"
                                                    alt="{{ $meta->user->name }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div class="h-full w-full flex items-center justify-center bg-violet-500 font-bold text-sm">
                                                    {{ strtoupper(substr($meta->user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        <p class="font-semibold text-white">
                                            {{ $meta->user->name }}
                                        </p>
                                    </div>

                                    @if($meta->updated_at)
                                        <p class="text-xs text-gray-400">
                                            {{ $meta->updated_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>

                                @if($meta->rating)
                                    <p class="text-sm text-yellow-300 mb-2">
                                        Nota: {{ $meta->rating }}/10
                                    </p>
                                @endif

                                @if($meta->comment)
                                    <p class="text-sm text-gray-300 break-words whitespace-pre-line">
                                        {{ $meta->comment }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-900 border border-gray-700 rounded p-4 text-gray-300">
                        Nenhuma review ainda.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>