            @if ($owner || $visible)
                <section data-async-collection id="{{ $id }}" class="mb-10">
                    <div class="section-heading">
                        <div>
                            <h2><x-icon :name="$icon" />{{ $title }}<span
                                    class="section-count">{{ $items->total() }}</span></h2>
                            <p>{{ $description }}</p>
                        </div>
                        @if ($owner)
                            <a
                                href="{{ route('personal.animes.index', ['status' => $id === 'watching' ? 'assistindo' : 'concluido']) }}">Gerenciar
                                lista <x-icon name="arrow" class="w-3 h-3" /></a>
                        @endif
                    </div>
                    <div class="horizontal-grid">
                        @forelse($items as $plan)
                            <x-profile-anime-card :anime="$plan->anime" :plan="$plan" :owner="$owner"
                            :meta="$owner ? $plan->anime->userMeta->first() : null" :collection="$id === 'completed' ? 'completed' : null" />@empty<div class="col-span-full"><x-empty-state
                                    title="Nenhum anime nesta seção"
                                    description="Explore o catálogo ou ajuste os filtros para encontrar mais histórias."
                                    :icon="$icon" /></div>
                        @endforelse
                    </div>
                    <div class="mt-5">{{ $items->links() }}</div>
                </section>
            @endif
