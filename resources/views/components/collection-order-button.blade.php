@props(['anime', 'collection', 'direction'])

@php
    $label = $direction === 'up' ? 'Subir' : 'Descer';
    $location = match ($collection) {
        'favorites' => 'em Favoritos',
        'completed' => 'em Concluídos',
        default => 'no Top 10',
    };
    $url = $collection === 'top10'
        ? route('personal.animes.top.'.$direction, $anime)
        : route('personal.animes.collection-order', $anime);
@endphp

<form data-collection-action method="POST" action="{{ $url }}">
    @csrf
    @method('PATCH')
    @if ($collection !== 'top10')
        <input type="hidden" name="collection" value="{{ $collection }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
    @endif
    <button class="btn btn-quiet btn-sm" title="{{ $label }} {{ $location }}" aria-label="{{ $label }} {{ $anime->title }} {{ $location }}">
        <x-icon :name="$direction === 'up' ? 'up' : 'chevron'" />
    </button>
</form>
