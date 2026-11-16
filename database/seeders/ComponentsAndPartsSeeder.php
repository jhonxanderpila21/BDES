<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\Attachment;
use Illuminate\Database\Seeder;

/**
 * ComponentsAndPartsSeeder
 *
 * Seeds the fire truck equipment, hoses, rescue tools, clearing tools, and damaged items
 * matching the "Components & Parts" page in the UI.
 */
class ComponentsAndPartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tanker     = Asset::where('code', 'TRK-001')->first() ?? Asset::first();
        $penetrator = Asset::where('code', 'TRK-002')->first() ?? $tanker;
        $jack       = Asset::where('code', 'JCK-001')->first() ?? $tanker;
        $chainsaw   = Asset::where('code', 'SAW-001')->first() ?? $tanker;
        $machete    = Asset::where('code', 'MCH-001')->first() ?? $tanker;
        $rope       = Asset::where('code', 'ROP-001')->first() ?? $tanker;
        $rick       = Asset::where('code', 'RCK-001')->first() ?? $tanker;
        $bullCutter = Asset::where('code', 'BLC-001')->first() ?? $tanker;
        $cplStation = Asset::where('code', 'CPL-002')->first() ?? $tanker;
        $eplStation = Asset::where('code', 'EPL-002')->first() ?? $tanker;
        $vgpStation = Asset::where('code', 'VGP-002')->first() ?? $tanker;
        $ybtStation = Asset::where('code', 'YBT-002')->first() ?? $tanker;
        $dmgHose15  = Asset::where('code', 'FH-005')->first() ?? $tanker;
        $dmgHose25  = Asset::where('code', 'FH-006')->first() ?? $penetrator;

        // =========================================================================
        // 1. FIRE TRUCK & RESCUE EQUIPMENT COMPONENTS (ATTACHMENTS)
        // =========================================================================
        $components = [
            // 1½-inch fire hoses – 7 (Tanker), 6 (Penetrator)
            [
                'name'          => '1½-inch fire hoses (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 7,
                'unit_cost'     => 6500.00,
                'condition'     => 'good',
            ],
            [
                'name'          => '1½-inch fire hoses (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 6,
                'unit_cost'     => 6500.00,
                'condition'     => 'good',
            ],

            // 2½-inch fire hoses – 3 (Tanker), 2 (Penetrator)
            [
                'name'          => '2½-inch fire hoses (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 3,
                'unit_cost'     => 9500.00,
                'condition'     => 'good',
            ],
            [
                'name'          => '2½-inch fire hoses (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 2,
                'unit_cost'     => 9500.00,
                'condition'     => 'good',
            ],

            // 2½-inch hose peed – 1 (each truck)
            [
                'name'          => '2½-inch hose peed (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 1,
                'unit_cost'     => 8500.00,
                'condition'     => 'good',
            ],
            [
                'name'          => '2½-inch hose peed (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 1,
                'unit_cost'     => 8500.00,
                'condition'     => 'good',
            ],

            // Spanner – 1 (each truck)
            [
                'name'          => 'Spanner (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 1,
                'unit_cost'     => 650.00,
                'condition'     => 'good',
            ],
            [
                'name'          => 'Spanner (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 1,
                'unit_cost'     => 650.00,
                'condition'     => 'good',
            ],

            // Yabie tubo – 1 (each truck)
            [
                'name'          => 'Yabie tubo (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 1,
                'unit_cost'     => 1200.00,
                'condition'     => 'good',
            ],
            [
                'name'          => 'Yabie tubo (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 1,
                'unit_cost'     => 1200.00,
                'condition'     => 'good',
            ],

            // Vice grip – 1 (each truck)
            [
                'name'          => 'Vice grip (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 1,
                'unit_cost'     => 750.00,
                'condition'     => 'good',
            ],
            [
                'name'          => 'Vice grip (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 1,
                'unit_cost'     => 750.00,
                'condition'     => 'good',
            ],

            // Cutter pliers – 1 (each truck)
            [
                'name'          => 'Cutter pliers (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 1,
                'unit_cost'     => 550.00,
                'condition'     => 'good',
            ],
            [
                'name'          => 'Cutter pliers (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 1,
                'unit_cost'     => 550.00,
                'condition'     => 'good',
            ],

            // Electrical pliers – 1 (each truck)
            [
                'name'          => 'Electrical pliers (Tanker)',
                'type'          => 'Component',
                'asset_type_id' => $tanker?->type_id,
                'asset_id'      => $tanker?->id,
                'quantity'      => 1,
                'unit_cost'     => 650.00,
                'condition'     => 'good',
            ],
            [
                'name'          => 'Electrical pliers (Penetrator)',
                'type'          => 'Component',
                'asset_type_id' => $penetrator?->type_id,
                'asset_id'      => $penetrator?->id,
                'quantity'      => 1,
                'unit_cost'     => 650.00,
                'condition'     => 'good',
            ],

            // Jack – 3
            [
                'name'          => 'Jack',
                'type'          => 'Component',
                'asset_type_id' => $jack?->type_id,
                'asset_id'      => $jack?->id,
                'quantity'      => 3,
                'unit_cost'     => 3800.00,
                'condition'     => 'good',
            ],

            // Chainsaws – 4 (2 large, 2 small)
            [
                'name'          => 'Chainsaws (Large)',
                'type'          => 'Component',
                'asset_type_id' => $chainsaw?->type_id,
                'asset_id'      => $chainsaw?->id,
                'quantity'      => 2,
                'unit_cost'     => 32000.00,
                'condition'     => 'good',
            ],
            [
                'name'          => 'Chainsaws (Small)',
                'type'          => 'Component',
                'asset_type_id' => $chainsaw?->type_id,
                'asset_id'      => $chainsaw?->id,
                'quantity'      => 2,
                'unit_cost'     => 18500.00,
                'condition'     => 'good',
            ],

            // Machetes (Sundang) – 4
            [
                'name'          => 'Machetes (Sundang)',
                'type'          => 'Component',
                'asset_type_id' => $machete?->type_id,
                'asset_id'      => $machete?->id,
                'quantity'      => 4,
                'unit_cost'     => 600.00,
                'condition'     => 'good',
            ],

            // Ropes (Pisi) – 4
            [
                'name'          => 'Ropes (Pisi)',
                'type'          => 'Component',
                'asset_type_id' => $rope?->type_id,
                'asset_id'      => $rope?->id,
                'quantity'      => 4,
                'unit_cost'     => 2500.00,
                'condition'     => 'good',
            ],

            // Rick – 14
            [
                'name'          => 'Rick',
                'type'          => 'Component',
                'asset_type_id' => $rick?->type_id,
                'asset_id'      => $rick?->id,
                'quantity'      => 14,
                'unit_cost'     => 450.00,
                'condition'     => 'good',
            ],

            // Bull cutter – 1
            [
                'name'          => 'Bull cutter',
                'type'          => 'Component',
                'asset_type_id' => $bullCutter?->type_id,
                'asset_id'      => $bullCutter?->id,
                'quantity'      => 1,
                'unit_cost'     => 2800.00,
                'condition'     => 'good',
            ],

            // Cutter pliers – 2
            [
                'name'          => 'Cutter pliers',
                'type'          => 'Component',
                'asset_type_id' => $cplStation?->type_id,
                'asset_id'      => $cplStation?->id,
                'quantity'      => 2,
                'unit_cost'     => 550.00,
                'condition'     => 'good',
            ],

            // Electrical pliers – 2
            [
                'name'          => 'Electrical pliers',
                'type'          => 'Component',
                'asset_type_id' => $eplStation?->type_id,
                'asset_id'      => $eplStation?->id,
                'quantity'      => 2,
                'unit_cost'     => 650.00,
                'condition'     => 'good',
            ],

            // Vice grips – 2
            [
                'name'          => 'Vice grips',
                'type'          => 'Component',
                'asset_type_id' => $vgpStation?->type_id,
                'asset_id'      => $vgpStation?->id,
                'quantity'      => 2,
                'unit_cost'     => 750.00,
                'condition'     => 'good',
            ],

            // Yabie tubo – 2
            [
                'name'          => 'Yabie tubo',
                'type'          => 'Component',
                'asset_type_id' => $ybtStation?->type_id,
                'asset_id'      => $ybtStation?->id,
                'quantity'      => 2,
                'unit_cost'     => 1200.00,
                'condition'     => 'good',
            ],

            // 1½-inch hose – 1 (Condition: Poor via damage workflow)
            [
                'name'          => '1½-inch fire hose',
                'type'          => 'Component',
                'asset_type_id' => $dmgHose15?->type_id,
                'asset_id'      => $dmgHose15?->id,
                'quantity'      => 1,
                'unit_cost'     => 6500.00,
                'condition'     => 'poor',
            ],

            // 2½-inch hose – 1 (Condition: Poor via damage workflow)
            [
                'name'          => '2½-inch fire hose',
                'type'          => 'Component',
                'asset_type_id' => $dmgHose25?->type_id,
                'asset_id'      => $dmgHose25?->id,
                'quantity'      => 1,
                'unit_cost'     => 9500.00,
                'condition'     => 'poor',
            ],
        ];

        foreach ($components as $comp) {
            if (!empty($comp['asset_id'])) {
                Attachment::updateOrCreate(
                    [
                        'name'     => $comp['name'],
                        'asset_id' => $comp['asset_id'],
                    ],
                    $comp
                );
            }
        }

        // =========================================================================
        // 2. RELATED REPLACEMENT PARTS & ACCESSORIES
        // =========================================================================
        $parts = [
            [
                'asset_id'       => $tanker?->id,
                'asset_type'     => 'Fire Truck',
                'accessory_name' => '1½-inch Fog / Straight Stream Pistol Grip Nozzle',
                'quantity'       => 2,
                'unit_cost'      => 4200.00,
            ],
            [
                'asset_id'       => $penetrator?->id,
                'asset_type'     => 'Fire Truck',
                'accessory_name' => '2½-inch Brass Fire Hose Coupling Gasket Set',
                'quantity'       => 6,
                'unit_cost'      => 350.00,
            ],
            [
                'asset_id'       => $chainsaw?->id,
                'asset_type'     => 'Chainsaw',
                'accessory_name' => 'Spare Chainsaw Chain & Spark Plug Kit (Large & Small)',
                'quantity'       => 4,
                'unit_cost'      => 1250.00,
            ],
        ];

        foreach ($parts as $part) {
            if (!empty($part['asset_id'])) {
                AssetAccessory::updateOrCreate(
                    [
                        'asset_id'       => $part['asset_id'],
                        'accessory_name' => $part['accessory_name'],
                    ],
                    $part
                );
            }
        }
    }
}
