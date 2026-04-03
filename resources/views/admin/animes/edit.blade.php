<x-app-layout>
    <div class="max-w-3xl mx-auto p-6 text-white">

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ url()->previous() }}"
                class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                    ← Voltar
                </a>
            </div>
            <div class="flex gap-4 mb-6">
                <img src="{{ $plan->anime->image }}"
                     alt="{{ $plan->anime->title }}"
                     class="w-24 h-32 object-cover rounded">

                <div>
                    <h1 class="text-xl font-bold">{{ $plan->anime->title }}</h1>
                    <p class="text-sm text-gray-300 mt-1">
                        Episódios: {{ $plan->anime->episodes ?? '?' }}
                    </p>
                    <p class="text-sm text-gray-400">
                        Status do anime: {{ $plan->anime->anime_status }}
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $days = $plan->days->keyBy('day_of_week');
            @endphp

            <form method="POST" action="{{ route('admin.animes.update', $plan->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Data de início</label>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ $plan->start_date }}"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Episódio atual</label>
                    <input
                        type="number"
                        name="episodes_watched"
                        min="0"
                        value="{{ $plan->episodes_watched }}"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                </div>

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium">Status</label>
                    <select
                        name="watch_status"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                        <option value="assistindo" @selected($plan->watch_status === 'assistindo')>Assistindo</option>
                        <option value="pausado" @selected($plan->watch_status === 'pausado')>Pausado</option>
                        <option value="concluido" @selected($plan->watch_status === 'concluido')>Concluído</option>
                    </select>
                </div>

                <h2 class="text-lg font-semibold mb-3">Planejamento por dia</h2>
                <p class="text-sm text-gray-400 mb-4">
                    Marque "Variado" quando quiser definir depois manualmente.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    @php
                        $weekDays = [
                            1 => ['label' => 'Segunda', 'name' => 'monday', 'var' => 'monday_variable'],
                            2 => ['label' => 'Terça', 'name' => 'tuesday', 'var' => 'tuesday_variable'],
                            3 => ['label' => 'Quarta', 'name' => 'wednesday', 'var' => 'wednesday_variable'],
                            4 => ['label' => 'Quinta', 'name' => 'thursday', 'var' => 'thursday_variable'],
                            5 => ['label' => 'Sexta', 'name' => 'friday', 'var' => 'friday_variable'],
                            6 => ['label' => 'Sábado', 'name' => 'saturday', 'var' => 'saturday_variable'],
                            0 => ['label' => 'Domingo', 'name' => 'sunday', 'var' => 'sunday_variable'],
                        ];
                    @endphp

                    @foreach ($weekDays as $dayNumber => $info)
                        <div>
                            <label class="block mb-2 text-sm font-medium">{{ $info['label'] }}</label>

                            <div class="flex items-center gap-3">
                                <input
                                    type="number"
                                    name="{{ $info['name'] }}"
                                    min="0"
                                    value="{{ $days[$dayNumber]->episodes_planned ?? 0 }}"
                                    class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                                    style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                                >

                                <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        name="{{ $info['var'] }}"
                                        value="1"
                                        class="accent-blue-500"
                                        @checked(($days[$dayNumber]->is_variable ?? false))
                                    >
                                    <span>Variado</span>
                                </label>
                            </div>
                        </div>
                    @endforeach

                </div>

                <button class="bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded text-white font-semibold w-full mt-6">
                    Salvar alterações
                </button>
            </form>
        </div>
    </div>
</x-app-layout>