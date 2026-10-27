<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call Seeders in dependency order (UserSeeder first to provide actor references)
        $this->call([
            UserSeeder::class,
            AssetCategorySeeder::class,
            AssetTypeSeeder::class,
            LocationSeeder::class,
            AssetSeeder::class,
            AssetFinancialsSeeder::class,
            DeploymentSeeder::class,
            MaintenanceSeeder::class,
            InspectionSeeder::class,
            AssetNeedSeeder::class,
            OperationalRequirementSeeder::class,
            MaintenanceScheduleSeeder::class,
            AssetAccessorySeeder::class,
            AttachmentSeeder::class,
            ReportedAssetSeeder::class,
            AssetHistorySeeder::class,
        ]);
    }
}
