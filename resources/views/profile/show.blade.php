<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-white">
        {{-- Header do perfil --}}
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 shadow-2xl mb-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(168,85,247,0.18),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.18),transparent_35%)]"></div>

            <div class="relative p-6 sm:p-8 lg:p-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-violet-500 to-blue-500 flex items-center justify-center text-2xl font-bold shadow-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight break-words">
                                    {{ $user->name }}
                                </h1>
                                <p class="text-sm sm:text-base text-zinc-300 break-all">
                                    {{ '@' . $user->username }}
                                </p>
                            </div>
                        </div>

                        @if($user->bio)
                            <p class="mt-4 max-w-3xl text-sm sm:text-base text-zinc-200 leading-relaxed whitespace-pre-line">
                                {{ $user->bio }}
                            </p>
                        @else
                            <p class="mt-4 text-sm text-zinc-400 italic">
                                Este usuário ainda não adicionou uma bio.
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:w-[320px]">
                        @if($user->show_favorites_public)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                                <p class="text-xs uppercase tracking-widest text-zinc-400">Favoritos</p>
                                <p class="mt-1 text-2xl font-extrabold text-yellow-300">{{ $favorites->count() }}</p>
                            </div>
                        @endif

                        @if($user->show_watching_public)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                                <p class="text-xs uppercase tracking-widest text-zinc-400">Assistindo</p>
                                <p class="mt-1 text-2xl font-extrabold text-blue-300">{{ $watching->count() }}</p>
                            </div>
                        @endif

                        @if($user->show_completed_public)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                                <p class="text-xs uppercase tracking-widest text-zinc-400">Concluídos</p>
                                <p class="mt-1 text-2xl font-extrabold text-green-300">{{ $completed->count() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 10 --}}
        @if($user->show_top10_public && $top10->count())
            <section class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-violet-500/20 border border-violet-400/20 flex items-center justify-center">
                        🏆
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold">Top 10</h2>
                        <p class="text-sm text-zinc-400">Os animes favoritos em ordem de ranking.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($top10 as $item)
                        <div class="group rounded-2xl border border-white/10 bg-zinc-900/90 hover:bg-zinc-800/90 transition overflow-hidden shadow-lg">
                            <div class="flex h-full">
                                <div class="relative w-24 sm:w-28 shrink-0">
                                    <img
                                        src="{{ $item->anime->image }}"
                                        alt="{{ $item->anime->title }}"
                                        class="h-full w-full object-cover"
                                    >
                                    <div class="absolute top-2 left-2 h-8 min-w-8 px-2 rounded-full bg-black/70 text-yellow-300 text-sm font-bold flex items-center justify-center">
                                        #{{ $item->top_position }}
                                    </div>
                                </div>

                                <div class="flex-1 p-4 min-w-0 flex flex-col justify-center">
                                    <h3 class="font-bold text-base sm:text-lg leading-snug break-words group-hover:text-violet-300 transition">
                                        {{ $item->anime->title }}
                                    </h3>

                                    @if(!empty($item->rating))
                                        <p class="mt-2 text-sm text-yellow-300">
                                            Nota: {{ $item->rating }}/10
                                        </p>
                                    @endif

                                    @if(!empty($item->comment))
                                        <p class="mt-2 text-sm text-zinc-400 line-clamp-3">
                                            {{ $item->comment }}
                                        </p>
                                    @endif

                                    @if(auth()->check() && auth()->id() === $user->id)
                                        <form method="POST" action="{{ route('personal.animes.removeTop', $item->anime->id) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium
                                                bg-red-500/10 text-red-400 border border-red-500/20
                                                hover:bg-red-500/20 hover:text-red-300 transition">
                                                
                                                ❌ Remover do Top
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Favoritos --}}
        @if($user->show_favorites_public && $favorites->count())
            <section class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-yellow-500/20 border border-yellow-400/20 flex items-center justify-center">
                        ⭐
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold">Favoritos</h2>
                        <p class="text-sm text-zinc-400">Animes marcados como favoritos.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($favorites as $item)
                        <div class="rounded-2xl overflow-hidden border border-white/10 bg-zinc-900 shadow-lg hover:-translate-y-1 hover:shadow-2xl transition">
                            <img
                                src="{{ $item->anime->image }}"
                                alt="{{ $item->anime->title }}"
                                class="w-full h-52 sm:h-60 object-cover"
                            >

                            <div class="p-4">
                                <h3 class="font-semibold text-sm sm:text-base break-words leading-snug">
                                    {{ $item->anime->title }}
                                </h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Assistindo --}}
        @if($user->show_watching_public && $watching->count())
            <section class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-blue-500/20 border border-blue-400/20 flex items-center justify-center">
                        📺
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold">Assistindo</h2>
                        <p class="text-sm text-zinc-400">Animes que estão sendo acompanhados agora.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($watching as $plan)
                        @php
                            $myMeta = $plan->anime->userMeta->firstWhere('user_id', auth()->id());
                        @endphp

                        <div class="rounded-2xl border border-white/10 bg-zinc-900 overflow-hidden shadow-lg flex">
                            <img
                                src="{{ $plan->anime->image }}"
                                alt="{{ $plan->anime->title }}"
                                class="w-24 sm:w-28 h-auto object-cover shrink-0"
                            >

                            <div class="p-4 min-w-0 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-base break-words leading-snug">
                                        {{ $plan->anime->title }}
                                    </h3>

                                    <p class="mt-2 text-sm text-zinc-400">
                                        Status: assistindo
                                    </p>

                                    @if(!empty($plan->anime->episodes))
                                        <p class="text-sm text-zinc-400">
                                            Episódios: {{ $plan->anime->episodes }}
                                        </p>
                                    @endif
                                </div>

                                @if(auth()->check() && auth()->id() === $user->id)
                                    <div class="flex gap-2 mt-3 flex-wrap">
                                        {{-- FAVORITO --}}
                                        <form method="POST" action="{{ route('personal.animes.favorite', $plan->anime->id) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="px-2 py-1 text-xs rounded
                                                {{ $myMeta && $myMeta->is_favorite ? 'bg-yellow-500 text-black' : 'bg-gray-700 hover:bg-gray-600' }}">
                                                
                                                {{ $myMeta && $myMeta->is_favorite ? '★ Favorito' : '☆ Favoritar' }}
                                            </button>
                                        </form>

                                        {{-- ADD TOP 10 --}}
                                        <form method="POST" action="{{ route('personal.animes.addTop', $plan->anime->id) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="px-2 py-1 text-xs rounded bg-violet-600 hover:bg-violet-700">
                                                ➕ Top
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Concluídos --}}
        @if($user->show_completed_public && $completed->count())
            <section class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-green-500/20 border border-green-400/20 flex items-center justify-center">
                        ✅
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold">Concluídos</h2>
                        <p class="text-sm text-zinc-400">Animes já terminados.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($completed as $plan)
                        @php
                            $myMeta = $plan->anime->userMeta->firstWhere('user_id', auth()->id());
                        @endphp

                        <div class="rounded-2xl border border-white/10 bg-zinc-900 overflow-hidden shadow-lg flex">
                            <img
                                src="{{ $plan->anime->image }}"
                                alt="{{ $plan->anime->title }}"
                                class="w-24 sm:w-28 object-cover shrink-0"
                            >

                            <div class="p-4 flex-1 flex flex-col justify-between min-w-0">
                                <div>
                                    <h3 class="font-bold text-base break-words">
                                        {{ $plan->anime->title }}
                                    </h3>

                                    <p class="text-sm text-green-300 mt-1">Concluído</p>
                                </div>

                                @if(auth()->check() && auth()->id() === $user->id)
                                    <div class="flex gap-2 mt-3 flex-wrap">
                                        {{-- FAVORITO --}}
                                        <form method="POST" action="{{ route('personal.animes.favorite', $plan->anime->id) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="px-2 py-1 text-xs rounded
                                                {{ $myMeta && $myMeta->is_favorite ? 'bg-yellow-500 text-black' : 'bg-gray-700 hover:bg-gray-600' }}">
                                                
                                                {{ $myMeta && $myMeta->is_favorite ? '★ Favorito' : '☆ Favoritar' }}
                                            </button>
                                        </form>

                                        {{-- ADD TOP 10 --}}
                                        <form method="POST" action="{{ route('personal.animes.addTop', $plan->anime->id) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                class="px-2 py-1 text-xs rounded bg-violet-600 hover:bg-violet-700">
                                                ➕ Top
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Mensagem caso esteja tudo vazio --}}
        @if(
            (!$user->show_top10_public || !$top10->count()) &&
            (!$user->show_favorites_public || !$favorites->count()) &&
            (!$user->show_watching_public || !$watching->count()) &&
            (!$user->show_completed_public || !$completed->count())
        )
            <div class="rounded-3xl border border-white/10 bg-zinc-900 p-8 text-center shadow-lg">
                <p class="text-lg font-semibold text-zinc-200">
                    Nada público para mostrar ainda.
                </p>
                <p class="mt-2 text-sm text-zinc-400">
                    Este perfil ainda não possui listas públicas visíveis.
                </p>
            </div>
        @endif
    </div>
</x-app-layout>