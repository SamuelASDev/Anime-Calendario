<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white space-y-8">
        <div>
            <h1 class="text-2xl font-bold">{{ $personal ? 'Minha biblioteca' : 'Planejamento da comunidade' }}</h1>
            <p class="mt-2 text-gray-400">Acompanhe sua agenda e registre o que assistiu. O progresso previsto não muda o status automaticamente.</p>
        </div>
        @if(session('success')) <p role="status" class="rounded-lg border border-green-600 bg-green-600/10 p-4 text-green-200">{{ session('success') }}</p> @endif
        @foreach(['Pretendo assistir' => $plannedPlans, 'Em andamento' => $plans, 'Pausados' => $pausedPlans, 'Concluídos' => $completedPlans] as $title => $page)
            <section class="space-y-4">
                <h2 class="text-xl font-semibold">{{ $title }} <span class="text-sm text-gray-400">({{ $page->total() }})</span></h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @forelse($page as $plan)
                        <x-plan-card :plan="$plan" :route-prefix="$routePrefix" />
                    @empty
                        <p class="text-gray-400">Nenhum plano nesta seção.</p>
                    @endforelse
                </div>
                {{ $page->links() }}
            </section>
        @endforeach
    </div>
</x-app-layout>
