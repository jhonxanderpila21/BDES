<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * AttachmentSeeder (Legacy alias for ComponentsAndPartsSeeder)
 * 
 * Maintained for backward-compatibility. Delegates to ComponentsAndPartsSeeder.
 */
class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ComponentsAndPartsSeeder::class);
    }
}
