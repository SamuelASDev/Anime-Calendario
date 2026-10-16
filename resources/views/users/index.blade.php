<x-app-layout>
    <div class="page-wrap">
        <x-page-heading eyebrow="HISTÓRIAS MELHORES QUANDO COMPARTILHADAS" title="Pessoas da comunidade" description="Conheça outras coleções, encontre favoritos em comum e descubra novas recomendações." />
        <form method="GET" class="filter-panel" role="search" aria-label="Buscar pessoas"><div class="filter-primary"><div class="search-field"><x-icon name="search" /><input aria-label="Nome ou usuário" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar pelo nome ou @usuário…" maxlength="200"></div><label class="filter-sort"><select name="sort" aria-label="Ordenar pessoas"><option value="name" @selected(request('sort', 'name') === 'name')>Nome: A–Z</option><option value="recent" @selected(request('sort') === 'recent')>Novos na comunidade</option></select></label><button class="btn btn-primary">Buscar pessoas</button>@if(request('q'))<a class="btn btn-quiet" href="{{ route('users.index') }}">Limpar</a>@endif</div></form>
        <x-validation-summary />
        <div class="user-grid">@forelse($users as $user)<a class="community-user" href="{{ route('profile.show', $user->username) }}">@if($user->profile_photo_url)<img loading="lazy" src="{{ $user->profile_photo_url }}" alt="">@else<span class="community-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>@endif<div class="min-w-0"><h2 class="truncate">{{ $user->name }}</h2><p class="truncate">{{ '@'.$user->username }}</p></div><x-icon name="arrow" class="ml-auto text-slate-600" /></a>@empty<div class="col-span-full"><x-empty-state title="Nenhuma pessoa encontrada" description="Tente outro nome ou nome de usuário." icon="users" /></div>@endforelse</div>
        <div class="mt-8">{{ $users->links() }}</div>
    </div>
</x-app-layout>
