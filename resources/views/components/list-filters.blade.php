@props(['scope' => 'library', 'statuses' => false])
<form method="GET" action="{{ url()->current() }}" class="filter-panel" role="search" aria-label="Filtrar animes">
    <div class="filter-primary">
        <div class="search-field"><x-icon name="search" /><label class="sr-only" for="list-search">Buscar pelo
                título</label><input id="list-search" type="search" name="q" value="{{ request('q') }}"
                placeholder="Encontre um anime pelo título…" maxlength="200"></div>
        @if ($scope !== 'profile')
            <label class="filter-sort"><span class="sr-only">Ordenar por</span><select name="sort"
                    aria-label="Ordenar por">
                    <option value="recent" @selected(request('sort', 'recent') === 'recent')>Adicionados recentemente</option>
                    <option value="title" @selected(request('sort') === 'title')>Título: A–Z</option>
                    @if ($scope === 'catalog')
                        <option value="rating" @selected(request('sort') === 'rating')>Melhor avaliação</option>
                        <option value="episodes" @selected(request('sort') === 'episodes')>Mais episódios</option>
                    @else
                        <option value="start_date" @selected(request('sort') === 'start_date')>Data de início</option>
                    @endif
                </select></label>
        @endif
        <button class="btn btn-primary" type="submit"><x-icon name="filter" />Aplicar filtros</button>
    </div>
    <div class="filter-secondary">
        <label><span>Gênero</span><select name="genre">
                <option value="">Todos os gêneros</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>
        </label>
        @if ($statuses)
            <label><span>Minha lista</span><select name="status">
                    <option value="">Todos os status</option>
                    @foreach (\App\Enums\WatchStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}
                        </option>
                        @endforeach @if ($scope === 'catalog')
                            <option value="unlisted" @selected(request('status') === 'unlisted')>Fora da minha lista</option>
                        @endif
                </select>
            </label>
        @endif
        @if ($scope === 'catalog')
            <label><span>Exibição</span><select name="airing">
                    <option value="">Qualquer situação</option>
                    @foreach ($airingStatuses as $status)
                        <option value="{{ $status }}" @selected(request('airing') === $status)>
                            {{ ['Currently Airing' => 'Em exibição', 'Finished Airing' => 'Finalizado', 'Not yet aired' => 'Ainda não estreou'][$status] ?? $status }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label><span>Duração</span><select name="length">
                    <option value="">Qualquer duração</option>
                    @foreach (['short' => 'Até 13 episódios', 'medium' => '14 a 26 episódios', 'long' => 'Mais de 26 episódios', 'unknown' => 'Total indefinido'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('length') === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
        @endif
        @if ($scope !== 'profile')
            <label class="filter-check"><input type="checkbox" name="favorite" value="1"
                    @checked(request()->boolean('favorite'))><x-icon name="star" /> Só meus favoritos</label>
        @endif
        @if (request()->hasAny(['q', 'genre', 'status', 'sort', 'favorite', 'airing', 'length']))
            <a class="filter-clear" href="{{ url()->current() }}"><x-icon name="close" /> Limpar filtros</a>
        @endif
    </div>
</form>
