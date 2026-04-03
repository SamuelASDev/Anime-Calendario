<x-app-layout>
    <div class="max-w-2xl mx-auto p-6 text-white">

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">

            <div class="mb-4">
                <a href="{{ url()->previous() }}"
                class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                    ← Voltar
                </a>
            </div>

            <h1 class="text-xl font-bold mb-4">
                Registrar episódios - {{ $plan->anime->title }}
            </h1>

            <form method="POST" action="{{ route('admin.animes.log.store', $plan->id) }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Data</label>
                    <input
                        type="date"
                        name="watched_date"
                        value="{{ date('Y-m-d') }}"
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
                        value="0"
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
                    ></textarea>
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white font-semibold">
                    Salvar registro
                </button>
            </form>
        </div>
    </div>
</x-app-layout>