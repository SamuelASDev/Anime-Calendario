        @if ($owner)
            <section data-async-collection id="planned" class="mb-10">
                <div class="section-heading">
                    <div>
                        <h2><x-icon name="bookmark" />Pretendo assistir<span
                                class="section-count">{{ $planned->total() }}</span></h2>
                        <p>Seu próximo capítulo. Esta seção é visível apenas para você.</p>
                    </div><a href="{{ route('personal.watch-later.index') }}">Gerenciar minha lista <x-icon name="arrow"
                            class="w-3 h-3" /></a>
                </div>
                <div class="horizontal-grid">
                    @forelse($planned as $plan)
                    <x-plan-card :plan="$plan" route-prefix="personal.animes" />@empty<div class="col-span-full">
                            <x-empty-state title="Sua próxima história está lá fora"
                                description="Guarde animes com Pretendo assistir para encontrá-los aqui."
                                icon="bookmark" /></div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $planned->links() }}</div>
            </section>
        @endif
