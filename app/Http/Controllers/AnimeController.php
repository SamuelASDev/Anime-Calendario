<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportAnimeRequest;
use App\Http\Requests\ListingFiltersRequest;
use App\Models\Anime;
use App\Models\WatchPlan;
use App\Queries\AnimeCatalogQuery;
use App\Services\AnimeSearchService;
use App\Services\Planning\PlanManager;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    public function search(Request $request, AnimeSearchService $catalogSearch)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $results = [];
        $searchError = null;
        if (! empty($data['q'])) {
            try {
                $results = $catalogSearch->search($data['q']);
            } catch (\RuntimeException $exception) {
                $searchError = 'A busca está indisponível no momento. Tente novamente em instantes.';
            }
        }

        $localAnimes = Anime::whereIn('mal_id', collect($results)->pluck('mal_id'))->pluck('id', 'mal_id');

        return view('anime.search', compact('results', 'searchError', 'localAnimes'));
    }

    public function store(ImportAnimeRequest $request, PlanManager $manager)
    {
        $data = $request->validated();
        $anime = Anime::firstOrCreate(['mal_id' => $data['mal_id']], [
            'title' => $data['title'], 'episodes' => $data['episodes'] ?? null,
            'synopsis' => $data['synopsis'] ?? null, 'anime_status' => $data['status'] ?? null,
            'image' => $data['image'] ?? null, 'genres' => $data['genres'] ?? null,
        ]);
        if ($data['target'] === 'later') {
            $plan = $manager->saveForLater($anime, $request->user());
            if ($plan->watch_status !== 'pretendo_assistir') {
                return redirect()->route('personal.animes.index')->with('success', 'Este anime já faz parte da sua biblioteca. Seu status foi mantido.');
            }

            return redirect()->route('personal.watch-later.index')->with('success', 'Anime disponível na sua biblioteca para assistir depois.');
        }

        return redirect()->route($data['target'] === 'global' ? 'watch-plans.create' : 'personal.watch-plans.create', $anime);
    }

    public function show(Anime $anime)
    {
        $reviews = $anime->userMeta()->publiclyVisible()->with('user')
            ->where(fn ($q) => $q->whereNotNull('rating')->orWhereNotNull('comment'))->latest()->paginate(12);

        $myPlanStatus = WatchPlan::ownedBy(auth()->id())->where('anime_id', $anime->id)->value('watch_status');

        return view('anime.show', compact('anime', 'reviews', 'myPlanStatus'));
    }

    public function editSynopsis(Anime $anime)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('anime.edit-synopsis', compact('anime'));
    }

    public function updateSynopsis(Request $request, Anime $anime)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate(['synopsis' => ['nullable', 'string', 'max:20000']]);
        $anime->update([filled($anime->synopsis_pt) ? 'synopsis_pt' : 'synopsis' => $data['synopsis']]);

        return redirect()->route('anime.show', $anime)->with('success', 'Sinopse atualizada.');
    }

    public function allAnimeIndex(ListingFiltersRequest $request, AnimeCatalogQuery $catalog)
    {
        $animes = $catalog->paginate($request->validated(), $request->user()->id);
        $statuses = WatchPlan::ownedBy($request->user()->id)->pluck('watch_status', 'anime_id');
        $myPlanStatuses = $statuses->all();
        $myWatchingAnimeIds = $statuses->filter(fn ($status) => in_array($status, ['assistindo', 'pausado']))->keys()->all();
        $myCompletedAnimeIds = $statuses->filter(fn ($status) => $status === 'concluido')->keys()->all();

        return view('anime.index-all', compact('animes', 'myWatchingAnimeIds', 'myCompletedAnimeIds', 'myPlanStatuses'));
    }
}
