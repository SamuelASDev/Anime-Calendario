<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Services\Catalog\AnimeMetadataSync;

class AnimeMetadataController extends Controller
{
    public function index(AnimeMetadataSync $sync)
    {
        return view('admin.animes.metadata', [
            'status' => $sync->status(),
            'failures' => Anime::whereNotNull('metadata_sync_error')->orderBy('title')->limit(50)->get(['id', 'title', 'mal_id', 'metadata_sync_error']),
        ]);
    }

    public function sync(AnimeMetadataSync $sync)
    {
        return response()->json($sync->next() + ['status' => $sync->status()]);
    }

    public function retryFailed(AnimeMetadataSync $sync)
    {
        $count = $sync->retryFailed();

        return redirect()->route('admin.animes.metadata')->with('success', $count.' anime(s) voltaram para a fila.');
    }
}
