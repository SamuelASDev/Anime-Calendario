<x-app-layout>
    @php($editing = isset($plan))
    @php($anime = $editing ? $plan->anime : $anime)
    @php($days = $editing ? $plan->days->keyBy('day_of_week') : (($startingPlan ?? null)?->days ?? collect())->keyBy('day_of_week'))
    <div class="page-wrap max-w-4xl">
        <x-page-heading eyebrow="SEU TEMPO, SUAS REGRAS" :title="$editing ? 'Editar planejamento' : 'Novo planejamento'" :description="$anime->title"><x-slot:actions><a class="btn btn-secondary" href="{{ route('anime.show', $anime) }}">Ver anime <x-icon name="arrow" /></a></x-slot:actions></x-page-heading>
        <form method="POST" action="{{ $editing ? route($routePrefix.'.update', $plan) : route($storeRoute, $anime) }}" class="surface p-6 sm:p-8 space-y-7">
            @csrf @if($editing) @method('PUT') @endif
            <x-validation-summary />
            <div class="grid sm:grid-cols-2 gap-5">
                <label class="text-sm text-slate-300">Quando quer começar?<input class="form-field" type="date" name="start_date" required value="{{ old('start_date', $plan->start_date ?? today()->toDateString()) }}"></label>
                <label class="text-sm text-slate-300">Episódios já assistidos antes do início<input class="form-field" type="number" name="episodes_watched" min="0" max="100000" required value="{{ old('episodes_watched', $plan->episodes_watched ?? 0) }}"></label>
            </div>
            @if($editing)<label class="block text-sm text-slate-300">Status<select name="watch_status" class="form-field">@foreach(\App\Enums\WatchStatus::cases() as $status)<option value="{{ $status->value }}" @selected(old('watch_status', $plan->watch_status) === $status->value)>{{ $status->label() }}</option>@endforeach</select></label>@if($plan->watch_status === 'pretendo_assistir')<p class="card-note">Escolha Assistindo e a data de início para ativar sua agenda. Na espera, os episódios não avançam.</p>@endif
            @else<label class="flex gap-3 items-center text-sm text-slate-300"><input type="hidden" name="is_completed" value="0"><input type="checkbox" name="is_completed" value="1" @checked(old('is_completed'))>Já concluí este anime</label>@endif
            <div class="border-t border-white/10 pt-6"><h2 class="font-semibold">Agenda semanal</h2><p class="card-note mt-2 mb-5">Escolha quantos episódios assistir em cada dia. Marque Variado para decidir na hora; um registro real sempre substitui a previsão.</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach([1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 0 => 'Domingo'] as $number => $label)
                        @php($name = \App\Services\Planning\PlanManager::WEEK[$number])
                        <div class="rounded-xl border border-white/5 p-4"><label for="{{ $name }}" class="block text-sm text-slate-300">{{ $label }}</label><div class="flex gap-4 items-center"><input id="{{ $name }}" type="number" name="{{ $name }}" min="0" max="100" required class="form-field" value="{{ old($name, $days->get($number)?->episodes_planned ?? 0) }}"><label class="flex gap-2 items-center text-xs text-slate-400 whitespace-nowrap"><input type="hidden" name="{{ $name }}_variable" value="0"><input type="checkbox" name="{{ $name }}_variable" value="1" @checked(old($name.'_variable', $days->get($number)?->is_variable ?? false))>Variado</label></div></div>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-wrap gap-3 border-t border-white/10 pt-6"><button class="btn btn-primary"><x-icon name="check" />Salvar planejamento</button><a class="btn btn-secondary" href="{{ route($indexRoute) }}">Cancelar</a></div>
        </form>
    </div>
</x-app-layout>
