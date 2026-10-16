<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListingFiltersRequest;
use App\Http\Requests\ReviewRequest;
use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;
use App\Queries\WatchPlanQuery;
use App\Services\Library\CollectionOrder;
use App\Services\Library\RankingManager;
use App\Services\Planning\PlanManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function moveCollection(Request $request, Anime $anime, CollectionOrder $order)
    {
        $data = $request->validate(['collection' => ['required', 'in:favorites,completed'], 'direction' => ['required', 'in:up,down']]);
        $order->move($request->user(), $anime->id, $data['collection'], $data['direction']);

        return back()->with('success', 'Ordem da coleção atualizada.');
    }

    public function watchLaterIndex(ListingFiltersRequest $request, WatchPlanQuery $query)
    {
        $plans = $query->filtered($request->user()->id, $request->validated())->where('watch_status', 'pretendo_assistir')
            ->paginate(12)->withQueryString();

        return view('personal.watch-later', compact('plans'));
    }

    public function saveForLater(Request $request, Anime $anime, PlanManager $manager)
    {
        $plan = $manager->saveForLater($anime, $request->user());
        $message = $plan->watch_status === 'pretendo_assistir' ? 'Anime guardado em Pretendo assistir.' : 'Este anime já faz parte da sua biblioteca. Seu status foi mantido.';

        return back()->with('success', $message);
    }

    public function markAsWatched(Anime $anime, PlanManager $manager)
    {
        DB::transaction(function () use ($anime, $manager) {
            Anime::whereKey($anime->id)->lockForUpdate()->firstOrFail();
            $plan = WatchPlan::firstOrCreate(['anime_id' => $anime->id, 'user_id' => auth()->id()], [
                'episodes_watched' => 0, 'episodes_per_day' => 0, 'start_date' => today()->toDateString(), 'watch_status' => 'assistindo',
            ]);
            $manager->transition($plan, 'concluido');
        });

        return redirect()->route('personal.completed.review.create', $anime)->with('success', 'Anime concluído. Você já pode avaliá-lo.');
    }

    public function createReview(Anime $anime)
    {
        abort_unless(WatchPlan::ownedBy(auth()->id())->where('anime_id', $anime->id)->where('watch_status', 'concluido')->exists(), 403);
        $myMeta = UserAnimeMeta::where('user_id', auth()->id())->where('anime_id', $anime->id)->first();

        return view('personal.review', compact('anime', 'myMeta'));
    }

    public function storeReview(ReviewRequest $request, Anime $anime)
    {
        DB::transaction(function () use ($request, $anime) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            UserAnimeMeta::updateOrCreate(['user_id' => $request->user()->id, 'anime_id' => $anime->id], $request->validated());
        });

        return redirect()->route('personal.completed')->with('success', 'Avaliação salva.');
    }

    public function toggleFavorite(Anime $anime)
    {
        DB::transaction(function () use ($anime) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $meta = UserAnimeMeta::firstOrCreate(['user_id' => auth()->id(), 'anime_id' => $anime->id]);
            $meta->update(['is_favorite' => ! $meta->is_favorite]);
        });

        return back()->with('success', 'Favorito atualizado.');
    }

    public function updateTopPosition(Request $request, Anime $anime, RankingManager $ranking)
    {
        $data = $request->validate(['top_position' => ['nullable', 'integer', 'min:1', 'max:10']]);
        $ranking->change($request->user(), $anime->id, 'position', $data['top_position'] ?? null);

        return back()->with('success', 'Top 10 atualizado.');
    }

    public function addToTop(Anime $anime, RankingManager $ranking)
    {
        return $this->rank($anime, $ranking, 'add');
    }

    public function removeFromTop(Anime $anime, RankingManager $ranking)
    {
        return $this->rank($anime, $ranking, 'remove');
    }

    public function moveTopUp(Anime $anime, RankingManager $ranking)
    {
        return $this->rank($anime, $ranking, 'up');
    }

    public function moveTopDown(Anime $anime, RankingManager $ranking)
    {
        return $this->rank($anime, $ranking, 'down');
    }

    private function rank(Anime $anime, RankingManager $ranking, string $action)
    {
        $ranking->change(auth()->user(), $anime->id, $action);

        return back()->with('success', 'Top 10 atualizado.');
    }
}
