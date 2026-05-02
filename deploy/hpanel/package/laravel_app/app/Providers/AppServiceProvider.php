<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer(
            [
                'layouts.navigation',
                'layouts.app',
                'layouts.guest',
                'dashboard',
                'reports.index',
                'reports.print',
                'settings.edit',
            ],
            function ($view) {
                $view->with('systemSettings', SystemSetting::current());
            }
        );
    }
}
