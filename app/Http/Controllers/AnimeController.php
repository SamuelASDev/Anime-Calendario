<?php

namespace App\Http\Controllers;

use App\Services\JikanService;
use Illuminate\Http\Request;
use App\Models\Anime;
use App\Services\TranslationService;

class AnimeController extends Controller
{
    public function search(Request $request, JikanService $jikan)
    {
        $results = [];

        if ($request->q) {
            $data = $jikan->search($request->q);
            $results = $data['data'];
        }

        return view('anime.search', compact('results'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mal_id' => ['required'],
            'title' => ['required', 'string'],
            'episodes' => ['nullable'],
            'synopsis' => ['nullable'],
            'status' => ['nullable'],
            'image' => ['nullable'],
            'target' => ['required', 'in:global,personal'],
        ]);

        $anime = Anime::firstOrCreate(
            ['mal_id' => $request->mal_id],
            [
                'title' => $request->title,
                'episodes' => $request->episodes ?: null,
                'synopsis' => $request->synopsis,
                'anime_status' => $request->status,
                'image' => $request->image,
            ]
        );

        if ($request->target === 'global') {
            abort_unless(auth()->user()?->role === 'admin', 403);

            return redirect()->route('watch-plans.create', $anime->id);
        }

        return redirect()->route('personal.watch-plans.create', $anime->id);
    }

    public function show(Anime $anime)
    {
        $anime->load([
            'userMeta' => function ($query) {
                $query->with('user')->latest();
            }
        ]);

        return view('anime.show', compact('anime'));
    }

    public function editSynopsis(Anime $anime)
    {
        return view('anime.edit-synopsis', compact('anime'));
    }

    public function updateSynopsis(Request $request, Anime $anime)
    {
        $request->validate([
            'synopsis' => ['nullable', 'string', 'max:5000'],
        ]);

        $anime->update([
            'synopsis' => $request->synopsis,
        ]);

        return redirect()
            ->route('anime.show', $anime->id)
            ->with('success', 'Sinopse atualizada com sucesso.');
    }

    public function allAnimeIndex()
    {
        $user = auth()->user();

        $animes = \App\Models\Anime::with([
            'userMeta.user',
        ])->latest()->paginate(24);

        $myWatchingAnimeIds = [];
        $myCompletedAnimeIds = [];

        if ($user) {
            $myWatchingAnimeIds = \App\Models\WatchPlan::where('user_id', $user->id)
                ->where('watch_status', 'assistindo')
                ->pluck('anime_id')
                ->toArray();

            $myCompletedAnimeIds = \App\Models\WatchPlan::where('user_id', $user->id)
                ->where('watch_status', 'concluido')
                ->pluck('anime_id')
                ->toArray();
        }

        return view('anime.index-all', compact(
            'animes',
            'myWatchingAnimeIds',
            'myCompletedAnimeIds'
        ));
    }

}