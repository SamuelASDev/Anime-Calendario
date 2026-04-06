<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-white">
        {{-- Header do perfil --}}
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-zinc-900 shadow-2xl mb-8">
            {{-- Banner --}}
            <div class="relative h-48 sm:h-56 lg:h-72 w-full">
                @if($user->profile_banner)
                    <img src="{{ asset('storage/' . $user->profile_banner) }}" alt="Banner de {{ $user->name }}" class="h-full w-full object-cover">
                @endif

                <div class="absolute inset-0 bg-black/35"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(168,85,247,0.20),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.20),transparent_35%)]"></div>
            </div>

            <div class="relative px-6 sm:px-8 lg:px-10 pb-8">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 -mt-12 sm:-mt-16">
                    <div class="min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-5">
                            {{-- Foto de perfil --}}
                            <div class="shrink-0">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto de {{ $user->name }}" class="h-24 w-24 sm:h-28 sm:w-28 lg:h-32 lg:w-32 rounded-3xl object-cover border-4 border-zinc-900 shadow-2xl bg-zinc-800">
                                @else
                                    <div class="h-24 w-24 sm:h-28 sm:w-28 lg:h-32 lg:w-32 rounded-3xl bg-gradient-to-br from-violet-500 to-blue-500 flex items-center justify-center text-3xl sm:text-4xl font-bold shadow-2xl border-4 border-zinc-900">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            {{-- Aumentei o pt-6 para pt-14 e sm:pt-4 para sm:pt-16 para o nome descer --}}
                            <div class="min-w-0 pt-14 sm:pt-16">
                                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight break-words drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">
                                    {{ $user->name }}
                                </h1>

                                <p class="text-sm sm:text-base text-zinc-400 break-all mt-1 font-medium drop-shadow-[0_1.2px_1.2px_rgba(0,0,0,0.8)]">
                                    {{ '@' . $user->username }}
                                </p>

                                @if($user->bio)
                                    <p class="mt-4 max-w-3xl text-sm sm:text-base text-zinc-200 leading-relaxed whitespace-pre-line drop-shadow-[0_1px_1px_rgba(0,0,0,0.5)]">
                                        {{ $user->bio }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stats - Adicionado ring-1 e border-white/20 para o contorno --}}
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:w-[320px]">
                        @if($user->show_favorites_public)
                            <div class="rounded-2xl border border-white/20 bg-white/5 backdrop-blur-md p-4 text-center ring-1 ring-white/10 shadow-inner">
                                <p class="text-xs uppercase tracking-widest text-zinc-400 font-bold">Favoritos</p>
                                <p class="mt-1 text-2xl font-extrabold text-yellow-300 drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">{{ $favorites->count() }}</p>
                            </div>
                        @endif

                        @if($user->show_watching_public)
                            <div class="rounded-2xl border border-white/20 bg-white/5 backdrop-blur-md p-4 text-center ring-1 ring-white/10 shadow-inner">
                                <p class="text-xs uppercase tracking-widest text-zinc-400 font-bold">Assistindo</p>
                                <p class="mt-1 text-2xl font-extrabold text-blue-300 drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">{{ $watching->count() }}</p>
                            </div>
                        @endif

                        @if($user->show_completed_public)
                            <div class="rounded-2xl border border-white/20 bg-white/5 backdrop-blur-md p-4 text-center ring-1 ring-white/10 shadow-inner">
                                <p class="text-xs uppercase tracking-widest text-zinc-400 font-bold">Concluídos</p>
                                <p class="mt-1 text-2xl font-extrabold text-green-300 drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)]">{{ $completed->count() }}</p>
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

                                {{-- Lógica de Botões --}}
                                @if(auth()->check())
                                    <div class="flex gap-2 mt-3 flex-wrap">
                                        @if(auth()->id() === $user->id)
                                            {{-- VISÃO DO DONO --}}
                                            <form method="POST" action="{{ route('personal.animes.favorite', $plan->anime->id) }}">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 text-xs rounded {{ $myMeta && $myMeta->is_favorite ? 'bg-yellow-500 text-black' : 'bg-gray-700 hover:bg-gray-600' }}">
                                                    {{ $myMeta && $myMeta->is_favorite ? '★ Favorito' : '☆ Favoritar' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('personal.animes.addTop', $plan->anime->id) }}">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 text-xs rounded bg-violet-600 hover:bg-violet-700">➕ Top</button>
                                            </form>
                                        @else
                                            {{-- VISÃO DO VISITANTE (Estilo Favoritar/Top) --}}
                                            <form method="POST" action="{{ route('personal.watch-plans.follow', $plan->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs rounded bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-600/40 transition">
                                                    📺 Acompanhar
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('completed.mark-watched', $plan->anime->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs rounded bg-green-600/20 text-green-400 border border-green-500/30 hover:bg-green-600/40 transition">
                                                    ✅ Já assisti
                                                </button>
                                            </form>
                                        @endif
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

                                {{-- Lógica de Botões --}}
                                @if(auth()->check())
                                    <div class="flex gap-2 mt-3 flex-wrap">
                                        @if(auth()->id() === $user->id)
                                            {{-- VISÃO DO DONO --}}
                                            <form method="POST" action="{{ route('personal.animes.favorite', $plan->anime->id) }}">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 text-xs rounded {{ $myMeta && $myMeta->is_favorite ? 'bg-yellow-500 text-black' : 'bg-gray-700 hover:bg-gray-600' }}">
                                                    {{ $myMeta && $myMeta->is_favorite ? '★ Favorito' : '☆ Favoritar' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('personal.animes.addTop', $plan->anime->id) }}">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 text-xs rounded bg-violet-600 hover:bg-violet-700">➕ Top</button>
                                            </form>
                                        @else
                                            {{-- VISÃO DO VISITANTE (Estilo Favoritar/Top) --}}
                                            <form method="POST" action="{{ route('completed.mark-watched', $plan->anime->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs rounded bg-green-600/20 text-green-400 border border-green-500/30 hover:bg-green-600/40 transition">
                                                    ✅ Já assisti
                                                </button>
                                            </form>
                                            
                                            <a href="{{ route('anime.show', $plan->anime->id) }}" 
                                            class="px-2 py-1 text-xs rounded bg-zinc-800 text-zinc-300 border border-white/10 hover:bg-zinc-700 transition">
                                                ℹ️ Detalhes
                                            </a>
                                        @endif
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