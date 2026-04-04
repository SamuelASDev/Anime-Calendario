<x-app-layout>
    <div class="max-w-6xl mx-auto p-6 text-white">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Meu Calendário</h1>




            <div class="flex gap-3">
                <a href="{{ route('personal.history') }}"
                class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded text-white text-sm">
                    Ver histórico
                </a>

                <a href="{{ route('anime.search') }}"
                class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white text-sm">
                    Buscar animes
                </a>
            </div>
        </div>

        @forelse ($calendarData as $date => $items)
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 mb-6">

                <h2 class="text-xl font-semibold mb-4 text-blue-300">
                    {{ $date }}
                </h2>

                <div class="space-y-3">

                    @foreach ($items as $item)
                        <div class="flex items-center gap-4 bg-gray-900 border border-gray-700 rounded-lg p-3">

                            <img src="{{ $item['anime_image'] }}"
                                 alt="{{ $item['anime_title'] }}"
                                 class="w-14 h-20 object-cover rounded">

                            <div class="flex-1">

                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-white">
                                        {{ $item['anime_title'] }}
                                    </h3>

                                    {{-- BADGE --}}
                                    @if($item['type'] === 'logged')
                                        <span class="text-xs bg-green-600 px-2 py-1 rounded">
                                            ✔ Real
                                        </span>
                                    @elseif($item['type'] === 'variable')
                                        <span class="text-xs bg-yellow-600 px-2 py-1 rounded">
                                            ⚠ A definir
                                        </span>
                                    @else
                                        <span class="text-xs bg-gray-600 px-2 py-1 rounded">
                                            Previsto
                                        </span>
                                    @endif
                                </div>

                                {{-- TEXTO PRINCIPAL --}}
                                <p class="text-sm mt-1
                                    @if($item['type'] === 'logged') text-green-300
                                    @elseif($item['type'] === 'variable') text-yellow-300
                                    @else text-gray-300
                                    @endif
                                ">
                                    {{ $item['label'] }}
                                </p>

                                {{-- OBSERVAÇÃO --}}
                                @if(!empty($item['notes']))
                                    <p class="text-xs text-gray-400 mt-1 italic">
                                        💬 {{ $item['notes'] }}
                                    </p>
                                @endif

                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

        @empty
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
                <p class="text-gray-300">Nenhum plano encontrado ainda.</p>
            </div>
        @endforelse

    </div>
</x-app-layout>