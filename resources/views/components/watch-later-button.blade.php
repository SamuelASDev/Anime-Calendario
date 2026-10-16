@props(['anime', 'status' => null])
@if($status === 'pretendo_assistir')
    <a href="{{ route('personal.watch-later.index') }}" class="inline-block rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-200">✓ Pretendo assistir</a>
@elseif($status === null)
    <form method="POST" action="{{ route('personal.watch-later.store', $anime) }}">
        @csrf
        <button class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-200 hover:bg-amber-500/20">＋ Pretendo assistir</button>
    </form>
@endif
