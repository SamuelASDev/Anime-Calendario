<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Tracker</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-zinc-950 text-white">

    {{-- NAVBAR --}}
    <header class="border-b border-white/10 bg-zinc-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">

            <h1 class="font-bold text-lg">
                🎌 Anime Tracker
            </h1>

            <div class="flex gap-3">
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm rounded-lg bg-white/10 hover:bg-white/20 transition">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="px-4 py-2 text-sm rounded-lg bg-violet-600 hover:bg-violet-700 transition">
                    Registrar
                </a>
            </div>

        </div>
    </header>

    {{-- HERO --}}
    <section class="max-w-6xl mx-auto px-4 py-20 text-center">

        <h2 class="text-3xl sm:text-5xl font-black mb-6">
            Organize seus animes de forma simples 🚀
        </h2>

        <p class="text-zinc-400 max-w-2xl mx-auto mb-8">
            Acompanhe episódios, marque favoritos, crie seu top 10 e veja o progresso dos seus animes em um só lugar.
        </p>

        <div class="flex justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}"
               class="px-6 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 font-semibold transition">
                Começar agora
            </a>

            <a href="{{ route('login') }}"
               class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 transition">
                Já tenho conta
            </a>
        </div>

    </section>

    {{-- FEATURES --}}
    <section class="max-w-6xl mx-auto px-4 pb-20 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-2">📺 Acompanhe</h3>
            <p class="text-zinc-400 text-sm">
                Controle episódios e progresso dos seus animes.
            </p>
        </div>

        <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-2">⭐ Favoritos</h3>
            <p class="text-zinc-400 text-sm">
                Marque seus animes favoritos facilmente.
            </p>
        </div>

        <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-2">🏆 Top 10</h3>
            <p class="text-zinc-400 text-sm">
                Monte seu ranking pessoal de animes.
            </p>
        </div>

    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-white/10 text-center py-6 text-zinc-500 text-sm">
        © {{ date('Y') }} Anime Tracker
    </footer>

</body>
</html>