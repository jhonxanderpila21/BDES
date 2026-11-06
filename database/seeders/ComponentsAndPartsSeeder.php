<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\Attachment;
use Illuminate\Database\Seeder;

/**
 * ComponentsAndPartsSeeder
 * 
 * Seeds realistic disaster equipment components, replacement parts, and accessories
 * matching the "Components & Parts" page in the UI.
 */
class ComponentsAndPartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gen001 = Asset::where('code', 'GEN-001')->first() ?? Asset::first();
        $saw001 = Asset::where('code', 'SAW-001')->first() ?? Asset::first();
        $str001 = Asset::where('code', 'STR-001')->first() ?? Asset::first();
        $bot001 = Asset::where('code', 'BOT-001')->first() ?? Asset::first();
        $veh001 = Asset::where('code', 'VEH-001')->first() ?? Asset::first();
        $med001 = Asset::where('code', 'AED-001')->orWhere('code', 'FAK-001')->first() ?? Asset::first();
        $com001 = Asset::where('code', 'RAD-001')->orWhere('code', 'RAD-002')->first() ?? Asset::first();
        $res001 = Asset::where('code', 'ROP-001')->orWhere('code', 'FE-001')->first() ?? Asset::first();

        // =========================================================================
        // 1. EQUIPMENT COMPONENTS (ATTACHMENTS)
        // =========================================================================
        $components = [
            [
                'name' => 'Generator Heavy-Duty Wheel Kit & Handle',
                'type' => 'Component',
                'asset_type_id' => $gen001?->type_id,
                'asset_id' => $gen001?->id,
                'quantity' => 1,
                'unit_cost' => 2400.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Chainsaw 24-inch Guide Bar & Chain',
                'type' => 'Component',
                'asset_type_id' => $saw001?->type_id,
                'asset_id' => $saw001?->id,
                'quantity' => 2,
                'unit_cost' => 1850.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Basket Stretcher Lifting Bridle Harness',
                'type' => 'Component',
                'asset_type_id' => $str001?->type_id,
                'asset_id' => $str001?->id,
                'quantity' => 2,
                'unit_cost' => 3200.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Rescue Boat Outboard Motor Propeller Guard',
                'type' => 'Component',
                'asset_type_id' => $bot001?->type_id,
                'asset_id' => $bot001?->id,
                'quantity' => 1,
                'unit_cost' => 4500.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Vehicle High-Output LED Scene Light Bar',
                'type' => 'Component',
                'asset_type_id' => $veh001?->type_id,
                'asset_id' => $veh001?->id,
                'quantity' => 1,
                'unit_cost' => 5800.00,
                'condition' => 'good',
            ],
            [
                'name' => 'High-Pressure Fire Discharge Hose (15m)',
                'type' => 'Component',
                'asset_type_id' => $veh001?->type_id,
                'asset_id' => $veh001?->id,
                'quantity' => 3,
                'unit_cost' => 1200.00,
                'condition' => 'fair',
            ],
            [
                'name' => 'Generator Air Filter & Carburetor Assembly',
                'type' => 'Component',
                'asset_type_id' => $gen001?->type_id,
                'asset_id' => $gen001?->id,
                'quantity' => 2,
                'unit_cost' => 850.00,
                'condition' => 'fair',
            ],
            [
                'name' => 'Worn Static Kernmantle Rescue Rope (Damaged Sheath)',
                'type' => 'Component',
                'asset_type_id' => $str001?->type_id,
                'asset_id' => $str001?->id,
                'quantity' => 1,
                'unit_cost' => 2100.00,
                'condition' => 'poor',
            ],
            [
                'name' => 'Exhaust Muffler Heat Deflector (Damaged)',
                'type' => 'Component',
                'asset_type_id' => $saw001?->type_id,
                'asset_id' => $saw001?->id,
                'quantity' => 0,
                'unit_cost' => 650.00,
                'condition' => 'poor',
            ],
        ];

        foreach ($components as $comp) {
            if (!empty($comp['asset_id'])) {
                Attachment::updateOrCreate(
                    [
                        'name' => $comp['name'],
                        'asset_id' => $comp['asset_id'],
                    ],
                    $comp
                );
            }
        }

        // =========================================================================
        // 2. REPLACEMENT PARTS & ACCESSORIES
        // =========================================================================
        $parts = [
            [
                'asset_id' => $med001?->id,
                'asset_type' => 'Medical Equipment',
                'accessory_name' => 'AED Electrode Pads Set',
                'quantity' => 5,
                'unit_cost' => 500.00,
            ],
            [
                'asset_id' => $gen001?->id,
                'asset_type' => 'Generator',
                'accessory_name' => 'Generator 20L Fuel Can',
                'quantity' => 2,
                'unit_cost' => 300.00,
            ],
            [
                'asset_id' => $com001?->id,
                'asset_type' => 'Communication Device',
                'accessory_name' => 'Handheld Radio Multi-Unit Rapid Charger',
                'quantity' => 0,
                'unit_cost' => 150.00,
            ],
            [
                'asset_id' => $res001?->id,
                'asset_type' => 'Rescue Equipment',
                'accessory_name' => 'Heavy-Duty Reinforced Rope Bag',
                'quantity' => 3,
                'unit_cost' => 800.00,
            ],
            [
                'asset_id' => $saw001?->id,
                'asset_type' => 'Rescue Equipment',
                'accessory_name' => 'Chainsaw Spark Plug Replacement Pack (Set of 4)',
                'quantity' => 4,
                'unit_cost' => 350.00,
            ],
        ];

        foreach ($parts as $part) {
            if (!empty($part['asset_id'])) {
                AssetAccessory::updateOrCreate(
                    [
                        'asset_id' => $part['asset_id'],
                        'accessory_name' => $part['accessory_name'],
                    ],
                    $part
                );
            }
        }
    }
}

