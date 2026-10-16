<x-app-layout>
    <div class="page-wrap">
        <x-page-heading :eyebrow="$personal ? 'SUA COLEÇÃO DE HISTÓRIAS' : 'ESPAÇO DA COMUNIDADE'" :title="$personal ? 'Minha biblioteca' : 'Planejamento da comunidade'" description="Tudo o que você acompanha, organizado no seu ritmo.">
            <x-slot:actions><a class="btn btn-secondary" href="{{ route($personal ? 'personal.calendar' : 'calendar') }}"><x-icon name="calendar" />Calendário</a><a class="btn btn-primary" href="{{ route('anime.index.all') }}"><x-icon name="plus" />Encontrar animes</a></x-slot:actions>
        </x-page-heading>
        @if(session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <x-validation-summary />
        <x-list-filters :statuses="true" />
        @php($sections = [
            'pretendo_assistir' => ['Pretendo assistir', $plannedPlans, 'bookmark'],
            'assistindo' => ['Em andamento', $plans, 'play'],
            'pausado' => ['Pausados', $pausedPlans, 'pause'],
            'concluido' => ['Concluídos', $completedPlans, 'check'],
        ])
        <nav class="library-tabs" aria-label="Status da biblioteca">
            <a class="library-tab {{ ! request('status') ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => null, 'active_page' => null, 'planned_page' => null, 'paused_page' => null, 'completed_page' => null]) }}">Visão geral</a>
            @foreach($sections as $value => [$title, $page, $icon])<a class="library-tab {{ request('status') === $value ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => $value, 'active_page' => null, 'planned_page' => null, 'paused_page' => null, 'completed_page' => null]) }}"><x-icon :name="$icon" />{{ $title }}<span>{{ $page->total() }}</span></a>@endforeach
        </nav>
        @foreach($sections as $value => [$title, $page, $icon])
            @if(! request('status') || request('status') === $value)
                <section class="mb-9" id="section-{{ $value }}">
                    <div class="section-heading"><h2><x-icon :name="$icon" />{{ $title }}<span class="section-count">{{ $page->total() }}</span></h2></div>
                    <div class="horizontal-grid">
                        @forelse($page as $plan)<x-plan-card :plan="$plan" :route-prefix="$routePrefix" />@empty<div class="col-span-full"><x-empty-state :title="request('q') || request('genre') || request('favorite') ? 'Nenhum resultado nesta seção' : 'Seu próximo capítulo ainda está por vir'" description="Explore o catálogo ou ajuste os filtros para encontrar seus animes." :icon="$icon" /></div>@endforelse
                    </div>
                    <div class="mt-5">{{ $page->links() }}</div>
                </section>
            @endif
        @endforeach
    </div>
</x-app-layout>
