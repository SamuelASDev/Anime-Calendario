@props(['plan', 'routePrefix'])
<article class="rounded-xl border border-gray-700 bg-gray-800 p-4 flex gap-4">
    <img loading="lazy" src="{{ $plan->anime->image }}" alt="{{ $plan->anime->title }}" class="w-20 h-28 rounded-lg object-cover shrink-0">
    <div class="min-w-0 flex-1">
        <a href="{{ route('anime.show', $plan->anime) }}" class="font-semibold hover:text-indigo-300">{{ $plan->anime->title }}</a>
        @if($plan->watch_status === 'pretendo_assistir')
            <p class="text-sm text-amber-200 mt-2">Pretendo assistir · Ainda não iniciado</p>
        @else
            <p class="text-sm text-gray-300 mt-2">Progresso: {{ $plan->current_episode_calculated }} / {{ $plan->anime->episodes ?? '?' }}</p>
        @endif
        <div class="flex flex-wrap gap-2 mt-4 text-sm">
            <a class="rounded bg-gray-700 px-3 py-2" href="{{ route($routePrefix.'.edit', $plan) }}">Editar</a>
            @if($plan->watch_status === 'pretendo_assistir')
                <a class="rounded bg-indigo-600 px-3 py-2" href="{{ route($routePrefix === 'personal.animes' ? 'personal.watch-plans.create' : 'watch-plans.create', $plan->anime) }}">Começar a assistir</a>
            @else
                <a class="rounded bg-indigo-600 px-3 py-2" href="{{ route($routePrefix.'.log.create', $plan) }}">Registrar</a>
            @endif
            @if(! in_array($plan->watch_status, ['concluido', 'pretendo_assistir']))
                @php($action = $plan->watch_status === 'pausado' ? 'resume' : 'pause')
                <form method="POST" action="{{ route($routePrefix.'.'.$action, $plan) }}">
                    @csrf @method('PATCH')
                    <button class="rounded bg-gray-700 px-3 py-2">{{ $action === 'pause' ? 'Pausar' : 'Retomar' }}</button>
                </form>
                <form method="POST" action="{{ route($routePrefix.'.complete', $plan) }}">
                    @csrf @method('PATCH')
                    <button class="rounded bg-green-700 px-3 py-2">Concluir</button>
                </form>
            @endif
            <form method="POST" action="{{ route($routePrefix.'.destroy', $plan) }}" onsubmit="return confirm('Remover este planejamento?')">
                @csrf @method('DELETE')
                <button class="rounded bg-red-700 px-3 py-2">Remover</button>
            </form>
        </div>
    </div>
</article>
