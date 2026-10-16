@props(['title' => 'Nada por aqui ainda', 'description' => 'Experimente outros filtros ou encontre seu próximo anime.', 'icon' => 'book'])
<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-symbol"><x-icon :name="$icon" /></span>
    <h3>{{ $title }}</h3><p>{{ $description }}</p>
    @if($slot->isNotEmpty())<div class="mt-5">{{ $slot }}</div>@endif
</div>
