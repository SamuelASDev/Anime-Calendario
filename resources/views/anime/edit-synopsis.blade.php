<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6 text-white">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-6 shadow-xl">
            <h1 class="text-2xl font-bold mb-4">Editar sinopse</h1>

            <div class="mb-4">
                <p class="text-sm text-zinc-400">Anime</p>
                <p class="text-lg font-semibold">{{ $anime->title }}</p>
            </div>

            <form method="POST" action="{{ route('anime.synopsis.update', $anime->id) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="synopsis" class="block text-sm font-medium text-zinc-300 mb-2">
                        Sinopse
                    </label>

                    <textarea
                        id="synopsis"
                        name="synopsis"
                        rows="10"
                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white focus:border-blue-500 focus:outline-none"
                    >{{ old('synopsis', $anime->synopsis) }}</textarea>

                    @error('synopsis')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-500 transition"
                    >
                        Salvar
                    </button>

                    <a
                        href="{{ route('anime.show', $anime->id) }}"
                        class="rounded-lg bg-zinc-700 px-4 py-2 font-semibold text-white hover:bg-zinc-600 transition"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>