<nav data-async-summary="nav" class="profile-nav" aria-label="Seções do perfil">
    @if (($owner || $user->show_top10_public) && $top10->count())
        <a href="#top10">Top 10</a>
    @endif
    @if ($owner || $user->show_favorites_public)
        <a href="#favorites">Favoritos</a>
    @endif
    @if ($owner || $user->show_watching_public)
        <a href="#watching">Assistindo</a>
    @endif
    @if ($owner || $user->show_completed_public)
        <a href="#completed">Concluídos</a>
    @endif
    @if ($owner)
        <a href="#planned">Em espera</a>
    @endif
    @if ($owner || $user->show_reviews_public)
        <a href="#reviews">Avaliações</a>
    @endif
</nav>
