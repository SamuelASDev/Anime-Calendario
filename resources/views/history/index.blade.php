<x-app-layout>
    <div class="max-w-6xl mx-auto p-6 text-white">

        @if (session('success'))
            <div class="mb-6 bg-green-600/20 border border-green-500 text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Histórico de episódios</h1>

            <div class="flex gap-3">
                <a href="{{ route('calendar') }}"
                   class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                    Ver calendário
                </a>

                <a href="{{ route('anime.search') }}"
                   class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white text-sm">
                    Buscar animes
                </a>
            </div>
        </div>

        @forelse ($historyData as $date => $items)
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

                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-white">
                                        {{ $item['anime_title'] }}
                                    </h3>

                                    @if($item['type'] === 'logged')
                                        <span class="text-xs bg-green-600 px-2 py-1 rounded">
                                            ✔ Confirmado
                                        </span>
                                    @elseif($item['type'] === 'missed')
                                        <span class="text-xs bg-red-600 px-2 py-1 rounded">
                                            ✖ Não assistimos
                                        </span>
                                    @elseif($item['type'] === 'variable')
                                        <span class="text-xs bg-yellow-600 px-2 py-1 rounded">
                                            ⚠ A definir
                                        </span>
                                    @else
                                        <span class="text-xs bg-gray-600 px-2 py-1 rounded">
                                            Pendente
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm mt-1
                                    @if($item['type'] === 'logged') text-green-300
                                    @elseif($item['type'] === 'missed') text-red-300
                                    @elseif($item['type'] === 'variable') text-yellow-300
                                    @else text-gray-300
                                    @endif
                                ">
                                    {{ $item['label'] }}
                                </p>

                                @if(!empty($item['notes']))
                                    <p class="text-xs text-gray-400 mt-1 italic">
                                        💬 {{ $item['notes'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col gap-2 min-w-[170px]">
                                @if($item['can_confirm'])
                                    <form method="POST" action="{{ route('history.confirm') }}">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $item['plan_id'] }}">
                                        <input type="hidden" name="date" value="{{ $item['date'] }}">
                                        <input type="hidden" name="episodes" value="{{ $item['episodes_to_confirm'] }}">
                                        <input type="hidden" name="action" value="confirm">

                                        <button type="submit"
                                            class="w-full bg-green-600 hover:bg-green-700 px-3 py-2 rounded text-white text-sm font-medium">
                                            Confirmar
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('history.confirm') }}">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $item['plan_id'] }}">
                                        <input type="hidden" name="date" value="{{ $item['date'] }}">
                                        <input type="hidden" name="episodes" value="0">
                                        <input type="hidden" name="action" value="missed">

                                        <button type="submit"
                                            class="w-full bg-red-600 hover:bg-red-700 px-3 py-2 rounded text-white text-sm font-medium">
                                            Não assistimos
                                        </button>
                                    </form>
                                @elseif($item['type'] === 'variable')
                                    @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('admin.animes.log.create', ['plan' => $item['plan_id'], 'date' => $item['date']]) }}"
                                        class="w-full bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded text-white text-sm text-center">
                                            Registrar dia
                                        </a>
                                    @else
                                        <span class="text-xs text-center bg-yellow-600/20 border border-yellow-500 text-yellow-300 px-3 py-2 rounded">
                                            Dia variável
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-center bg-gray-700 text-gray-300 px-3 py-2 rounded">
                                        Já registrado
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        @empty
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
                <p class="text-gray-300">Nenhum histórico encontrado ainda.</p>
            </div>
        @endforelse

    </div>
</x-app-layout>