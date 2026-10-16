<?php

namespace App\Providers;

use App\Queries\AnimeFilters;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('components.pagination');
        View::composer('components.list-filters', function ($view) {
            $view->with(app(AnimeFilters::class)->options());
        });
    }
}
