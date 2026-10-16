@props(['item', 'personal' => false, 'history' => false])
<article class="anime-card timeline-entry" data-anime-card>
    @if($item['anime_image'])<img loading="lazy" src="{{ $item['anime_image'] }}" alt="">@endif
    <div class="min-w-0 flex-1">
        <h3 class="card-title"><a class="card-link" href="{{ route('anime.show', $item['anime_id']) }}">{{ $item['anime_title'] }}</a></h3>
        <p class="card-note mt-1">{{ $item['label'] }}</p>
        @if($item['notes'])<p class="text-xs text-slate-500 mt-1">{{ $item['notes'] }}</p>@endif
    </div>
    <div class="card-actions">
        @if($history && $item['can_confirm'] && ($personal || auth()->user()->isAdmin()))
            @foreach(['confirm' => 'Confirmar', 'missed' => 'Não assisti'] as $action => $label)
                <form method="POST" action="{{ route($personal ? 'personal.history.confirm' : 'history.confirm') }}">@csrf<input type="hidden" name="plan_id" value="{{ $item['plan_id'] }}"><input type="hidden" name="date" value="{{ $item['date'] }}"><input type="hidden" name="episodes" value="{{ $action === 'confirm' ? $item['episodes_to_confirm'] : 0 }}"><input type="hidden" name="action" value="{{ $action }}"><button class="btn {{ $action === 'confirm' ? 'btn-primary' : 'btn-secondary' }} btn-sm">{{ $label }}</button></form>
            @endforeach
        @elseif($history && $item['type'] === 'variable' && ($personal || auth()->user()->isAdmin()))
            <a class="btn btn-secondary btn-sm" href="{{ route($personal ? 'personal.animes.log.create' : 'admin.animes.log.create', ['plan' => $item['plan_id'], 'date' => $item['date']]) }}">Registrar dia</a>
        @else
            <span class="status-tag">{{ match($item['type']) { 'logged' => 'Registrado', 'missed' => 'Não assistido', 'variable' => 'A definir', default => $history ? 'Não confirmado' : 'Previsto' } }}</span>
        @endif
    </div>
</article>
