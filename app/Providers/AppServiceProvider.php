<?php

namespace App\Providers;

use App\Models\SchoolClass;
use App\Models\SystemSetting;
use App\Policies\SchoolClassPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(SchoolClass::class, SchoolClassPolicy::class);

        View::composer(
            [
                'layouts.navigation',
                'layouts.app',
                'layouts.guest',
                'dashboard',
                'reports.index',
                'reports.print',
                'settings.edit',
                'fees.receipt',
                'fees.additional-receipt',
            ],
            function ($view) {
                $view->with('systemSettings', SystemSetting::current());
            }
        );
    }
}
