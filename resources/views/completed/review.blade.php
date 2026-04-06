<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6 text-white">

        <h1 class="text-2xl font-bold mb-6">
            Avaliar anime
        </h1>

        <div class="bg-gray-800 rounded-lg p-4 shadow-md">

            {{-- BADGE GLOBAL --}}
            <div class="mb-4">
                <span class="inline-block bg-yellow-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                    🌍 Review global
                </span>
            </div>

            {{-- BOTÃO VOLTAR --}}
            <div class="mb-4">
                <a href="{{ route('completed.animes') }}"
                   class="text-sm text-gray-400 hover:text-white">
                    ← Voltar
                </a>
            </div>

            {{-- HEADER --}}
            <div class="flex flex-col sm:flex-row gap-4 mb-6">

                <div class="flex-shrink-0">
                    <img src="{{ $anime->image }}"
                         alt="{{ $anime->title }}"
                         class="w-32 sm:w-40 rounded-lg shadow">
                </div>

                <div class="flex-1">
                    <h2 class="text-xl font-semibold mb-2">
                        {{ $anime->title }}
                    </h2>

                    <p class="text-sm text-gray-400">
                        Episódios: {{ $anime->episodes ?? 'Não definido' }}
                    </p>

                    <p class="text-sm text-gray-400 mb-2">
                        Status: {{ $anime->anime_status }}
                    </p>
                </div>

            </div>

            {{-- ERROS --}}
            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM --}}
            <form method="POST"
                  action="{{ route('completed.review.store', $anime->id) }}"
                  class="space-y-4">

                @csrf

                {{-- NOTA --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">
                        Nota (1 a 10)
                    </label>

                    <input
                        type="number"
                        name="rating"
                        min="1"
                        max="10"
                        value="{{ old('rating', $myMeta->rating ?? '') }}"
                        class="w-full rounded bg-gray-900 border border-gray-700 text-white p-2"
                    >
                </div>

                {{-- COMENTÁRIO --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">
                        Comentário
                    </label>

                    <textarea
                        name="comment"
                        rows="4"
                        class="w-full rounded bg-gray-900 border border-gray-700 text-white p-2"
                    >{{ old('comment', $myMeta->comment ?? '') }}</textarea>
                </div>

                {{-- AÇÕES --}}
                <div class="flex justify-between items-center mt-4">

                    <a href="{{ route('completed.animes') }}"
                       class="text-sm text-gray-400 hover:text-white">
                        ← Voltar
                    </a>

                    <button type="submit"
                            class="bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded text-white font-semibold">
                        Salvar review
                    </button>

                </div>

            </form>

        </div>
    </div>
</x-app-layout>