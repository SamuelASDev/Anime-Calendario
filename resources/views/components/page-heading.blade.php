@props(['eyebrow' => null, 'title', 'description' => null])
<div {{ $attributes->class(['page-heading']) }}>
    <div>
        @if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h1>{{ $title }}</h1>
        @if($description)<p class="page-description">{{ $description }}</p>@endif
    </div>
    @if(isset($actions))<div class="heading-actions">{{ $actions }}</div>@endif
</div>
