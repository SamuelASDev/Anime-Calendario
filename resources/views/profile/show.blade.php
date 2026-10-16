<x-app-layout>
    @php($owner = auth()->id() === $user->id)
    <div class="max-w-7xl mx-auto p-4 sm:p-6 text-white space-y-8">
        <header class="overflow-hidden rounded-2xl border border-gray-700 bg-gray-800">
            @if($user->profile_banner_url)<img src="{{ $user->profile_banner_url }}" alt="Banner de {{ $user->name }}" class="h-40 sm:h-56 w-full object-cover">@endif
            <div class="p-6 flex flex-col sm:flex-row gap-5">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="Foto de {{ $user->name }}" class="h-24 w-24 rounded-2xl object-cover">
                @else
                    <div class="h-24 w-24 rounded-2xl bg-indigo-600 flex items-center justify-center text-3xl font-bold">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold break-words">{{ $user->name }}</h1>
                    <p class="text-gray-400 mt-1">{{ '@'.$user->username }}</p>
                    <p class="text-gray-300 mt-3 whitespace-pre-line break-words">{{ $user->bio }}</p>
                    @if(! $user->is_public)<p class="mt-3 text-sm text-amber-300">Seu perfil está privado.</p>@endif
                    @if($owner)<a class="inline-block mt-3 text-indigo-300" href="{{ route('profile.edit') }}">Editar perfil e privacidade</a>@endif
                </div>
            </div>
        </header>
        @if(session('success'))<p role="status" class="rounded-lg bg-green-900/30 p-4 text-green-200">{{ session('success') }}</p>@endif
        @if($owner)
            <section class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold">Pretendo assistir <span class="text-sm text-gray-400">({{ $planned->total() }})</span></h2>
                    <a href="{{ route('personal.watch-later.index') }}" class="text-sm text-amber-200">Gerenciar minha lista</a>
                </div>
                <p class="text-sm text-gray-400">Sua lista para depois fica visível apenas para você.</p>
                <div class="grid md:grid-cols-2 gap-4">
                    @forelse($planned as $plan)<x-plan-card :plan="$plan" route-prefix="personal.animes" />@empty<p class="text-gray-400">Guarde animes no catálogo para assistir depois.</p>@endforelse
                </div>
                {{ $planned->links() }}
            </section>
        @endif
        @if(($owner || $user->show_top10_public) && $top10->count())
            <section class="space-y-4">
                <h2 class="text-xl font-semibold">Top 10</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($top10 as $item)<x-profile-anime-card :anime="$item->anime" :meta="$item" :owner="$owner" />@endforeach
                </div>
            </section>
        @endif
        @if($owner || $user->show_favorites_public)
            <section class="space-y-4">
                <h2 class="text-xl font-semibold">Favoritos <span class="text-sm text-gray-400">({{ $favorites->total() }})</span></h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @forelse($favorites as $item)<x-profile-anime-card :anime="$item->anime" :meta="$item" :owner="$owner" />@empty<p class="text-gray-400">Nenhum favorito ainda.</p>@endforelse
                </div>
                {{ $favorites->links() }}
            </section>
        @endif
        @foreach(['Assistindo' => ['items' => $watching, 'visible' => $user->show_watching_public], 'Concluídos' => ['items' => $completed, 'visible' => $user->show_completed_public]] as $title => $section)
            @if($owner || $section['visible'])
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold">{{ $title }} <span class="text-sm text-gray-400">({{ $section['items']->total() }})</span></h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        @forelse($section['items'] as $plan)
                            <x-profile-anime-card :anime="$plan->anime" :plan="$plan" :owner="$owner" :meta="$owner ? $plan->anime->userMeta->first() : null" />
                        @empty<p class="text-gray-400">Nenhum anime nesta seção.</p>@endforelse
                    </div>
                    {{ $section['items']->links() }}
                </section>
            @endif
        @endforeach
        @if($owner || $user->show_reviews_public)
            <section class="space-y-4">
                <h2 class="text-xl font-semibold">Avaliações</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @forelse($reviews as $item)<x-profile-anime-card :anime="$item->anime" :meta="$item" />@empty<p class="text-gray-400">Nenhuma avaliação ainda.</p>@endforelse
                </div>
                {{ $reviews->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
