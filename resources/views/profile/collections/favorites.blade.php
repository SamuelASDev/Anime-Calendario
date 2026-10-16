        @if ($owner || $user->show_favorites_public)
            <section data-async-collection id="favorites" class="mb-10">
                <div class="section-heading">
                    <div>
                        <h2><x-icon name="star" />Favoritos<span class="section-count">{{ $favorites->total() }}</span>
                        </h2>
                        <p>Aqueles animes para guardar por perto.</p>
                    </div>
                </div>
                <div class="favorite-grid">
                    @forelse($favorites as $item)
                        <x-profile-anime-card :anime="$item->anime" :meta="$item" :owner="$owner" :poster="true"
                        collection="favorites" />@empty<div class="col-span-full"><x-empty-state
                                title="Nenhum favorito encontrado"
                                description="Os animes marcados como favoritos aparecem aqui." icon="star" /></div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $favorites->links() }}</div>
            </section>
        @endif
