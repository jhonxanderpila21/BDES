<?php

/**
 * Simple Database Migration Runner
 * Run from terminal: php artisan migrate:runner
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Include Laravel bootstrap
$basePath = __DIR__;
require_once $basePath . '/vendor/autoload.php';

// Create Laravel app instance
$app = require_once $basePath . '/bootstrap/app.php';

// Get the migration repository
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Access database
$db = $app['db'];

// Get migration files
$migrationsPath = $basePath . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

echo "=== BDES Database Migration Runner ===\n\n";

echo "Found " . count($files) . " migration files:\n";
foreach ($files as $file) {
    echo "  - " . basename($file) . "\n";
}

echo "\n";

// Check if we should run migrations
if (php_sapi_name() === 'cli') {
    echo "Starting migrations...\n\n";
    
    try {
        // Run migrations
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
        $exitCode = $kernel->call('migrate', ['--force' => true]);
        
        echo "\n✅ Migrations completed successfully!\n";
        exit($exitCode);
    } catch (Exception $e) {
        echo "\n❌ Migration error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
