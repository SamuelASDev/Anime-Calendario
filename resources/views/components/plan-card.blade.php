@props(['plan', 'routePrefix', 'editable' => true])
@php($episode = $plan->current_episode_calculated)
<article class="anime-card plan-card" data-anime-card>
    <div class="plan-cover">
        @if ($plan->anime->image)
            <img loading="lazy" src="{{ $plan->anime->image }}" alt="Capa de {{ $plan->anime->title }}">
        @endif
    </div>
    <div class="plan-content">
        <div><x-status-badge :status="$plan->watch_status" /></div>
        <h3 class="card-title"><a class="card-link"
                href="{{ route('anime.show', $plan->anime) }}">{{ $plan->anime->title }}</a></h3>
        @if ($plan->watch_status === 'pretendo_assistir')
            <p class="card-note">Uma história esperando a sua vez.</p>
        @else
            <div>
                <p class="card-note">{{ $episode }} <span class="text-slate-600">/</span>
                    {{ $plan->anime->episodes ?? '?' }} episódios</p>
                @if ($plan->anime->episodes)
                    <div class="progress-track" role="progressbar" aria-label="Progresso de {{ $plan->anime->title }}"
                        aria-valuenow="{{ $episode }}" aria-valuemin="0"
                        aria-valuemax="{{ $plan->anime->episodes }}">
                        <div class="progress-fill"
                            style="width:{{ min(100, ($episode / max(1, $plan->anime->episodes)) * 100) }}%"></div>
                    </div>
                @endif
            </div>
        @endif
        <div class="card-actions">
            @if ($editable)
                @if ($plan->watch_status === 'pretendo_assistir')
                    <a class="btn btn-primary btn-sm"
                        href="{{ route($routePrefix === 'personal.animes' ? 'personal.watch-plans.create' : 'watch-plans.create', $plan->anime) }}"><x-icon
                            name="play" />Começar a assistir</a>
                @elseif($plan->watch_status === 'concluido' && $routePrefix === 'personal.animes')
                    <a class="btn btn-primary btn-sm"
                        href="{{ route('personal.completed.review.create', $plan->anime) }}"><x-icon
                            name="star" />Avaliar</a>
                @else
                    <a class="btn btn-primary btn-sm" href="{{ route($routePrefix . '.log.create', $plan) }}"><x-icon
                            name="plus" />Registrar</a>
                @endif
                <a class="btn btn-secondary btn-sm" href="{{ route($routePrefix . '.edit', $plan) }}">Editar</a>
                @if (!in_array($plan->watch_status, ['concluido', 'pretendo_assistir']))
                    @php($action = $plan->watch_status === 'pausado' ? 'resume' : 'pause')
                    <form method="POST" action="{{ route($routePrefix . '.' . $action, $plan) }}">@csrf
                        @method('PATCH')<button class="btn btn-quiet btn-sm"
                            aria-label="{{ $action === 'pause' ? 'Pausar' : 'Retomar' }} {{ $plan->anime->title }}"
                            title="{{ $action === 'pause' ? 'Pausar' : 'Retomar' }}"><x-icon
                                :name="$action === 'pause' ? 'pause' : 'play'" /></button></form>
                    <form method="POST" action="{{ route($routePrefix . '.complete', $plan) }}">@csrf
                        @method('PATCH')<button class="btn btn-quiet btn-sm"
                            aria-label="Concluir {{ $plan->anime->title }}" title="Concluir"><x-icon
                                name="check" /></button></form>
                @endif
                <form method="POST" action="{{ route($routePrefix . '.destroy', $plan) }}"
                    onsubmit="return confirm('Remover este planejamento?')">@csrf @method('DELETE')<button
                        class="btn btn-danger btn-sm" aria-label="Remover {{ $plan->anime->title }}"
                        title="Remover"><x-icon name="trash" /></button></form>
            @else
                <form method="POST" action="{{ route('personal.watch-plans.follow', $plan) }}">@csrf<button
                        class="btn btn-secondary btn-sm"><x-icon name="plus" />Acompanhar</button></form>
                <x-watch-later-button :anime="$plan->anime" />
            @endif
        </div>
    </div>
</article>
