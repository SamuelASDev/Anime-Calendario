<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white">

        @if (session('success'))
            <div class="mb-6 bg-green-600/20 border border-green-500 text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold">Histórico de episódios</h1>

            <div class="flex flex-row gap-3 w-full sm:w-auto">
                <a href="{{ route('calendar') }}"
                   class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm flex-1 sm:flex-none text-center">
                    Ver calendário
                </a>

                <a href="{{ route('anime.search') }}"
                   class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white text-sm flex-1 sm:flex-none text-center">
                    Buscar animes
                </a>
            </div>
        </div>

        @forelse ($historyData as $date => $items)
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 sm:p-5 mb-6 shadow-lg">

                <h2 class="text-xl font-semibold mb-4 text-blue-300">
                    {{ $date }}
                </h2>

                <div class="space-y-4">

                    @foreach ($items as $item)
                        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4">
                            <div class="flex flex-col sm:flex-row gap-4 items-start">

                                <div class="flex sm:block w-full sm:w-auto justify-center">
                                    <img src="{{ $item['anime_image'] }}"
                                         alt="{{ $item['anime_title'] }}"
                                         class="w-24 h-36 sm:w-16 sm:h-24 object-cover rounded shadow-md shrink-0">
                                </div>

                                <div class="flex-1 w-full min-w-0">

                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                        <h3 class="font-bold text-white break-words text-lg">
                                            {{ $item['anime_title'] }}
                                        </h3>

                                        <div>
                                            @if($item['type'] === 'logged')
                                                <span class="text-[10px] uppercase tracking-wider bg-green-600 px-2 py-1 rounded font-bold">
                                                    ✔ Confirmado
                                                </span>
                                            @elseif($item['type'] === 'missed')
                                                <span class="text-[10px] uppercase tracking-wider bg-red-600 px-2 py-1 rounded font-bold">
                                                    ✖ Não assistimos
                                                </span>
                                            @elseif($item['type'] === 'variable')
                                                <span class="text-[10px] uppercase tracking-wider bg-yellow-600 px-2 py-1 rounded font-bold">
                                                    ⚠ A definir
                                                </span>
                                            @else
                                                <span class="text-[10px] uppercase tracking-wider bg-gray-600 px-2 py-1 rounded font-bold">
                                                    Pendente
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <p class="text-sm font-medium mb-1
                                        @if($item['type'] === 'logged') text-green-300
                                        @elseif($item['type'] === 'missed') text-red-400
                                        @elseif($item['type'] === 'variable') text-yellow-300
                                        @else text-gray-300
                                        @endif
                                    ">
                                        {{ $item['label'] }}
                                    </p>

                                    @if(!empty($item['notes']))
                                        <p class="text-xs text-gray-400 mt-2 p-2 bg-black/20 rounded border-l-2 border-gray-600 italic break-words">
                                            💬 {{ $item['notes'] }}
                                        </p>
                                    @endif

                                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                                        @if($item['can_confirm'])
                                            <form method="POST" action="{{ route('history.confirm') }}" class="w-full sm:w-auto">
                                                @csrf
                                                <input type="hidden" name="plan_id" value="{{ $item['plan_id'] }}">
                                                <input type="hidden" name="date" value="{{ $item['date'] }}">
                                                <input type="hidden" name="episodes" value="{{ $item['episodes_to_confirm'] }}">
                                                <input type="hidden" name="action" value="confirm">

                                                <button type="submit"
                                                    class="w-full bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-white text-sm font-bold transition">
                                                    Confirmar
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('history.confirm') }}" class="w-full sm:w-auto">
                                                @csrf
                                                <input type="hidden" name="plan_id" value="{{ $item['plan_id'] }}">
                                                <input type="hidden" name="date" value="{{ $item['date'] }}">
                                                <input type="hidden" name="episodes" value="0">
                                                <input type="hidden" name="action" value="missed">

                                                <button type="submit"
                                                    class="w-full bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-white text-sm font-bold transition">
                                                    Não assistimos
                                                </button>
                                            </form>
                                        @elseif($item['type'] === 'variable')
                                            @if(auth()->user()?->role === 'admin')
                                                <a href="{{ route('admin.animes.log.create', ['plan' => $item['plan_id'], 'date' => $item['date']]) }}"
                                                   class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white text-sm text-center font-bold">
                                                   Registrar dia
                                                </a>
                                            @else
                                                <span class="text-xs text-center bg-yellow-600/20 border border-yellow-500 text-yellow-300 px-4 py-2 rounded w-full sm:w-auto">
                                                    Dia variável
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-center bg-gray-700/50 text-gray-400 border border-gray-600 px-4 py-2 rounded w-full sm:w-auto font-medium">
                                                Já registrado
                                            </span>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        @empty
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 text-center">
                <p class="text-gray-400 text-lg">Nenhum histórico encontrado ainda.</p>
            </div>
        @endforelse

    </div>
</x-app-layout>