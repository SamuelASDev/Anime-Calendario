<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Perfil público
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Configure sua bio, username e o que será exibido publicamente.
        </p>
    </header>

    <form method="post" action="{{ route('profile.public.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- BANNER --}}
        <div>
            <x-input-label for="profile_banner" value="Banner" />

            <div class="mt-2">
                <img 
                    src="{{ $user->profile_banner ? asset('storage/' . $user->profile_banner) : 'https://via.placeholder.com/800x200' }}"
                    class="w-full h-40 object-cover rounded-lg mb-2"
                >
            </div>

            <input type="file" name="profile_banner" class="block w-full text-sm text-white">
            <x-input-error class="mt-2" :messages="$errors->get('profile_banner')" />
        </div>

        {{-- FOTO --}}
        <div>
            <x-input-label for="profile_photo" value="Foto de perfil" />

            <div class="mt-2 flex items-center gap-4">
                <img 
                    src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : 'https://via.placeholder.com/150' }}"
                    class="w-20 h-20 rounded-full object-cover border"
                >
                <input type="file" name="profile_photo" class="text-sm text-white">
            </div>

            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        {{-- USERNAME --}}
        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                :value="old('username', $user->username)" required />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        {{-- BIO --}}
        <div>
            <x-input-label for="bio" value="Bio" />
            <textarea id="bio" name="bio" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        {{-- CONFIGS --}}
        <div class="space-y-3">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_public" value="1" {{ old('is_public', $user->is_public) ? 'checked' : '' }}>
                <span>Perfil público</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="show_watching_public" value="1" {{ old('show_watching_public', $user->show_watching_public) ? 'checked' : '' }}>
                <span>Mostrar assistindo</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="show_completed_public" value="1" {{ old('show_completed_public', $user->show_completed_public) ? 'checked' : '' }}>
                <span>Mostrar concluídos</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="show_favorites_public" value="1" {{ old('show_favorites_public', $user->show_favorites_public) ? 'checked' : '' }}>
                <span>Mostrar favoritos</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="show_top10_public" value="1" {{ old('show_top10_public', $user->show_top10_public) ? 'checked' : '' }}>
                <span>Mostrar top 10</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="show_reviews_public" value="1" {{ old('show_reviews_public', $user->show_reviews_public) ? 'checked' : '' }}>
                <span>Mostrar reviews</span>
            </label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar</x-primary-button>
        </div>
    </form>
</section>