<?php

namespace App\Providers;

use App\Contracts\AnimeCatalogProvider;
use App\Queries\AnimeFilters;
use App\Services\Catalog\TenraiProvider;
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
        $this->app->bind(AnimeCatalogProvider::class, TenraiProvider::class);
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
