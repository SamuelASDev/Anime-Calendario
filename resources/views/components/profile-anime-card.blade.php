@props(['anime', 'meta' => null, 'plan' => null, 'owner' => false])
<article class="rounded-xl border border-gray-700 bg-gray-800 p-4 flex gap-4">
    <img loading="lazy" src="{{ $anime->image }}" alt="{{ $anime->title }}" class="w-20 h-28 object-cover rounded-lg shrink-0">
    <div class="min-w-0 flex-1 space-y-2">
        <a href="{{ route('anime.show', $anime) }}" class="font-semibold hover:text-indigo-300">{{ $anime->title }}</a>
        @if($meta?->top_position)<p class="text-yellow-300 font-semibold">#{{ $meta->top_position }}</p>@endif
        @if($meta?->rating)<p class="text-sm text-yellow-300">Nota: {{ $meta->rating }}/10</p>@endif
        @if($meta?->comment)<p class="text-sm text-gray-300 whitespace-pre-line break-words">{{ $meta->comment }}</p>@endif
        @if($owner)
            <div class="flex flex-wrap gap-3 text-sm">
                <form method="POST" action="{{ route('personal.animes.favorite', $anime) }}">@csrf @method('PATCH') <button class="text-yellow-300">{{ $meta?->is_favorite ? '★ Favorito' : '☆ Favoritar' }}</button></form>
                @if($meta?->top_position)
                    @foreach(['up' => 'Subir', 'down' => 'Descer'] as $action => $label)
                        <form method="POST" action="{{ route('personal.animes.top.'.$action, $anime) }}">@csrf @method('PATCH') <button class="text-indigo-300">{{ $label }}</button></form>
                    @endforeach
                    <form method="POST" action="{{ route('personal.animes.removeTop', $anime) }}">@csrf @method('PATCH') <button class="text-gray-300">Remover do Top</button></form>
                @else
                    <form method="POST" action="{{ route('personal.animes.addTop', $anime) }}">@csrf @method('PATCH') <button class="text-indigo-300">Top 10</button></form>
                @endif
            </div>
        @elseif($plan)
            <x-watch-later-button :anime="$anime" />
            @can('copy', $plan)
                <form method="POST" action="{{ route('personal.watch-plans.follow', $plan) }}">@csrf <button class="rounded bg-indigo-600 px-3 py-2 text-sm">Copiar agenda</button></form>
            @endcan
        @else
            <x-watch-later-button :anime="$anime" />
        @endif
    </div>
</article>
