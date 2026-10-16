<x-app-layout>
    <x-slot:title>{{ $user->name }} · Anime Tracker</x-slot:title>
    @php($owner = auth()->id() === $user->id)
    <div class="page-wrap">
        <header class="profile-hero">
            <div class="profile-banner">
                @if ($user->profile_banner_url)
                    <img src="{{ $user->profile_banner_url }}" alt="Banner de {{ $user->name }}">
                @endif
                <span class="profile-banner-label">MINHA COLEÇÃO DE HISTÓRIAS</span>
            </div>
            <div class="profile-overview">
                <div class="profile-identity">
                    <div class="profile-avatar">
                        @if ($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}"
                                alt="Foto de {{ $user->name }}">@else{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="profile-name">
                        <h1>{{ $user->name }}</h1>
                        <p class="profile-handle">{{ '@' . $user->username }}</p>
                        @if ($user->bio)
                            <p class="profile-bio">{{ $user->bio }}</p>
                        @endif
                        <div class="profile-tools">
                            @if ($owner)
                                <a class="btn btn-secondary btn-sm" href="{{ route('profile.edit') }}"><x-icon
                                        name="settings" />Editar perfil</a>
                            @endif
                            @if (!$user->is_public)
                                <span class="status-tag"><x-icon name="shield" class="w-3 h-3" />Perfil privado</span>
                            @endif
                            <span class="text-xs text-slate-500">Na comunidade desde
                                {{ $user->created_at->format('m/Y') }}</span>
                        </div>
                    </div>
                </div>
                @include('profile.collections.stats')
            </div>
        </header>
        @if (session('success'))
            <p role="status" class="flash">{{ session('success') }}</p>
        @endif
        @include('profile.collections.nav')
        <p data-async-feedback role="status" aria-live="polite"></p><x-validation-summary /><x-list-filters
            scope="profile" />
        @include('profile.collections.top10')
        @include('profile.collections.favorites')
        @include('profile.collections.watching')
        @include('profile.collections.completed')
        @include('profile.collections.planned')
        @include('profile.collections.reviews')
    </div>
</x-app-layout>
