<x-app-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 text-white">

        <h1 class="text-2xl font-bold mb-6">Usuários</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($users as $user)
                <a href="{{ route('profile.show', $user->username) }}"
                   class="bg-zinc-900 border border-white/10 rounded-xl p-4 hover:bg-zinc-800 transition">

                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-violet-500 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <p class="font-semibold truncate">{{ $user->name }}</p>
                            <p class="text-sm text-zinc-400 truncate">{{ '@'.$user->username }}</p>
                        </div>
                    </div>

                </a>
            @endforeach
        </div>

    </div>
</x-app-layout>