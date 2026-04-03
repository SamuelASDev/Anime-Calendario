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

        return redirect()->route('watch-plans.create', $anime->id);
    }
}