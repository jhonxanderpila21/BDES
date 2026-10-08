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
        $admin = User::where('role', 'administrator')->first() ?? User::first();
        $member = User::where('role', 'bdrrmc_officer')->first() ?? $admin;

        $assets = Asset::all()->keyBy('code');

        if ($assets->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        $logs = [
            // Generator events
            [
                'code'        => 'GEN-001',
                'user_id'     => $admin?->id,
                'event'       => 'created',
                'description' => 'Initial asset registration and commissioning into disaster inventory dossier.',
                'old_values'  => null,
                'new_values'  => ['status' => 'available', 'condition' => 'excellent', 'location_id' => 1],
                'created_at'  => $now->copy()->subMonths(6),
            ],
            [
                'code'        => 'GEN-001',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Dispatched generator to Barangay Evacuation Center during Typhoon advisory standby.',
                'old_values'  => ['status' => 'available'],
                'new_values'  => ['status' => 'deployed'],
                'created_at'  => $now->copy()->subMonths(3),
            ],
            [
                'code'        => 'GEN-001',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Returned from evacuation center post-standby in clean, operational condition.',
                'old_values'  => ['status' => 'deployed'],
                'new_values'  => ['status' => 'available'],
                'created_at'  => $now->copy()->subMonths(2)->subDays(10),
            ],
            [
                'code'        => 'GEN-001',
                'user_id'     => $member?->id,
                'event'       => 'updated',
                'description' => 'Preventive maintenance oil and air filter replacement performed by BDRRMC mechanics.',
                'old_values'  => ['notes' => 'Portable generator for evacuation center use'],
                'new_values'  => ['notes' => 'Synthetic engine oil replaced, spark plug cleaned.'],
                'created_at'  => $now->copy()->subDays(12),
            ],

            // Rescue Boat events
            [
                'code'        => 'BOT-001',
                'user_id'     => $admin?->id,
                'event'       => 'created',
                'description' => 'Registered marine rescue asset: 19ft fiberglass hull with 15HP outboard engine.',
                'old_values'  => null,
                'new_values'  => ['status' => 'available', 'condition' => 'good', 'acquisition_cost' => 285000.00],
                'created_at'  => $now->copy()->subMonths(10),
            ],
            [
                'code'        => 'BOT-001',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Mobilized for riverbank flood rescue drills at Sector 4.',
                'old_values'  => ['status' => 'available'],
                'new_values'  => ['status' => 'deployed'],
                'created_at'  => $now->copy()->subMonths(1),
            ],
            [
                'code'        => 'BOT-001',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Returned to Marine Depot Station; hull washed down with fresh water.',
                'old_values'  => ['status' => 'deployed'],
                'new_values'  => ['status' => 'available'],
                'created_at'  => $now->copy()->subDays(20),
            ],

            // Chainsaw events
            [
                'code'        => 'SAW-001',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Flagged out of service following hard clearing operations: Carburetor clogged.',
                'old_values'  => ['status' => 'available', 'condition' => 'fair'],
                'new_values'  => ['status' => 'out_of_service', 'condition' => 'poor'],
                'created_at'  => $now->copy()->subDays(5),
            ],



            // Zoll AED events
            [
                'code'        => 'AED-001',
                'user_id'     => $admin?->id,
                'event'       => 'updated',
                'description' => 'Semi-annual calibration check passed: Self-test diagnostic status verified operational.',
                'old_values'  => ['notes' => 'Batteries replaced 2024'],
                'new_values'  => ['notes' => 'Diagnostic test passed; defibrillation pads verified sealed.'],
                'created_at'  => $now->copy()->subHours(8),
            ],

            // Patrol Vehicle events
            [
                'code'        => 'VEH-001',
                'user_id'     => $member?->id,
                'event'       => 'status_changed',
                'description' => 'Deployed Multicab on routine barangay disaster mitigation and flood monitoring rounds.',
                'old_values'  => ['status' => 'available'],
                'new_values'  => ['status' => 'deployed'],
                'created_at'  => $now->copy()->subHours(2),
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

