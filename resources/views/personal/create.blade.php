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
                <img src="{{ $anime->image }}"
                     alt="{{ $anime->title }}"
                     class="w-24 h-32 object-cover rounded">

                <div>
                    <h1 class="text-xl font-bold">{{ $anime->title }}</h1>
                    <p class="text-sm text-gray-300 mt-1">
                        Episódios: {{ $anime->episodes ?? '?' }}
                    </p>
                    <p class="text-sm text-gray-400">
                        Status: {{ $anime->anime_status }}
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

            <form method="POST" action="{{ route('personal.watch-plans.store', $anime->id) }}">
                @csrf       

                <div class="mb-6">
                    <label class="flex items-center gap-3 text-sm font-medium">
                        <input
                            type="checkbox"
                            name="is_completed"
                            value="1"
                            class="accent-green-500 w-4 h-4"
                        >
                        <span>Concluído</span>
                    </label>

                    <p class="text-sm text-gray-400 mt-1">
                        Marque se este anime já foi concluído e você só quer cadastrar no sistema.
                    </p>
                </div>
                
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium">
                        Data de início
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ date('Y-m-d') }}"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                </div>

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium">
                        Episódio atual
                    </label>

                    <input
                        type="number"
                        name="episodes_watched"
                        min="0"
                        value="0"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >

                    <p class="text-sm text-gray-400 mt-1">
                        Informe em qual episódio vocês estão atualmente.
                    </p>
                </div>

                <div class="mb-4">
                    <h2 class="text-lg font-semibold mb-3">Episódios por dia da semana</h2>
                    <p class="text-sm text-gray-400 mb-4">
                        Coloque 0 nos dias em que vocês não vão assistir.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 text-sm font-medium">Segunda</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="monday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="monday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Terça</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="tuesday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="tuesday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Quarta</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="wednesday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="wednesday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Quinta</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="thursday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="thursday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Sexta</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="friday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="friday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Sábado</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="saturday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="saturday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Domingo</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                name="sunday"
                                min="0"
                                value="0"
                                class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                            >
                            <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                                <input type="checkbox" name="sunday_variable" value="1" class="accent-blue-500">
                                <span>Variado</span>
                            </label>
                        </div>
                    </div>

                </div>

                <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white font-semibold w-full mt-6">
                    Salvar plano
                </button>
            </form>
        </div>
    </div>
</x-app-layout>