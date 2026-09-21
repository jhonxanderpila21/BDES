<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Deployment;
use App\Models\Maintenance;
use App\Policies\AssetPolicy;
use App\Policies\DeploymentPolicy;
use App\Policies\MaintenancePolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(Deployment::class, DeploymentPolicy::class);
        Gate::policy(Maintenance::class, MaintenancePolicy::class);
    }
}
