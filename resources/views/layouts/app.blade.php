<!DOCTYPE html>
<html class="dark" lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101b2b"><title>{{ $title ?? 'Anime Tracker · Sua próxima história' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] btn btn-primary">Pular para o conteúdo</a>
    @include('layouts.navigation')
    <main id="main-content" class="app-main" tabindex="-1">
        @isset($header)<header class="page-wrap pb-0">{{ $header }}</header>@endisset
        {{ $slot }}
    </main>
    <x-site-footer />
</body>
</html>
