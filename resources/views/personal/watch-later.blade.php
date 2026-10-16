<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Pretendo assistir <span class="text-base text-gray-400">({{ $plans->total() }})</span></h1>
                <p class="mt-2 text-gray-400">Guarde animes para depois. Eles entram na agenda quando você começar a assistir.</p>
                <p class="mt-1 text-sm text-gray-400">Ao começar, escolha a data e os episódios por dia. O acompanhamento só inicia depois de salvar o plano.</p>
            </div>
            <a href="{{ route('anime.index.all') }}" class="rounded-lg bg-indigo-600 px-4 py-3 text-sm">Explorar catálogo</a>
        </div>
        @if(session('success'))<p role="status" class="rounded-lg border border-green-600 bg-green-600/10 p-4 text-green-200">{{ session('success') }}</p>@endif
        <x-validation-summary />
        <div class="grid md:grid-cols-2 gap-4">
            @forelse($plans as $plan)
                <x-plan-card :plan="$plan" route-prefix="personal.animes" />
            @empty
                <div class="rounded-xl border border-gray-700 bg-gray-800 p-6 md:col-span-2">
                    <p class="font-semibold">Sua lista está vazia.</p>
                    <p class="mt-2 text-gray-400">Use “Pretendo assistir” no catálogo, na busca ou nos detalhes de um anime para guardá-lo aqui.</p>
                    <a href="{{ route('anime.search') }}" class="inline-block mt-4 text-indigo-300">Buscar um anime</a>
                </div>
            @endforelse
        </div>
        {{ $plans->links() }}
    </div>
</x-app-layout>
