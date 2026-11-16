<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssetHistorySeeder extends Seeder
{
    /**
     * Run the audit log seeder for Asset History & Audit Trail.
     */
    public function run(): void
    {
        $admin  = User::where('role', 'administrator')->first() ?? User::first();
        $member = User::where('role', 'inventory_officer')->first() ?? User::where('role', 'bdrrmc_officer')->first() ?? $admin;

        $assets = Asset::all()->keyBy('code');

        if ($assets->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        $logs = [
            // Fire Truck – Tanker events
            [
                'code'        => 'TRK-001',
                'user_id'     => $admin?->id,
                'event'       => 'created',
                'description' => 'Registered Fire Truck – Tanker with 7x 1½-inch hoses, 3x 2½-inch hoses, 1x 2½-inch hose peed, spanner, yabie tubo, vice grip, cutter pliers, and electrical pliers.',
                'old_values'  => null,
                'new_values'  => ['status' => 'available', 'condition' => 'good', 'location_id' => 2],
                'created_at'  => $now->copy()->subMonths(6),
            ],
            [
                'code'        => 'TRK-001',
                'user_id'     => $member?->id,
                'event'       => 'updated',
                'description' => 'Completed monthly pump test and verified all 7x 1½-inch and 3x 2½-inch fire hoses onboard.',
                'old_values'  => ['notes' => 'Initial apparatus commissioning'],
                'new_values'  => ['notes' => 'Pump pressure tested at 150 PSI; all hoses and truck hand tools accounted for.'],
                'created_at'  => $now->copy()->subDays(12),
            ],

            // Fire Truck – Penetrator events
            [
                'code'        => 'TRK-002',
                'user_id'     => $admin?->id,
                'event'       => 'created',
                'description' => 'Registered Fire Truck – Penetrator with 6x 1½-inch hoses, 2x 2½-inch hoses, 1x 2½-inch hose peed, and complete truck tool set.',
                'old_values'  => null,
                'new_values'  => ['status' => 'available', 'condition' => 'good', 'location_id' => 2],
                'created_at'  => $now->copy()->subMonths(5),
            ],
            [
                'code'        => 'TRK-002',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Dispatched Penetrator Fire Truck for grass fire containment at Purok C.',
                'old_values'  => ['status' => 'available'],
                'new_values'  => ['status' => 'deployed'],
                'created_at'  => $now->copy()->subDays(10),
            ],
            [
                'code'        => 'TRK-002',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Returned Penetrator to Purok A Fire Station; all 6x 1½-inch and 2x 2½-inch hoses washed and dried.',
                'old_values'  => ['status' => 'deployed'],
                'new_values'  => ['status' => 'available'],
                'created_at'  => $now->copy()->subDays(9),
            ],

            // Chainsaws (2 large, 2 small) events
            [
                'code'        => 'SAW-001',
                'user_id'     => $member?->id,
                'event'       => 'updated',
                'description' => 'Inspected all 4 chainsaws (2 large, 2 small) and topped up 2-stroke fuel premix and bar oil.',
                'old_values'  => ['condition' => 'good'],
                'new_values'  => ['condition' => 'good', 'notes' => '2 large and 2 small chainsaws ready for storm clearing.'],
                'created_at'  => $now->copy()->subDays(7),
            ],

            // 1½-inch Fire Hose – 1 (FH-005) — Complete System Damage Workflow
            [
                'code'        => 'FH-005',
                'user_id'     => $admin?->id,
                'event'       => 'created',
                'description' => 'Initial asset registration into Purok A Fire Station inventory.',
                'old_values'  => null,
                'new_values'  => ['status' => 'available', 'condition' => 'good', 'location_id' => 2],
                'created_at'  => $now->copy()->subMonths(4),
            ],
            [
                'code'        => 'FH-005',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Deployed to [Purok B Residential Fire Incident] with Alpha Fire Suppression Crew.',
                'old_values'  => ['status' => 'available'],
                'new_values'  => ['status' => 'deployed'],
                'created_at'  => $now->copy()->subDays(6),
            ],
            [
                'code'        => 'FH-005',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Asset returned from deployment to [Purok B Residential Fire Incident]. Post-operation condition: [poor]. Returned to: Purok A Fire Station',
                'old_values'  => ['status' => 'deployed', 'condition' => 'good'],
                'new_values'  => ['status' => 'out_of_service', 'condition' => 'poor'],
                'created_at'  => $now->copy()->subDays(5),
            ],

            // 2½-inch Fire Hose – 1 (FH-006) — Complete System Damage Workflow
            [
                'code'        => 'FH-006',
                'user_id'     => $admin?->id,
                'event'       => 'created',
                'description' => 'Initial asset registration into Purok A Fire Station inventory.',
                'old_values'  => null,
                'new_values'  => ['status' => 'available', 'condition' => 'good', 'location_id' => 2],
                'created_at'  => $now->copy()->subMonths(4),
            ],
            [
                'code'        => 'FH-006',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Deployed to [Zone 2 Warehouse Fire Hydrant Relay] with Rapid Intervention Fire Unit.',
                'old_values'  => ['status' => 'available'],
                'new_values'  => ['status' => 'deployed'],
                'created_at'  => $now->copy()->subDays(4),
            ],
            [
                'code'        => 'FH-006',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Asset returned from deployment to [Zone 2 Warehouse Fire Hydrant Relay]. Post-operation condition: [poor]. Returned to: Purok A Fire Station',
                'old_values'  => ['status' => 'deployed', 'condition' => 'good'],
                'new_values'  => ['status' => 'out_of_service', 'condition' => 'poor'],
                'created_at'  => $now->copy()->subDays(3),
            ],
        ];

        foreach ($logs as $data) {
            $asset = $assets->get($data['code']);
            if (!$asset) {
                continue;
            }

            AuditLog::create([
                'user_id'        => $data['user_id'],
                'auditable_type' => Asset::class,
                'auditable_id'   => $asset->id,
                'event'          => $data['event'],
                'description'    => $data['description'],
                'old_values'     => $data['old_values'],
                'new_values'     => $data['new_values'],
                'ip_address'     => '127.0.0.1',
                'user_agent'     => 'BDES-Workstation/Chrome',
                'created_at'     => $data['created_at'],
            ]);
        }
    }
}
