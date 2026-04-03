<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- BOAS VINDAS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-2">👋 Bem-vindo ao Anime Tracker</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Organize seus animes, acompanhe seu progresso e registre suas reviews em um só lugar.
                </p>
            </div>

            {{-- AÇÕES RÁPIDAS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-4">🚀 Ações rápidas</h3>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('anime.search') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Buscar anime
                    </a>

                    <a href="{{ route('calendar') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                        📅 Ver calendário
                    </a>

                    <a href="{{ route('admin.animes.index') }}"
                       class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm">
                        ⚙️ Gerenciar animes
                    </a>
                </div>
            </div>

            {{-- SOBRE O SISTEMA --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg text-white font-semibold mb-3">📌 Sobre o sistema</h3>

                <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                    <li>✔️ Acompanhe episódios assistidos</li>
                    <li>✔️ Crie planos de visualização</li>
                    <li>✔️ Marque animes como concluídos ou pausados</li>
                    <li>✔️ Avalie e escreva reviews</li>
                    <li>✔️ Visualize seu progresso no calendário</li>
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>