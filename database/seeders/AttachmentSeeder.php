<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Attachment;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
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

        $attachments = [
            [
                'name' => 'Generator Heavy-Duty Wheel Kit & Handle',
                'type' => 'Mobility Attachment',
                'asset_type_id' => $gen001?->type_id,
                'asset_id' => $gen001?->id,
                'quantity' => 1,
                'unit_cost' => 2400.00,
                'condition' => 'excellent',
            ],
            [
                'name' => 'Chainsaw 24-inch Guide Bar & Chain',
                'type' => 'Cutting Attachment',
                'asset_type_id' => $saw001?->type_id,
                'asset_id' => $saw001?->id,
                'quantity' => 2,
                'unit_cost' => 1850.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Basket Stretcher Lifting Bridle Harness',
                'type' => 'Rigging Attachment',
                'asset_type_id' => $str001?->type_id,
                'asset_id' => $str001?->id,
                'quantity' => 2,
                'unit_cost' => 3200.00,
                'condition' => 'excellent',
            ],
            [
                'name' => 'Rescue Boat Outboard Motor Propeller Guard',
                'type' => 'Safety Attachment',
                'asset_type_id' => $bot001?->type_id,
                'asset_id' => $bot001?->id,
                'quantity' => 1,
                'unit_cost' => 4500.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Vehicle High-Output LED Scene Light Bar',
                'type' => 'Lighting Attachment',
                'asset_type_id' => $veh001?->type_id,
                'asset_id' => $veh001?->id,
                'quantity' => 1,
                'unit_cost' => 5800.00,
                'condition' => 'good',
            ],
        ];

        foreach ($attachments as $att) {
            if (!empty($att['asset_id'])) {
                Attachment::updateOrCreate(
                    [
                        'name' => $att['name'],
                        'asset_id' => $att['asset_id'],
                    ],
                    $att
                );
            }
        }
    }
}

