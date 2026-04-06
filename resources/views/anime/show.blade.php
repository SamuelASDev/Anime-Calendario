<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 text-white">

        <div class="mb-6">
            <a href="{{ url()->previous() }}"
               class="text-sm text-gray-400 hover:text-white">
                ← Voltar
            </a>
        </div>

        <div class="bg-gray-800 rounded-lg p-4 sm:p-6 shadow-md border border-gray-700">

            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-shrink-0">
                    <img src="{{ $anime->image }}"
                         alt="{{ $anime->title }}"
                         class="w-48 rounded-lg shadow">
                </div>

                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-3">
                        {{ $anime->title }}
                    </h1>

                    <div class="space-y-2 text-sm text-gray-300">
                        <p><span class="text-white font-semibold">Episódios:</span> {{ $anime->episodes ?? 'Não definido' }}</p>
                        <p><span class="text-white font-semibold">Status:</span> {{ $anime->anime_status ?? 'Não definido' }}</p>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-lg font-semibold mb-2 text-indigo-300">
                            Sinopse
                        </h2>

                        <div class="bg-gray-900 border border-gray-700 rounded p-4 text-sm text-gray-300 leading-relaxed">
                            {{ $anime->synopsis_pt ?? $anime->synopsis ?? 'Sem sinopse disponível.' }}
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
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <p class="font-semibold text-white">
                                        {{ $meta->user->name }}
                                    </p>

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
                                    <p class="text-sm text-gray-300 break-words">
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