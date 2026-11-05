<x-app-layout>
    <x-slot:title>Completar dados · Anime Tracker</x-slot:title>
    <div class="page-wrap">
        <x-page-heading eyebrow="Administração do catálogo" title="Completar dados dos animes" description="Busca nomes alternativos, datas de lançamento e outros detalhes dos animes já cadastrados, um por vez e sem pressa. Títulos, sinopses e capas que já existem não são alterados.">
            <x-slot:actions><a class="btn btn-secondary" href="{{ route('admin.animes.index') }}">← Voltar ao planejamento</a></x-slot:actions>
        </x-page-heading>
        @if(session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif

        <section class="surface metadata-panel" data-metadata-sync data-url="{{ route('admin.animes.metadata.sync') }}" data-token="{{ csrf_token() }}">
            <div class="metadata-stats">
                <div>Cadastrados<strong data-count="total">{{ $status['total'] }}</strong></div>
                <div>Completos<strong data-count="synced">{{ $status['synced'] }}</strong></div>
                <div>Na fila<strong data-count="pending">{{ $status['pending'] }}</strong></div>
                <div>Com falha<strong data-count="failed">{{ $status['failed'] }}</strong></div>
            </div>
            <div class="progress-track" role="progressbar" aria-label="Animes completos" aria-valuemin="0" aria-valuemax="{{ $status['total'] }}" aria-valuenow="{{ $status['synced'] }}" data-progress>
                <div class="progress-fill" data-progress-fill style="width:{{ $status['total'] ? ($status['synced'] / $status['total']) * 100 : 0 }}%"></div>
            </div>
            <div class="metadata-controls">
                <button type="button" class="btn btn-primary" data-sync-toggle @disabled($status['pending'] === 0)><x-icon name="play" /><span data-sync-label>Iniciar</span></button>
                <p class="card-note" role="status" aria-live="polite" data-sync-feedback>{{ $status['pending'] === 0 ? 'Não há animes na fila.' : 'Mantenha esta página aberta durante o processo. Você pode pausar e continuar quando quiser.' }}</p>
            </div>
            <ol class="metadata-log" data-sync-log></ol>
        </section>

        <p class="card-note">Ritmo: um anime a cada {{ config('services.anime_catalog.sync_interval') }} segundos, para respeitar os limites da API. Se ela pedir uma pausa, o processo espera sozinho e continua depois.</p>

        @if($failures->isNotEmpty())
            <section>
                <div class="section-heading">
                    <div><h2><x-icon name="close" />Animes com falha<span class="section-count">{{ $status['failed'] }}</span></h2><p>Estes animes não entram na fila até você pedir uma nova tentativa.</p></div>
                    <form method="POST" action="{{ route('admin.animes.metadata.retry') }}">@csrf<button class="btn btn-secondary btn-sm">Tentar novamente</button></form>
                </div>
                <ul class="metadata-failures">
                    @foreach($failures as $anime)
                        <li><a href="{{ route('anime.show', $anime) }}">{{ $anime->title }}</a><span class="status-tag">{{ $anime->metadata_sync_error === 'not_found' ? 'Não encontrado na API' : 'Resposta inválida' }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-app-layout>
