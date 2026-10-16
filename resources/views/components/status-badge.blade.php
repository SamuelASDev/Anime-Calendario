@props(['status' => null])
<span {{ $attributes->class(['status-tag', 'status-'.$status]) }}>{{ \App\Enums\WatchStatus::tryFrom($status ?? '')?->label() ?? 'No catálogo' }}</span>
