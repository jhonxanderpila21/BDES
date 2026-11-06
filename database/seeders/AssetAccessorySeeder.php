<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * AssetAccessorySeeder (Legacy alias for ComponentsAndPartsSeeder)
 * 
 * Maintained for backward-compatibility. Delegates to ComponentsAndPartsSeeder.
 */
class AssetAccessorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ComponentsAndPartsSeeder::class);
    }
}
