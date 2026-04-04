<x-app-layout>
    <div class="max-w-2xl mx-auto p-6 text-white">

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">

            <div class="mb-4">
                <a href="{{ route('personal.animes.index') }}"
                   class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                    ← Voltar
                </a>
            </div>

            <h1 class="text-xl font-bold mb-4">
                Registrar episódios - {{ $plan->anime->title }}
            </h1>

            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('personal.animes.log.store', $plan->id) }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Data</label>
                    <input
                        type="date"
                        name="watched_date"
                        value="{{ old('watched_date', request('date', date('Y-m-d'))) }}"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Quantos episódios assistiram</label>
                    <input
                        type="number"
                        name="episodes_watched_today"
                        min="0"
                        value="{{ old('episodes_watched_today', 0) }}"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Observação</label>
                    <textarea
                        name="notes"
                        rows="3"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >{{ old('notes') }}</textarea>
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white font-semibold">
                    Salvar registro
                </button>
            </form>
        </div>
    </div>
</x-app-layout>