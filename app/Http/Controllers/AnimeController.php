<?php

namespace App\Http\Controllers;

use App\Services\JikanService;
use Illuminate\Http\Request;
use App\Models\Anime;

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
}