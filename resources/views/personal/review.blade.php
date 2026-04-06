<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6 text-white">

        <h1 class="text-2xl font-bold mb-6">
            Avaliar anime
        </h1>

        <div class="bg-gray-800 rounded-lg p-4 shadow-md">

            <div class="flex flex-col sm:flex-row gap-4">

                {{-- IMAGEM --}}
                <div class="flex-shrink-0">
                    <img src="{{ $anime->image }}"
                         alt="{{ $anime->title }}"
                         class="w-40 rounded-lg shadow">
                </div>

                {{-- INFO --}}
                <div class="flex-1">
                    <h2 class="text-xl font-semibold mb-2">
                        {{ $anime->title }}
                    </h2>

                    @if($anime->episodes)
                        <p class="text-sm text-gray-400 mb-2">
                            Episódios: {{ $anime->episodes }}
                        </p>
                    @endif
                </div>

            </div>

            {{-- FORM --}}
            <form action="{{ route('personal.completed.review.store', $anime->id) }}"
                  method="POST"
                  class="mt-6 space-y-4">

                @csrf

                {{-- NOTA --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">
                        Nota (1 a 10)
                    </label>
                    <input type="number"
                           name="rating"
                           min="1"
                           max="10"
                           value="{{ old('rating', $myMeta->rating ?? '') }}"
                           class="w-full rounded bg-gray-900 border border-gray-700 text-white p-2">
                </div>

                {{-- COMENTÁRIO --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">
                        Comentário
                    </label>
                    <textarea name="comment"
                              rows="4"
                              class="w-full rounded bg-gray-900 border border-gray-700 text-white p-2">{{ old('comment', $myMeta->comment ?? '') }}</textarea>
                </div>

                {{-- BOTÕES --}}
                <div class="flex justify-between items-center mt-4">

                    <a href="{{ route('personal.completed') }}"
                       class="text-sm text-gray-400 hover:text-white">
                        ← Voltar
                    </a>

                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded text-white font-semibold">
                        Salvar review
                    </button>

                </div>

            </form>

        </div>
    </div>
</x-app-layout>