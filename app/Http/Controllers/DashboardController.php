<?php

namespace App\Http\Controllers;

use App\Models\WatchPlan;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $liveWatchingAnimes = WatchPlan::global()->with('anime', 'days', 'logs', 'pauses')
            ->where('watch_status', 'assistindo')->latest('updated_at')->limit(6)->get();
        $personalPlans = WatchPlan::ownedBy(auth()->id())->with('anime', 'days', 'logs', 'pauses')->where('watch_status', 'assistindo')->latest('updated_at')->limit(4)->get();
        $libraryCounts = WatchPlan::ownedBy(auth()->id())->selectRaw('watch_status, COUNT(*) as total')->groupBy('watch_status')->pluck('total', 'watch_status');

        return view('dashboard', compact('liveWatchingAnimes', 'personalPlans', 'libraryCounts'));
    }
}
