<x-app-layout>
    <div class="page-wrap max-w-3xl">
        <x-page-heading eyebrow="MAIS UM CAPÍTULO NA CONTA" title="Registrar episódios" :description="$plan->anime->title" />
        <form class="surface p-6 sm:p-8 space-y-6" method="POST" action="{{ route($routePrefix.'.log.store', $plan) }}">@csrf<x-validation-summary />
            <label class="block text-sm text-slate-300">Em qual dia?<input class="form-field" name="watched_date" type="date" required min="{{ $plan->start_date }}" max="{{ today()->toDateString() }}" value="{{ old('watched_date', request('date', today()->toDateString())) }}"></label>
            <label class="block text-sm text-slate-300">Quantos episódios assistiu?<input class="form-field" name="episodes_watched_today" type="number" min="0" max="100000" required value="{{ old('episodes_watched_today', 1) }}"></label>
            <label class="block text-sm text-slate-300">Observações <span class="text-slate-600">(opcional)</span><textarea class="form-field" name="notes" rows="4" maxlength="2000" placeholder="Alguma lembrança desse dia?">{{ old('notes') }}</textarea></label>
            <p class="card-note">Este registro substitui a previsão ou o registro anterior da mesma data. Use zero se não assistiu nesse dia.</p>
            <div class="flex gap-3"><button class="btn btn-primary"><x-icon name="check" />Salvar registro</button><a class="btn btn-secondary" href="{{ route($routePrefix.'.index') }}">Cancelar</a></div>
        </form>
    </div>
</x-app-layout>
