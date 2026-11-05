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
        $myStatuses = WatchPlan::ownedBy(auth()->id())->pluck('watch_status', 'anime_id');
        // Shared with the watch-later button so the page does not repeat this query.
        request()->attributes->set('viewer_watch_statuses', $myStatuses);

        return view('dashboard', compact('liveWatchingAnimes', 'personalPlans', 'libraryCounts', 'myStatuses'));
    }
}
