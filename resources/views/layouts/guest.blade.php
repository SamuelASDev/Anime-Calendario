<!DOCTYPE html>
<html class="dark" lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#0c0f16"><title>Anime Tracker · Sua conta</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body>
    <main class="auth-shell">
        <aside class="auth-story">
            <x-brand />
            <div><p class="eyebrow">SUA PRÓXIMA HISTÓRIA COMEÇA AQUI</p><h1>Mais histórias.<br>Menos <em>“em qual episódio parei?”</em></h1><p>Organize sua lista, encontre novas histórias e acompanhe tudo no seu ritmo.</p><div class="auth-visual" aria-hidden="true"><span><x-icon name="book" /></span><span><x-icon name="play" /></span><span><x-icon name="star" /></span></div></div>
            <p class="text-xs">Um lugar para quem sempre tem mais um anime na lista.</p>
        </aside>
        <section class="auth-form-side"><div class="auth-form-box"><div class="mb-10 lg:hidden"><x-brand /></div>{{ $slot }}</div></section>
    </main>
</body></html>
