<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\DashboardHelper;
use App\Helpers\AtividadesHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('dashboardHelper', function ($app) {
            return new DashboardHelper();
        });

        $this->app->singleton('atividadesHelper', function ($app) {
            return new AtividadesHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
