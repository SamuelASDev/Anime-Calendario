<section data-async-collection id="top10" class="mb-10" @if (!$top10->count()) hidden @endif>
    @if (($owner || $user->show_top10_public) && $top10->count())
        <div class="section-heading">
            <div>
                <h2><x-icon name="spark" />Top 10</h2>
                <p>As histórias que ficaram com {{ $owner ? 'você' : $user->name }}.</p>
            </div><span class="eyebrow mb-0">FAVORITOS EM ORDEM</span>
        </div>
        <div class="top-grid">
            @foreach ($top10 as $item)
                <x-profile-anime-card :anime="$item->anime" :meta="$item" :owner="$owner" collection="top10" />
            @endforeach
        </div>
    @endif
</section>
