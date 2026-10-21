<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAccessory;
use Illuminate\Database\Seeder;

class AssetAccessorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gen001 = Asset::where('code', 'GEN-001')->first() ?? Asset::first();
        $med001 = Asset::where('code', 'AED-001')->orWhere('code', 'FAK-001')->first() ?? Asset::first();
        $com001 = Asset::where('code', 'RAD-001')->orWhere('code', 'RAD-002')->first() ?? Asset::first();
        $res001 = Asset::where('code', 'ROP-001')->orWhere('code', 'FE-001')->first() ?? Asset::first();

        $accessories = [
            // AED Pads / Medical Equipment / Qty 5 / ₱500 → Available
            [
                'asset_id' => $med001?->id,
                'asset_type' => 'Medical Equipment',
                'accessory_name' => 'AED Pads',
                'quantity' => 5,
                'unit_cost' => 500.00,
            ],
            // Generator Fuel Can / Generator / Qty 2 / ₱300 → Available
            [
                'asset_id' => $gen001?->id,
                'asset_type' => 'Generator',
                'accessory_name' => 'Generator Fuel Can',
                'quantity' => 2,
                'unit_cost' => 300.00,
            ],
            // Radio Charger / Communication Device / Qty 0 / ₱150 → Not Available
            [
                'asset_id' => $com001?->id,
                'asset_type' => 'Communication Device',
                'accessory_name' => 'Radio Charger',
                'quantity' => 0,
                'unit_cost' => 150.00,
            ],
            // Rope Bag / Rescue Equipment / Qty 3 / ₱800 → Available
            [
                'asset_id' => $res001?->id,
                'asset_type' => 'Rescue Equipment',
                'accessory_name' => 'Rope Bag',
                'quantity' => 3,
                'unit_cost' => 800.00,
            ],
        ];

        foreach ($accessories as $acc) {
            if (!empty($acc['asset_id'])) {
                AssetAccessory::updateOrCreate(
                    [
                        'asset_id' => $acc['asset_id'],
                        'accessory_name' => $acc['accessory_name'],
                    ],
                    $acc
                );
            }
        }
    }
}
