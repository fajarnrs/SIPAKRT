<?php

namespace App\Providers;

use App\Models\Household;
use App\Models\Resident;
use App\Observers\HouseholdObserver;
use App\Observers\ResidentObserver;
use Filament\Facades\Filament;
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
        // Register observers
        Household::observe(HouseholdObserver::class);
        Resident::observe(ResidentObserver::class);

        Filament::serving(function () {
            Filament::registerRenderHook('head.end', function (): string {
                return <<<'HTML'
                    <style>
                        .filament-sidebar-group p.uppercase {
                            text-transform: none !important;
                        }
                    </style>
                HTML;
            });
        });
    }
}
