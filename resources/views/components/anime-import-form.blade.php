@props(['anime', 'target' => 'personal'])
@php($allTags = collect($anime['genres'] ?? [])->merge($anime['themes'] ?? [])->merge($anime['demographics'] ?? [])->pluck('name')->filter()->unique()->implode(', '))
<form method="POST" action="{{ route('anime.store') }}">
    @csrf
    <input type="hidden" name="mal_id" value="{{ $anime['mal_id'] }}">
    <input type="hidden" name="title" value="{{ $anime['title'] }}">
    <input type="hidden" name="episodes" value="{{ $anime['episodes'] ?? '' }}">
    <input type="hidden" name="synopsis" value="{{ $anime['synopsis'] ?? '' }}">
    <input type="hidden" name="status" value="{{ $anime['status'] ?? '' }}">
    <input type="hidden" name="image" value="{{ $anime['images']['jpg']['image_url'] }}">
    <input type="hidden" name="genres" value="{{ $allTags }}">
    <input type="hidden" name="target" value="{{ $target }}">
    <button class="{{ $target === 'later' ? 'bg-amber-600 hover:bg-amber-500' : 'bg-indigo-600 hover:bg-indigo-500' }} text-white text-sm px-3 py-2 rounded-lg w-full">{{ match ($target) { 'global' => 'Adicionar à comunidade', 'later' => 'Pretendo assistir', default => 'Adicionar à minha biblioteca' } }}</button>
</form>
