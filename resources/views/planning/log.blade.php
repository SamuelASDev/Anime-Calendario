<x-app-layout>
    <div class="max-w-2xl mx-auto p-6 text-white">
        <h1 class="text-2xl font-bold mb-6">Registrar episódios · {{ $plan->anime->title }}</h1>
        <form class="rounded-xl border border-gray-700 bg-gray-800 p-6 space-y-5" method="POST" action="{{ route($routePrefix.'.log.store', $plan) }}">
            @csrf
            <x-validation-summary />
            <label class="block">Data
                <input class="form-field" name="watched_date" type="date" required min="{{ $plan->start_date }}" max="{{ today()->toDateString() }}" value="{{ old('watched_date', request('date', today()->toDateString())) }}">
            </label>
            <label class="block">Episódios assistidos neste dia
                <input class="form-field" name="episodes_watched_today" type="number" min="0" max="100000" required value="{{ old('episodes_watched_today', 1) }}">
            </label>
            <label class="block">Observações
                <textarea class="form-field" name="notes" maxlength="2000">{{ old('notes') }}</textarea>
            </label>
            <p class="text-sm text-gray-400">Este registro substitui a previsão ou o registro anterior da mesma data.</p>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold">Salvar registro</button>
        </form>
    </div>
</x-app-layout>
