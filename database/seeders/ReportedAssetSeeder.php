<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ReportedAsset;
use App\Models\User;
use App\Services\ReportedAssetService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReportedAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'administrator')->first();
        $staff = User::where('role', 'viewer')->first() ?? User::where('role', 'bdrrmc_officer')->first();
        $reporter = $staff ?? $admin;

        if (!$reporter) {
            return;
        }

        $allAssets = Asset::with('category')->get();
        if ($allAssets->isEmpty()) {
            return;
        }

        $findAsset = function (string $code) use ($allAssets) {
            return $allAssets->firstWhere('code', $code) ?? $allAssets->random();
        };

        $reports = [
            // 1. Generator Damage — Ongoing
            [
                'asset'         => $findAsset('GEN-001'),
                'reported_type' => 'Damage',
                'target_status' => 'Ongoing',
                'location'      => 'Evacuation Center 1',
                'description'   => "Generator won't start. Fuel line blockage and spark plug fouling detected.",
                'action'        => 'Repair/Refill',
                'reported_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Submitted for admin review.'],
                    ['to' => 'Ongoing', 'notes' => 'Approved. Field technician dispatched.'],
                ],
            ],

            // 2. AED Missing — Escalated
            [
                'asset'         => $findAsset('AED-001'),
                'reported_type' => 'Missing',
                'target_status' => 'Escalated',
                'location'      => 'Barangay Hall',
                'description'   => 'Automated External Defibrillator unit not found in assigned emergency bay.',
                'action'        => 'Report to city DRRMO',
                'reported_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Internal inventory investigation initiated.'],
                    ['to' => 'Escalated', 'notes' => 'Cannot locate after site search. Escalated to City Disaster Risk Office.'],
                ],
            ],

            // 3. Radio Malfunction — Pending
            [
                'asset'         => $findAsset('RAD-001'),
                'reported_type' => 'Malfunction',
                'target_status' => 'Pending',
                'location'      => 'Coastal Watch Tower',
                'description'   => 'Handheld VHF radio emitting continuous static noise on Emergency Channel 16.',
                'action'        => 'Awaiting technical inspection',
                'reported_date' => Carbon::now()->format('Y-m-d'),
                'transitions'   => [],
            ],

            // 4. Chainsaw Damage — For Review
            [
                'asset'         => $findAsset('SAW-001'),
                'reported_type' => 'Damage',
                'target_status' => 'For Review',
                'location'      => 'Main Depot Workshop',
                'description'   => 'Chainsaw guide bar bent and drive sprocket teeth chipped during road clearing operations.',
                'action'        => 'Quote requested for bar & chain replacement',
                'reported_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Inspection report and replacement quotation submitted for review.'],
                ],
            ],

            // 5. Fire Extinguisher Maintenance — Done
            [
                'asset'         => $findAsset('FE-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Done',
                'location'      => 'Barangay Hall',
                'description'   => 'Dry chemical pressure gauge dropped below optimal operating pressure.',
                'action'        => 'Refilled and pressure-tested',
                'reported_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Dispatched to accredited fire safety refilling vendor.'],
                    ['to' => 'Done', 'notes' => 'Refilled, hydrostatic pressure tested, and safety inspection seal attached.'],
                ],
            ],

            // 6. Rescue Boat Hull Damage — Escalated
            [
                'asset'         => $findAsset('BOT-001'),
                'reported_type' => 'Damage',
                'target_status' => 'Escalated',
                'location'      => 'Riverine Rescue Station',
                'description'   => 'Transom mount seam separated during flash flood swiftwater rescue operation.',
                'action'        => 'Escalated for manufacturer warranty repair',
                'reported_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Structural inspection by rescue team mechanics.'],
                    ['to' => 'Escalated', 'notes' => 'Seam separation exceeds local repair capability. Escalated for specialized maritime service.'],
                ],
            ],

            // 7. Megaphone Clasp Replacement — Closed
            [
                'asset'         => $findAsset('MEG-001'),
                'reported_type' => 'Replacement',
                'target_status' => 'Closed',
                'location'      => 'Evacuation Center 2',
                'description'   => 'Battery door locking latch snapped off, preventing secure battery retention.',
                'action'        => 'Replaced battery door latch and tested siren',
                'reported_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Replacement part ordered.'],
                    ['to' => 'Done', 'notes' => 'Part installed and operational audio test passed.'],
                    ['to' => 'Closed', 'notes' => 'Maintenance verified and signed off into active reserve.'],
                ],
            ],

            // 8. First Aid Kit Consumables Depleted — Pending
            [
                'asset'         => $findAsset('FAK-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Pending',
                'location'      => 'Health Sub-Center',
                'description'   => 'Trauma dressings, tourniquets, and sterile gauze packs depleted following emergency response.',
                'action'        => 'Restock request initiated',
                'reported_date' => Carbon::now()->format('Y-m-d'),
                'transitions'   => [],
            ],

            // 9. Rescue Vehicle Alternator Fluctuation — Ongoing
            [
                'asset'         => $findAsset('VEH-001'),
                'reported_type' => 'Malfunction',
                'target_status' => 'Ongoing',
                'location'      => 'Emergency Vehicle Bay',
                'description'   => 'Alternator charging warning light illuminated intermittently during emergency transport.',
                'action'        => 'Under diagnosis at barangay motor pool',
                'reported_date' => Carbon::now()->subDays(4)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Vehicle logged into incident system.'],
                    ['to' => 'Ongoing', 'notes' => 'Auto-electrician diagnosing voltage regulator.'],
                ],
            ],

            // 10. Secondary Fire Extinguisher Annual Check — Closed
            [
                'asset'         => $findAsset('FE-002'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Closed',
                'location'      => 'Storage Room',
                'description'   => 'Routine inspection: annual inspection tag expiring this month.',
                'action'        => 'Certified and re-tagged for 12 months',
                'reported_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Dispatched for certified inspection.'],
                    ['to' => 'Done', 'notes' => 'BFP certification completed and tag updated.'],
                    ['to' => 'Closed', 'notes' => 'Returned to storage room.'],
                ],
            ],
        ];

        foreach ($reports as $data) {
            if (!$data['asset'] || !$reporter) {
                continue;
            }

            // Load the category relationship so we can store its name.
            $data['asset']->load('category');

            $report = ReportedAsset::create([
                'asset_id'       => $data['asset']->id,
                'asset_category' => $data['asset']->category?->name ?? 'Uncategorized',
                'asset_name'     => $data['asset']->name,
                'reported_by'    => $reporter->id,
                'reported_type'  => $data['reported_type'],
                'status'         => ReportedAssetService::STATUS_PENDING,
                'location'       => $data['location'],
                'description'    => $data['description'],
                'action'         => $data['action'],
                'reported_date'  => $data['reported_date'],
            ]);

            // Log initial status
            $report->statusHistories()->create([
                'from_status' => null,
                'to_status'   => ReportedAssetService::STATUS_PENDING,
                'changed_by'  => $reporter->id,
                'notes'       => 'Report created.',
            ]);

            // Walk through transitions
            foreach ($data['transitions'] as $transition) {
                $report->changeStatus(
                    $transition['to'],
                    $admin?->id ?? $reporter->id,
                    $transition['notes']
                );
            }
        }
    }
}
