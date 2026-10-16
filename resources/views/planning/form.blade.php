<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6 text-white">
        <div class="rounded-2xl border border-gray-700 bg-gray-800 p-6">
            @php($editing = isset($plan))
            @php($anime = $editing ? $plan->anime : $anime)
            @php($days = $editing ? $plan->days->keyBy('day_of_week') : (($startingPlan ?? null)?->days ?? collect())->keyBy('day_of_week'))
            <h1 class="text-2xl font-bold mb-2">{{ $editing ? 'Editar planejamento' : 'Novo planejamento' }}</h1>
            <p class="text-gray-300 mb-6">{{ $anime->title }} · {{ $anime->episodes ?? 'Total indefinido' }} episódios</p>
            <form method="POST" action="{{ $editing ? route($routePrefix.'.update', $plan) : route($storeRoute, $anime) }}" class="space-y-6">
                @csrf
                @if($editing) @method('PUT') @endif
                <x-validation-summary />
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="block">Data de início
                        <input class="form-field" type="date" name="start_date" required value="{{ old('start_date', $plan->start_date ?? today()->toDateString()) }}">
                    </label>
                    <label class="block">Episódios já assistidos antes do início
                        <input class="form-field" type="number" name="episodes_watched" min="0" max="100000" required value="{{ old('episodes_watched', $plan->episodes_watched ?? 0) }}">
                    </label>
                </div>
                @if($editing)
                    <label class="block">Status
                        <select name="watch_status" class="form-field">
                            @foreach(\App\Enums\WatchStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('watch_status', $plan->watch_status) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if($plan->watch_status === 'pretendo_assistir')
                        <p class="text-sm text-amber-200">Escolha “Assistindo” e a data de início para ativar sua agenda. Enquanto estiver em “Pretendo assistir”, os episódios não avançam.</p>
                    @endif
                @else
                    <label class="flex items-center gap-2"><input type="hidden" name="is_completed" value="0"><input type="checkbox" name="is_completed" value="1" @checked(old('is_completed'))> Já concluí este anime</label>
                @endif
                <div>
                    <h2 class="font-semibold text-lg mb-1">Agenda semanal</h2>
                    <p class="text-sm text-gray-400 mb-4">Um registro real substitui a previsão. Dias variados só avançam quando você registra episódios.</p>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach([1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 0 => 'Domingo'] as $number => $label)
                            @php($name = \App\Services\Planning\PlanManager::WEEK[$number])
                            <div>
                                <label for="{{ $name }}" class="block text-sm">{{ $label }}</label>
                                <input id="{{ $name }}" type="number" name="{{ $name }}" min="0" max="100" required class="form-field" value="{{ old($name, $days->get($number)?->episodes_planned ?? 0) }}">
                                <label class="flex items-center gap-2 mt-2 text-sm text-gray-300">
                                    <input type="hidden" name="{{ $name }}_variable" value="0">
                                    <input type="checkbox" name="{{ $name }}_variable" value="1" @checked(old($name.'_variable', $days->get($number)?->is_variable ?? false))> Variado
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-3">
                    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-5 py-3 font-semibold">Salvar planejamento</button>
                    <a class="rounded-lg bg-gray-700 px-5 py-3" href="{{ route($indexRoute) }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
