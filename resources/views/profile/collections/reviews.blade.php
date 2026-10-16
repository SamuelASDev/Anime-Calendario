        @if ($owner || $user->show_reviews_public)
            <section data-async-collection id="reviews">
                <div class="section-heading">
                    <div>
                        <h2><x-icon name="star" />Avaliações</h2>
                        <p>Impressões sobre cada jornada.</p>
                    </div>
                </div>
                <div class="horizontal-grid">
                    @forelse($reviews as $item)
                    <x-profile-anime-card :anime="$item->anime" :meta="$item" />@empty<div class="col-span-full">
                            <x-empty-state title="Ainda sem avaliações"
                                description="As notas e impressões sobre os animes aparecem aqui." /></div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $reviews->links() }}</div>
            </section>
        @endif
