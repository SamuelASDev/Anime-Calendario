@props(['label', 'value', 'icon', 'section', 'tone'])
@if($value !== null)
    <a href="#{{ $section }}" class="profile-stat stat-{{ $tone }}" aria-label="{{ $value }} {{ $label }}. Ir para a seção">
        <div><span>{{ $label }}</span><strong>{{ $value }}</strong></div><x-icon :name="$icon" />
    </a>
@else
    <div class="profile-stat stat-private"><div><span>{{ $label }}</span><strong>Privado</strong></div><x-icon name="shield" /></div>
@endif
