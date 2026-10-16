@props(['anime', 'target' => 'personal'])
@php($allTags = implode(', ', $anime->genres))
<form method="POST" action="{{ route('anime.store') }}">
    @csrf
    <input type="hidden" name="mal_id" value="{{ $anime->malId }}">
    <input type="hidden" name="title" value="{{ $anime->title }}">
    <input type="hidden" name="episodes" value="{{ $anime->episodes ?? '' }}">
    <input type="hidden" name="synopsis" value="{{ $anime->synopsis ?? '' }}">
    <input type="hidden" name="status" value="{{ $anime->status ?? '' }}">
    <input type="hidden" name="image" value="{{ $anime->imageUrl }}">
    <input type="hidden" name="genres" value="{{ $allTags }}">
    <input type="hidden" name="target" value="{{ $target }}">
    <button
        class="btn btn-secondary btn-sm w-full">{{ match ($target) {'global' => 'Adicionar à comunidade','later' => 'Pretendo assistir',default => 'Adicionar à minha biblioteca'} }}</button>
</form>
