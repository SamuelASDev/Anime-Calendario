<?php

namespace App\Http\Controllers;

use App\Models\WatchPlan;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $liveWatchingAnimes = WatchPlan::global()->with('anime', 'days', 'logs', 'pauses')
            ->where('watch_status', 'assistindo')->latest('updated_at')->limit(6)->get();

        return view('dashboard', compact('liveWatchingAnimes'));
    }
}
