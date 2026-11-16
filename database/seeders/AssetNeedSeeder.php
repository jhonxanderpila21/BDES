<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetNeed;
use Illuminate\Database\Seeder;

class AssetNeedSeeder extends Seeder
{
    /**
     * Seed asset needs covering the Fire Trucks, Chainsaws, Jacks, and Generator.
     */
    public function run(): void
    {
        $tanker     = Asset::where('code', 'TRK-001')->first();
        $penetrator = Asset::where('code', 'TRK-002')->first();
        $chainsaw   = Asset::where('code', 'SAW-001')->first();
        $jack       = Asset::where('code', 'JCK-001')->first();
        $gen001     = Asset::where('code', 'GEN-001')->first();

        $needs = [
            // Fire Truck – Tanker — Diesel Fuel at 160/200 -> Monitoring Only
            [
                'asset_id'         => $tanker?->id,
                'asset_type'       => 'Fire Truck',
                'requirement'      => 'Diesel Fuel (Tanker)',
                'unit'             => 'Liters',
                'maximum_capacity' => 200,
                'minimum_level'    => 50,
                'current_level'    => 160,
            ],
            // Fire Truck – Penetrator — Diesel Fuel at 35/150 (below min 40) -> Requires Maintenance / Refill
            [
                'asset_id'         => $penetrator?->id,
                'asset_type'       => 'Fire Truck',
                'requirement'      => 'Diesel Fuel (Penetrator)',
                'unit'             => 'Liters',
                'maximum_capacity' => 150,
                'minimum_level'    => 40,
                'current_level'    => 35,
            ],
            // Chainsaws (2 large, 2 small) — Bar & Chain Oil at 0/8 -> Under Maintenance
            [
                'asset_id'         => $chainsaw?->id,
                'asset_type'       => 'Chainsaw',
                'requirement'      => 'Bar & Chain Oil (4 Chainsaws)',
                'unit'             => 'Liters',
                'maximum_capacity' => 8,
                'minimum_level'    => 2,
                'current_level'    => 0,
            ],
            // Chainsaws (2 large, 2 small) — 2-Stroke Fuel Premix at 10/15 -> Monitoring Only
            [
                'asset_id'         => $chainsaw?->id,
                'asset_type'       => 'Chainsaw',
                'requirement'      => '2-Stroke Fuel Premix (4 Chainsaws)',
                'unit'             => 'Liters',
                'maximum_capacity' => 15,
                'minimum_level'    => 4,
                'current_level'    => 10,
            ],
            // Hydraulic Jacks (3 units) — Hydraulic Oil at 3/4 -> Monitoring Only
            [
                'asset_id'         => $jack?->id,
                'asset_type'       => 'Hydraulic & Lifting Tool',
                'requirement'      => 'Hydraulic Jack Fluid (3 Jacks)',
                'unit'             => 'Liters',
                'maximum_capacity' => 4,
                'minimum_level'    => 1,
                'current_level'    => 3,
            ],
            // Standby Generator — Diesel Fuel at 40/50 -> Monitoring Only
            [
                'asset_id'         => $gen001?->id,
                'asset_type'       => 'Generator',
                'requirement'      => 'Diesel Fuel',
                'unit'             => 'Liters',
                'maximum_capacity' => 50,
                'minimum_level'    => 15,
                'current_level'    => 40,
            ],
        ];

        foreach ($needs as $need) {
            if ($need['asset_id']) {
                AssetNeed::create($need);
            }
        }
    }
}
