<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetNeed;
use Illuminate\Database\Seeder;

class AssetNeedSeeder extends Seeder
{
    /**
     * Seed asset needs covering both unit types and all three status outcomes.
     */
    public function run(): void
    {
        // Look up assets by their code to get the correct IDs
        $gen001 = Asset::where('code', 'GEN-001')->first();
        $gen002 = Asset::where('code', 'GEN-002')->first();
        $aed001 = Asset::where('code', 'AED-001')->first();
        $rad001 = Asset::where('code', 'RAD-001')->first();
        $saw001 = Asset::where('code', 'SAW-001')->first();

        $needs = [
            // Generator — Diesel Fuel at 50/100 → Monitoring Only
            [
                'asset_id'         => $gen001?->id,
                'asset_type'       => 'Generator',
                'requirement'      => 'Diesel Fuel',
                'unit'             => 'Liters',
                'maximum_capacity' => 100,
                'minimum_level'    => 20,
                'current_level'    => 50,
            ],
            // Generator — Diesel Fuel at 15/100 (below min 20) → Requires Maintenance
            [
                'asset_id'         => $gen002?->id,
                'asset_type'       => 'Generator',
                'requirement'      => 'Diesel Fuel',
                'unit'             => 'Liters',
                'maximum_capacity' => 100,
                'minimum_level'    => 20,
                'current_level'    => 15,
            ],
            // AED — Battery Pack at 0/4 → Under Maintenance
            [
                'asset_id'         => $aed001?->id,
                'asset_type'       => 'AED',
                'requirement'      => 'Battery Pack',
                'unit'             => 'Units',
                'maximum_capacity' => 4,
                'minimum_level'    => 1,
                'current_level'    => 0,
            ],
            // AED — Electrode Pads at 6/6 (fully stocked) → Monitoring Only
            [
                'asset_id'         => $aed001?->id,
                'asset_type'       => 'AED',
                'requirement'      => 'Electrode Pads',
                'unit'             => 'Units',
                'maximum_capacity' => 6,
                'minimum_level'    => 2,
                'current_level'    => 6,
            ],
            // Radio — Spare Battery at 1/3 (at minimum) → Monitoring Only
            [
                'asset_id'         => $rad001?->id,
                'asset_type'       => 'Radio',
                'requirement'      => 'Spare Battery',
                'unit'             => 'Units',
                'maximum_capacity' => 3,
                'minimum_level'    => 1,
                'current_level'    => 1,
            ],
            // Chainsaw — Bar & Chain Oil at 0/5 → Under Maintenance
            [
                'asset_id'         => $saw001?->id,
                'asset_type'       => 'Chainsaw',
                'requirement'      => 'Bar & Chain Oil',
                'unit'             => 'Liters',
                'maximum_capacity' => 5,
                'minimum_level'    => 1,
                'current_level'    => 0,
            ],
        ];

        foreach ($needs as $need) {
            if ($need['asset_id']) {
                AssetNeed::create($need);
            }
        }
    }
}
