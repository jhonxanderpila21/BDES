<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Deployment;
use App\Models\Maintenance;
use App\Policies\AssetPolicy;
use App\Policies\DeploymentPolicy;
use App\Policies\MaintenancePolicy;
use App\Services\NotificationService;
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
        $this->ensureEnvironmentReadiness();

        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(Deployment::class, DeploymentPolicy::class);
        Gate::policy(Maintenance::class, MaintenancePolicy::class);

        // Share real-time notifications with the admin top navigation bar
        View::composer('admin.partials.navbar', function ($view) {
            $notificationService = app(NotificationService::class);
            $view->with('navbarNotifications', $notificationService->getRecentNotifications(15));
        });
    }

    /**
     * Ensure storage directories and SQLite database file exist on fresh environments.
     */
    protected function ensureEnvironmentReadiness(): void
    {
        // 1. Ensure essential storage directories exist
        $storagePaths = [
            storage_path('framework/views'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            storage_path('app/public'),
        ];

        foreach ($storagePaths as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }

        // 2. Ensure SQLite database file exists if using sqlite connection
        if (config('database.default') === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && $dbPath !== ':memory:' && !str_contains($dbPath, 'mode=memory') && !str_starts_with($dbPath, 'file:')) {
                $resolved = realpath($dbPath) ?: (function_exists('base_path') ? realpath(base_path($dbPath)) : false);
                if ($resolved === false) {
                    $target = (str_starts_with($dbPath, DIRECTORY_SEPARATOR) || preg_match('/^[a-zA-Z]:/', $dbPath))
                        ? $dbPath
                        : base_path($dbPath);

                    $dir = dirname($target);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }

                    if (file_exists(base_path('capstone_db')) && realpath(base_path('capstone_db')) !== realpath($target)) {
                        @copy(base_path('capstone_db'), $target);
                    } else {
                        @touch($target);
                    }
                }
            }
        }
    }
}
