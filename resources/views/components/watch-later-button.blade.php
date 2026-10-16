@props(['anime', 'status' => null])
@php
    if (auth()->check()) {
        if (! request()->attributes->has('viewer_watch_statuses')) {
            request()->attributes->set('viewer_watch_statuses', \App\Models\WatchPlan::ownedBy(auth()->id())->pluck('watch_status', 'anime_id'));
        }
        $status = request()->attributes->get('viewer_watch_statuses')->get($anime->id, $status);
    }
@endphp
@if($status === 'pretendo_assistir')
    <a href="{{ route('personal.watch-later.index') }}" class="btn btn-secondary btn-sm">✓ Pretendo assistir</a>
@elseif($status === null)
    <form method="POST" action="{{ route('personal.watch-later.store', $anime) }}">@csrf<button class="btn btn-secondary btn-sm"><x-icon name="bookmark" />Pretendo assistir</button></form>
@endif
