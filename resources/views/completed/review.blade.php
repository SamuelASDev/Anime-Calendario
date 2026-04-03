<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6 text-white">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 sm:p-6">

            <div class="mb-4">
                <a href="{{ route('completed.animes') }}"
                   class="inline-block bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">
                    ← Voltar
                </a>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <img src="{{ $anime->image }}"
                     alt="{{ $anime->title }}"
                     class="w-24 h-32 object-cover rounded shrink-0">

                <div>
                    <h1 class="text-xl font-bold">{{ $anime->title }}</h1>
                    <p class="text-sm text-gray-300 mt-1">
                        Episódios: {{ $anime->episodes ?? 'Não definido' }}
                    </p>
                    <p class="text-sm text-gray-400">
                        Status: {{ $anime->anime_status }}
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('completed.review.store', $anime->id) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-2 text-sm font-medium">Nota</label>
                    <input
                        type="number"
                        name="rating"
                        min="1"
                        max="10"
                        value="{{ $myMeta->rating ?? '' }}"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 text-sm"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Comentário</label>
                    <textarea
                        name="comment"
                        rows="5"
                        class="w-full rounded bg-gray-200 border border-gray-600 px-3 py-2 text-sm"
                        style="color: #111827 !important; -webkit-text-fill-color: #111827 !important;"
                    >{{ $myMeta->comment ?? '' }}</textarea>
                </div>

                <button class="bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded text-white font-semibold">
                    Salvar review
                </button>
            </form>
        </div>
    </div>
</x-app-layout>