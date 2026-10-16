<div data-async-summary="stats" class="profile-stats" aria-label="Estatísticas da coleção">
    <x-profile-stat label="Favoritos" :value="$stats['favorites']" icon="star" section="favorites" tone="favorites" />
    <x-profile-stat label="Assistindo" :value="$stats['watching']" icon="play" section="watching" tone="watching" />
    <x-profile-stat label="Concluídos" :value="$stats['completed']" icon="check" section="completed" tone="completed" />
    <x-profile-stat label="Em espera" :value="$stats['planned']" icon="bookmark" section="planned" tone="planned" />
</div>
