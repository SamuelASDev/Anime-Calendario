<x-app-layout>
    <div class="page-wrap">
        <x-page-heading eyebrow="PARA QUANDO DER VONTADE" title="Pretendo assistir" description="Histórias que você quer viver. Guarde agora e escolha sua agenda quando for começar.">
            <x-slot:actions><a class="btn btn-secondary" href="{{ route('personal.animes.index') }}">Minha biblioteca</a><a class="btn btn-primary" href="{{ route('anime.index.all') }}"><x-icon name="plus" />Explorar catálogo</a></x-slot:actions>
        </x-page-heading>
        @if(session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <x-validation-summary /><x-list-filters />
        <div class="section-heading"><h2><x-icon name="bookmark" />Sua lista de espera <span class="section-count">{{ $plans->total() }}</span></h2></div>
        <div class="horizontal-grid">
            @forelse($plans as $plan)<x-plan-card :plan="$plan" route-prefix="personal.animes" />
            @empty<div class="col-span-full"><x-empty-state title="Nenhum anime na espera" description="Use Pretendo assistir no catálogo ou na busca. Você escolhe quando começar."><a class="btn btn-primary" href="{{ route('anime.index.all') }}">Encontrar minha próxima história</a></x-empty-state></div>@endforelse
        </div>
        <div class="mt-6">{{ $plans->links() }}</div>
    </div>
</x-app-layout>
