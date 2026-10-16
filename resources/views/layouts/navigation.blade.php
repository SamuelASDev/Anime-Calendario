<header class="site-header" x-data="{ mobileOpen: false, accountOpen: false }" @keydown.escape.window="mobileOpen = false; accountOpen = false">
    <div class="nav-wrap">
        <x-brand />
        <nav class="nav-links desktop-nav" aria-label="Navegação principal">
            @foreach([
                ['dashboard', 'grid', 'Início', 'dashboard'],
                ['anime.index.all', 'book', 'Explorar', 'anime.*'],
                ['personal.animes.index', 'bookmark', 'Minha lista', 'personal.*'],
                ['calendar', 'calendar', 'Comunidade', 'calendar,history,completed.*'],
                ['users.index', 'users', 'Pessoas', 'users.*'],
            ] as [$route, $icon, $label, $patterns])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs(...explode(',', $patterns)) ? 'is-active' : '' }}" @if(request()->routeIs(...explode(',', $patterns))) aria-current="page" @endif><x-icon :name="$icon" />{{ $label }}</a>
            @endforeach
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.animes.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}"><x-icon name="shield" />Admin</a>@endif
        </nav>
        <div class="relative desktop-account" @click.outside="accountOpen = false">
            <button class="user-trigger" @click="accountOpen = !accountOpen" :aria-expanded="accountOpen" aria-controls="account-menu">
                @if(auth()->user()->profile_photo_url)<img src="{{ auth()->user()->profile_photo_url }}" alt="">@else<span class="avatar-mini">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>@endif
                <span class="max-w-[110px] truncate">{{ auth()->user()->name }}</span><x-icon name="chevron" class="w-3 h-3" />
            </button>
            <nav id="account-menu" x-show="accountOpen" x-cloak x-transition class="nav-popover" aria-label="Sua conta">
                <a href="{{ auth()->user()->username ? route('profile.show', auth()->user()->username) : route('profile.edit') }}"><x-icon name="user" />Meu perfil</a>
                <a href="{{ route('personal.watch-later.index') }}"><x-icon name="bookmark" />Pretendo assistir</a>
                <a href="{{ route('personal.calendar') }}"><x-icon name="calendar" />Meu calendário</a>
                <a href="{{ route('personal.completed') }}"><x-icon name="check" />Meus concluídos</a>
                <a href="{{ route('personal.history') }}"><x-icon name="clock" />Meu histórico</a>
                <a href="{{ route('profile.edit') }}"><x-icon name="settings" />Configurações</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button><x-icon name="logout" />Sair da conta</button></form>
            </nav>
        </div>
        <button class="btn btn-secondary btn-sm mobile-toggle" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen" aria-controls="mobile-navigation" aria-label="Abrir menu"><x-icon name="menu" /></button>
    </div>
    <nav id="mobile-navigation" class="mobile-nav" x-show="mobileOpen" x-cloak aria-label="Navegação móvel">
        @foreach(['dashboard' => 'Início', 'anime.index.all' => 'Explorar animes', 'anime.search' => 'Buscar novos animes', 'personal.animes.index' => 'Minha lista', 'personal.watch-later.index' => 'Pretendo assistir', 'personal.calendar' => 'Meu calendário', 'personal.history' => 'Meu histórico', 'calendar' => 'Comunidade', 'users.index' => 'Pessoas'] as $route => $label)<a class="nav-link {{ request()->routeIs($route) ? 'is-active' : '' }}" href="{{ route($route) }}">{{ $label }}</a>@endforeach
        <a class="nav-link" href="{{ auth()->user()->username ? route('profile.show', auth()->user()->username) : route('profile.edit') }}">Meu perfil</a>
        <a class="nav-link" href="{{ route('profile.edit') }}">Configurações</a>
        @if(auth()->user()->isAdmin())<a class="nav-link" href="{{ route('admin.animes.index') }}">Administração</a>@endif
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link w-full">Sair da conta</button></form>
    </nav>
</header>
