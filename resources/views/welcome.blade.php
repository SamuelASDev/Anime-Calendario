<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Tracker</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-zinc-950 text-white antialiased">

    {{-- FUNDO DECORATIVO --}}
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-zinc-950"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 h-[420px] w-[420px] rounded-full bg-violet-600/20 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-[320px] w-[320px] rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(168,85,247,0.12),transparent_30%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.10),transparent_30%)]"></div>
    </div>

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-50 border-b border-white/10 bg-zinc-900/70 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="/images/logoSapo.png" alt="Anime Tracker" class="h-10 w-auto">
                <div>
                    <p class="font-black text-base sm:text-lg tracking-tight">Anime Tracker</p>
                    <p class="text-[11px] text-zinc-400 -mt-0.5">Organize. Acompanhe. Avalie.</p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 transition">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="px-4 py-2 text-sm rounded-xl bg-violet-600 hover:bg-violet-700 font-medium transition shadow-lg shadow-violet-900/30">
                    Registrar
                </a>
            </div>
        </div>
    </header>

    {{-- HERO --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 sm:pt-20 pb-14">
        <div class="grid lg:grid-cols-2 gap-10 items-center">

            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-violet-400/20 bg-violet-500/10 px-4 py-2 text-xs sm:text-sm text-violet-200 mb-6">
                    🎌 Seu espaço para acompanhar animes
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Organize seus animes
                    <span class="text-violet-400">do seu jeito</span>
                </h1>

                <p class="mt-6 max-w-2xl text-base sm:text-lg text-zinc-300 leading-relaxed">
                    O Anime Tracker reúne em um só lugar tudo o que você precisa para acompanhar animes:
                    calendário, progresso de episódios, concluídos, reviews, favoritos e ranking pessoal.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('register') }}"
                       class="px-6 py-3 rounded-2xl bg-violet-600 hover:bg-violet-700 font-semibold transition shadow-lg shadow-violet-900/30">
                        Começar agora
                    </a>

                    <a href="{{ route('login') }}"
                       class="px-6 py-3 rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition">
                        Já tenho conta
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-zinc-400">Organize</p>
                        <p class="font-semibold text-white mt-1">Seu espaço pessoal</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-zinc-400">Acompanhe</p>
                        <p class="font-semibold text-white mt-1">Progresso e calendário</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-zinc-400">Avalie</p>
                        <p class="font-semibold text-white mt-1">Reviews e Top 10</p>
                    </div>
                </div>
            </div>

            {{-- CARD DE APRESENTAÇÃO --}}
            <div class="relative">
                <div class="rounded-3xl border border-white/10 bg-zinc-900/80 backdrop-blur-xl p-5 sm:p-6 shadow-2xl">
                    <div class="grid gap-4">

                        <div class="rounded-2xl border border-violet-400/20 bg-violet-500/10 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm text-violet-200 font-semibold">👤 Meu espaço</p>
                                    <p class="mt-1 text-sm text-zinc-300">
                                        Adicione animes ao seu espaço, acompanhe episódios, pause, conclua e faça reviews.
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full bg-violet-500/20 px-3 py-1 text-xs text-violet-200 border border-violet-400/20">
                                    Pessoal
                                </span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-blue-400/20 bg-blue-500/10 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm text-blue-200 font-semibold">📺 Live / Global</p>
                                    <p class="mt-1 text-sm text-zinc-300">
                                        Veja os animes acompanhados na live, acompanhe o calendário global e puxe para o seu espaço.
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full bg-blue-500/20 px-3 py-1 text-xs text-blue-200 border border-blue-400/20">
                                    Global
                                </span>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-sm font-semibold text-white">⭐ Favoritos</p>
                                <p class="mt-1 text-sm text-zinc-400">
                                    Marque os animes que mais gosta.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-sm font-semibold text-white">🏆 Top 10</p>
                                <p class="mt-1 text-sm text-zinc-400">
                                    Monte e reorganize seu ranking.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-sm font-semibold text-white">📝 Reviews</p>
                                <p class="mt-1 text-sm text-zinc-400">
                                    Avalie e registre suas opiniões.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-sm font-semibold text-white">📅 Calendário</p>
                                <p class="mt-1 text-sm text-zinc-400">
                                    Veja o progresso dos seus animes.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- COMO FUNCIONA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="rounded-3xl border border-white/10 bg-zinc-900/70 backdrop-blur p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-2xl sm:text-3xl font-bold">Como funciona</h2>
                <p class="text-zinc-400 mt-2 text-sm sm:text-base">
                    Um fluxo simples para acompanhar animes sem bagunça.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-violet-300 font-bold text-sm">01</p>
                    <h3 class="mt-2 font-semibold text-lg">Busque um anime</h3>
                    <p class="mt-2 text-sm text-zinc-400">
                        Encontre um anime e adicione ele ao sistema geral ou ao seu espaço pessoal.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-violet-300 font-bold text-sm">02</p>
                    <h3 class="mt-2 font-semibold text-lg">Acompanhe episódios</h3>
                    <p class="mt-2 text-sm text-zinc-400">
                        Crie planos, veja calendário, registre histórico e acompanhe seu progresso.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-violet-300 font-bold text-sm">03</p>
                    <h3 class="mt-2 font-semibold text-lg">Organize seu perfil</h3>
                    <p class="mt-2 text-sm text-zinc-400">
                        Favorite, ranqueie no Top 10, conclua animes e publique suas reviews.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-bold">O que você encontra aqui</h2>
            <p class="text-zinc-400 mt-2 text-sm sm:text-base">
                Recursos pensados para acompanhar animes de forma organizada.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-2">📺 Acompanhe</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Controle episódios assistidos, progresso atual e o que ainda falta ver.
                </p>
            </div>

            <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-2">📅 Calendário</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Veja o calendário global da live e também o seu calendário pessoal.
                </p>
            </div>

            <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-2">📝 Reviews</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Registre notas, comentários e acompanhe reviews de outros usuários.
                </p>
            </div>

            <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-2">⭐ Favoritos</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Marque os animes que mais gosta e destaque eles no seu perfil.
                </p>
            </div>

            <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-2">🏆 Top 10</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Crie um ranking pessoal e reorganize as posições do seu jeito.
                </p>
            </div>

            <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-2">🌐 Perfil público</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Mostre seu perfil, favoritos, concluídos, reviews e Top 10 para outros usuários.
                </p>
            </div>
        </div>
    </section>

    {{-- CTA FINAL --}}
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="rounded-3xl border border-violet-400/20 bg-violet-500/10 p-8 text-center shadow-xl">
            <h2 class="text-2xl sm:text-3xl font-bold mb-3">
                Pronto para montar seu espaço de animes?
            </h2>

            <p class="text-zinc-300 max-w-2xl mx-auto mb-6">
                Crie sua conta e comece a acompanhar animes, montar seu Top 10, favoritar títulos e registrar reviews.
            </p>

            <div class="flex justify-center gap-4 flex-wrap">
                <a href="{{ route('register') }}"
                   class="px-6 py-3 rounded-2xl bg-violet-600 hover:bg-violet-700 font-semibold transition">
                    Criar conta
                </a>

                <a href="{{ route('login') }}"
                   class="px-6 py-3 rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition">
                    Entrar
                </a>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-white/10 text-center py-6 text-zinc-500 text-sm">
        © {{ date('Y') }} Anime Tracker
    </footer>

</body>
</html>