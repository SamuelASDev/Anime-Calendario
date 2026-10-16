<a href="{{ auth()->check() ? route('dashboard') : route('home') }}" {{ $attributes->class(['brand']) }} aria-label="Anime Tracker, início">
    <span class="brand-mark"><img src="/images/logoSapo.png" alt="" width="36" height="36"></span>
    <span>anime<span class="brand-accent">tracker</span><small>UM EPISÓDIO DE CADA VEZ</small></span>
</a>
