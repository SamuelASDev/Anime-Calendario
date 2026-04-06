<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="/images/logoSapo.png" class="h-12 w-auto" alt="Logo">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden sm:flex sm:items-center space-x-6 ms-6">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('anime.search')" :active="request()->routeIs('anime.search')">
                        🎬 Buscar
                    </x-nav-link>

                    <x-nav-link :href="route('calendar')" :active="request()->routeIs('calendar')">
                        📅 Calendário
                    </x-nav-link>

                    <x-nav-link :href="route('history')" :active="request()->routeIs('history')">
                        🕒 Histórico
                    </x-nav-link>

                    <x-nav-link :href="route('completed.animes')" :active="request()->routeIs('completed.animes')">
                        ✅ Concluídos
                    </x-nav-link>

                    @if(auth()->user()?->role === 'admin')
                        <x-nav-link :href="route('admin.animes.index')" :active="request()->routeIs('admin.animes.*')">
                            🛠 Admin
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('users.index')">
                        👥 Usuários
                    </x-nav-link>

                    <!-- Meu Espaço Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">

                            👤 Meu espaço

                            <svg class="ms-1 h-4 w-4 transform transition-transform"
                                :class="{ 'rotate-180': open }"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg z-50">
                            
                            <a href="{{ route('profile.show', Auth::user()->username) }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                 👤 Perfil
                            </a>

                            <a href="{{ route('personal.calendar') }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                📅 Meu calendário
                            </a>

                            <a href="{{ route('personal.history') }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                📘 Meu histórico
                            </a>

                            <a href="{{ route('personal.completed') }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                ⭐ Meus concluídos
                            </a>

                            <a href="{{ route('personal.animes.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                🎛 Gerenciar meus animes
                            </a>

                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- User Dropdown Desktop -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div class="max-w-[120px] truncate">{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Configurações
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Sair
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />

                        <path :class="{ 'hidden': !open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden bg-gray-900 border-t border-gray-700">
        <div class="pt-2 pb-3 space-y-1 px-2">

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('anime.search')" :active="request()->routeIs('anime.search')">
                🎬 Buscar Anime
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('calendar')" :active="request()->routeIs('calendar')">
                📅 Calendário
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('history')" :active="request()->routeIs('history')">
                🕒 Histórico
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('completed.animes')" :active="request()->routeIs('completed.animes')">
                ✅ Concluídos
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('completed.animes')">
                👥 Usuários
            </x-responsive-nav-link>

            @if(auth()->user()?->role === 'admin')
                <x-responsive-nav-link :href="route('admin.animes.index')" :active="request()->routeIs('admin.animes.*')">
                    🛠 Admin
                </x-responsive-nav-link>
            @endif

                        <!-- Meu Espaço Mobile -->
            <div x-data="{ openPersonal: false }">

                <button @click="openPersonal = !openPersonal"
                    class="w-full text-left px-3 py-2 text-white flex justify-between items-center">
                    👤 Meu espaço
                    <span x-text="openPersonal ? '−' : '+'"></span>
                </button>

                <div x-show="openPersonal" x-transition class="space-y-1">
                
                    <x-responsive-nav-link :href="route('profile.show', Auth::user()->username)">
                        👤 Perfil
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('personal.calendar')">
                        📅 Meu calendário
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('personal.history')">
                        📘 Meu histórico
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('personal.completed')">
                        ⭐ Meus concluídos
                    </x-responsive-nav-link>

                     <x-responsive-nav-link :href="route('personal.animes.index')">
                        🎛 Gerenciar meus animes
                    </x-responsive-nav-link>


                </div>
            </div>
        </div>

        <!-- Mobile User Info -->
        <div class="pt-4 pb-3 border-t border-gray-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-400 break-all">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1 px-2">

                <x-responsive-nav-link :href="route('profile.edit')">
                    Configurações
                </x-responsive-nav-link>'

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Sair
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>