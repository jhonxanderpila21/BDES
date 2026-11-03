<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\OperationalRequirement;
use App\Models\ReportedAsset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ArchivedRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds realistic archived (soft-deleted) records across all supported modules
     * along with corresponding metadata and historical audit trail entries.
     */
    public function run(): void
    {
        // 1. Resolve Actor Users for Archiving Metadata
        $admin = User::where('role', 'administrator')->first();
        if (!$admin) {
            $this->call(UserSeeder::class);
            $admin = User::where('role', 'administrator')->first() ?? User::first();
        }

        $inventoryOfficer = User::where('role', 'inventory_officer')->first() ?? $admin;
        $maintenanceOfficer = User::where('role', 'maintenance_officer')->first() ?? $admin;

        // 2. Ensure baseline Category, Type, and Location exist
        $category = AssetCategory::first() ?? AssetCategory::create([
            'name'        => 'Emergency Equipment',
            'description' => 'Disaster response gear and machinery',
        ]);

        $type = AssetType::first() ?? AssetType::create([
            'name'        => 'Power Machinery',
            'category_id' => $category->id,
            'description' => 'Heavy motor and electrical equipment',
        ]);

        $location = Location::first() ?? Location::create([
            'name'             => 'Main Emergency Depot',
            'type'             => 'depot',
            'address'          => 'Zone 1 Barangay Hall Grounds',
            'storage_capacity' => 100,
        ]);

        // Base active reference asset for child associations
        $baseAsset = Asset::first();
        if (!$baseAsset) {
            $baseAsset = Asset::create([
                'code'             => 'AST-BASE-001',
                'name'             => 'Standard Staging Generator',
                'category_id'      => $category->id,
                'type_id'          => $type->id,
                'location_id'      => $location->id,
                'condition'        => 'good',
                'status'           => 'available',
                'acquisition_cost' => 25000.00,
            ]);
        }

        // ==========================================
        // 3. ARCHIVED ASSETS
        // ==========================================
        $archivedAssets = [
            [
                'code'             => 'AST-ARC-001',
                'name'             => 'Yamaha 2.5kVA Standby Generator (Overheated)',
                'category_id'      => $category->id,
                'type_id'          => $type->id,
                'location_id'      => $location->id,
                'condition'        => 'scrap',
                'status'           => 'decommissioned',
                'acquisition_cost' => 38500.00,
                'serial_number'    => 'YAM-98124-GEN',
                'manufacturer'     => 'Yamaha Motor Corp.',
                'deleted_by'       => $admin->id,
                'deleted_at'       => Carbon::now()->subDays(14),
                'deletion_reason'  => 'Engine block cracked due to overheat during 2024 operations; cost of repair exceeds replacement value. Salvaged for usable electrical switches and officially retired.',
            ],
            [
                'code'             => 'AST-ARC-002',
                'name'             => 'Icom IC-V80 VHF Handheld Radio (Water Damaged)',
                'category_id'      => $category->id,
                'type_id'          => $type->id,
                'location_id'      => $location->id,
                'condition'        => 'poor',
                'status'           => 'decommissioned',
                'acquisition_cost' => 6200.00,
                'serial_number'    => 'ICOM-55421-VHF',
                'manufacturer'     => 'Icom Inc.',
                'deleted_by'       => $inventoryOfficer->id,
                'deleted_at'       => Carbon::now()->subDays(6),
                'deletion_reason'  => 'Internal transceiver circuit board completely oxidized and shorted following prolonged saltwater submersion during sea rescue. Beyond economic repair.',
            ],
            [
                'code'             => 'AST-ARC-003',
                'name'             => 'Heavy Duty Canvas Frame Tent 4m x 6m (Relief Shelter)',
                'category_id'      => $category->id,
                'type_id'          => $type->id,
                'location_id'      => $location->id,
                'condition'        => 'scrap',
                'status'           => 'decommissioned',
                'acquisition_cost' => 14000.00,
                'serial_number'    => 'TNT-004-REL',
                'manufacturer'     => 'ShelterCraft Philippines',
                'deleted_by'       => $admin->id,
                'deleted_at'       => Carbon::now()->subDays(2),
                'deletion_reason'  => 'Severe mildew rot and widespread structural canvas tears after extended field deployment during seasonal monsoon evacuation. Condemned by safety officer.',
            ],
        ];

        foreach ($archivedAssets as $item) {
            $asset = Asset::withTrashed()->firstOrNew(['code' => $item['code']]);
            $asset->fill([
                'name'             => $item['name'],
                'category_id'      => $item['category_id'],
                'type_id'          => $item['type_id'],
                'location_id'      => $item['location_id'],
                'condition'        => $item['condition'],
                'status'           => $item['status'],
                'acquisition_cost' => $item['acquisition_cost'],
                'serial_number'    => $item['serial_number'],
                'manufacturer'     => $item['manufacturer'],
            ]);
            $asset->saveQuietly();
            $this->applyArchiveMetadata($asset, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['code']);
        }

        // ==========================================
        // 4. ARCHIVED COMPONENTS & PARTS (ATTACHMENTS)
        // ==========================================
        $archivedAttachments = [
            [
                'name'            => '2-inch Canvas Discharge Hose 50ft (Punctured)',
                'type'            => 'hose',
                'asset_id'        => $baseAsset->id,
                'asset_type_id'   => $type->id,
                'quantity'        => 1,
                'condition'       => 'poor',
                'deleted_by'      => $inventoryOfficer->id,
                'deleted_at'      => Carbon::now()->subDays(10),
                'deletion_reason' => 'Multiple seam punctures identified under pressure hydrostatic testing. Unfit for pressurized flood pumping.',
            ],
            [
                'name'            => 'Stihl 20-inch Guide Bar (Pinched Groove)',
                'type'            => 'blade',
                'asset_id'        => $baseAsset->id,
                'asset_type_id'   => $type->id,
                'quantity'        => 1,
                'condition'       => 'poor',
                'deleted_by'      => $maintenanceOfficer->id,
                'deleted_at'      => Carbon::now()->subDays(3),
                'deletion_reason' => 'Guide bar warped and chain rail crushed during debris road clearing operations. Discarded and replaced with hardened alloy bar.',
            ],
        ];

        foreach ($archivedAttachments as $item) {
            $attachment = Attachment::withTrashed()->firstOrNew([
                'name'     => $item['name'],
                'asset_id' => $item['asset_id'],
            ]);
            $attachment->fill([
                'type'          => $item['type'],
                'asset_type_id' => $item['asset_type_id'],
                'quantity'      => $item['quantity'],
                'condition'     => $item['condition'],
            ]);
            $attachment->saveQuietly();
            $this->applyArchiveMetadata($attachment, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], "#{$attachment->id}");
        }

        // ==========================================
        // 5. ARCHIVED OPERATIONAL REQUIREMENTS
        // ==========================================
        $archivedRequirements = [
            [
                'asset_type_id'    => $type->id,
                'requirement_type' => '2-Stroke Mineral Oil (Legacy Spec)',
                'unit'             => 'Liters',
                'current_level'    => 0.00,
                'maximum_capacity' => 10.00,
                'minimum_level'    => 2.00,
                'description'      => 'Mineral-based 2-stroke lubricant for older chainsaw models.',
                'deleted_by'       => $admin->id,
                'deleted_at'       => Carbon::now()->subDays(18),
                'deletion_reason'  => 'Standardized all field 2-stroke tools to synthetic JASO-FD low-smoke oil. Mineral specification retired to prevent spark plug fouling.',
            ],
        ];

        foreach ($archivedRequirements as $item) {
            $req = OperationalRequirement::withTrashed()->firstOrNew([
                'requirement_type' => $item['requirement_type'],
                'asset_type_id'    => $item['asset_type_id'],
            ]);
            $req->fill([
                'unit'             => $item['unit'],
                'current_level'    => $item['current_level'],
                'maximum_capacity' => $item['maximum_capacity'],
                'minimum_level'    => $item['minimum_level'],
                'description'      => $item['description'],
            ]);
            $req->saveQuietly();
            $this->applyArchiveMetadata($req, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['requirement_type']);
        }

        // ==========================================
        // 6. ARCHIVED MAINTENANCE & WORK ORDERS
        // ==========================================
        $archivedMaintenances = [
            [
                'asset_id'           => $baseAsset->id,
                'type'               => 'repair',
                'work_order_number'  => 'WO-ARC-001',
                'technician'         => 'Mang Kanor (In-house)',
                'scheduled_date'     => Carbon::now()->subDays(12)->format('Y-m-d'),
                'estimated_duration' => '1 day',
                'notes'              => 'Accidental duplicate work order logged during network reconnection timeout.',
                'status'             => 'cancelled',
                'cost'               => 0.00,
                'deleted_by'         => $maintenanceOfficer->id,
                'deleted_at'         => Carbon::now()->subDays(8),
                'deletion_reason'    => 'Duplicate work order entry created accidentally. Work was already scheduled and performed under WO-2026-004.',
            ],
            [
                'asset_id'           => $baseAsset->id,
                'type'               => 'routine',
                'work_order_number'  => 'WO-ARC-002',
                'technician'         => 'Delta Heavy Equipment Services',
                'scheduled_date'     => Carbon::now()->subDays(20)->format('Y-m-d'),
                'estimated_duration' => '3 days',
                'notes'              => 'Quarterly contractor chassis inspection and tune-up.',
                'status'             => 'cancelled',
                'cost'               => 0.00,
                'deleted_by'         => $maintenanceOfficer->id,
                'deleted_at'         => Carbon::now()->subDays(16),
                'deletion_reason'    => 'Service contract was terminated due to vendor delivery failure. Reassigned to authorized municipal service station.',
            ],
        ];

        foreach ($archivedMaintenances as $item) {
            $maint = Maintenance::withTrashed()->firstOrNew(['work_order_number' => $item['work_order_number']]);
            $maint->fill([
                'asset_id'           => $item['asset_id'],
                'type'               => $item['type'],
                'technician'         => $item['technician'],
                'scheduled_date'     => $item['scheduled_date'],
                'estimated_duration' => $item['estimated_duration'],
                'notes'              => $item['notes'],
                'status'             => $item['status'],
                'cost'               => $item['cost'],
            ]);
            $maint->saveQuietly();
            $this->applyArchiveMetadata($maint, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['work_order_number']);
        }

        // ==========================================
        // 7. ARCHIVED INSPECTIONS
        // ==========================================
        $archivedInspections = [
            [
                'asset_id'        => $baseAsset->id,
                'inspector'       => 'Barangay Volunteer Tanod Sison',
                'inspection_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'result'          => 'conditional_pass',
                'findings'        => 'Preliminary visual observations note (Draft only - not certified).',
                'status'          => 'completed',
                'deleted_by'      => $inventoryOfficer->id,
                'deleted_at'      => Carbon::now()->subDays(12),
                'deletion_reason' => 'Informal volunteer draft inspection record superseded by official certified BFP inspection report #2026-081.',
            ],
        ];

        foreach ($archivedInspections as $item) {
            $insp = Inspection::withTrashed()->firstOrNew([
                'findings' => $item['findings'],
                'asset_id' => $item['asset_id'],
            ]);
            $insp->fill([
                'inspector'       => $item['inspector'],
                'inspection_date' => $item['inspection_date'],
                'result'          => $item['result'],
                'status'          => $item['status'],
            ]);
            $insp->saveQuietly();
            $this->applyArchiveMetadata($insp, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], "#{$insp->id}");
        }

        // ==========================================
        // 8. ARCHIVED MAINTENANCE SCHEDULES
        // ==========================================
        $archivedSchedules = [
            [
                'asset_id'              => $baseAsset->id,
                'asset_type'            => 'Generator',
                'maintenance_task'      => 'Bi-weekly Battery Electrolyte Level Top-Up',
                'interval_type'         => 'Monthly',
                'interval_days'         => 30,
                'last_maintenance_date' => Carbon::now()->subDays(40)->format('Y-m-d'),
                'next_due_date'         => Carbon::now()->subDays(10)->format('Y-m-d'),
                'deleted_by'            => $admin->id,
                'deleted_at'            => Carbon::now()->subDays(22),
                'deletion_reason'       => 'Flooded lead-acid batteries replaced with sealed maintenance-free AGM battery units. Manual distilled water top-up schedule is obsolete.',
            ],
        ];

        foreach ($archivedSchedules as $item) {
            $sched = MaintenanceSchedule::withTrashed()->firstOrNew([
                'maintenance_task' => $item['maintenance_task'],
                'asset_id'         => $item['asset_id'],
            ]);
            $sched->fill([
                'asset_type'            => $item['asset_type'],
                'interval_type'         => $item['interval_type'],
                'interval_days'         => $item['interval_days'],
                'last_maintenance_date' => $item['last_maintenance_date'],
                'next_due_date'         => $item['next_due_date'],
            ]);
            $sched->saveQuietly();
            $this->applyArchiveMetadata($sched, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['maintenance_task']);
        }

        // ==========================================
        // 9. ARCHIVED DEPLOYMENTS
        // ==========================================
        $archivedDeployments = [
            [
                'asset_id'             => $baseAsset->id,
                'destination'          => 'Sitio Riverside Outpost (Simulated Drill)',
                'assigned_team'        => 'Bravo Water Rescue Unit',
                'responsible_person'   => 'Team Leader Alonzo',
                'borrower_type'        => 'personnel',
                'deployment_date'      => Carbon::now()->subDays(11)->format('Y-m-d'),
                'expected_return_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'purpose'              => 'Barangay-wide simulated flood evacuation staging exercise.',
                'status'               => 'cancelled',
                'deleted_by'           => $admin->id,
                'deleted_at'           => Carbon::now()->subDays(9),
                'deletion_reason'      => 'Drill canceled by Municipal Disaster Office due to heavy thunderstorm alert; staging deployment never dispatched.',
            ],
        ];

        foreach ($archivedDeployments as $item) {
            $dep = Deployment::withTrashed()->firstOrNew([
                'destination' => $item['destination'],
                'asset_id'    => $item['asset_id'],
            ]);
            $dep->fill([
                'assigned_team'        => $item['assigned_team'],
                'responsible_person'   => $item['responsible_person'],
                'borrower_type'        => $item['borrower_type'],
                'deployment_date'      => $item['deployment_date'],
                'expected_return_date' => $item['expected_return_date'],
                'purpose'              => $item['purpose'],
                'status'               => $item['status'],
            ]);
            $dep->saveQuietly();
            $this->applyArchiveMetadata($dep, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], "#{$dep->id}");
        }

        // ==========================================
        // 10. ARCHIVED REPORTED ASSETS
        // ==========================================
        $archivedReports = [
            [
                'asset_id'        => $baseAsset->id,
                'asset_category'  => $category->name,
                'asset_name'      => $baseAsset->name,
                'reported_by'     => $inventoryOfficer->id,
                'reported_type'   => 'Missing',
                'status'          => 'Done',
                'location'        => 'Main Storage Tool Rack C',
                'description'     => 'Reported missing 17mm socket wrench and ratchet from field tool bag.',
                'action'          => 'Found in secondary vehicle tool compartment; report closed.',
                'reported_date'   => Carbon::now()->subDays(7)->format('Y-m-d'),
                'deleted_by'      => $inventoryOfficer->id,
                'deleted_at'      => Carbon::now()->subDays(4),
                'deletion_reason' => 'Item was located in Patrol Multicab secondary storage drawer. False loss incident report officially withdrawn.',
            ],
        ];

        foreach ($archivedReports as $item) {
            $rep = ReportedAsset::withTrashed()->firstOrNew([
                'description' => $item['description'],
                'asset_id'    => $item['asset_id'],
            ]);
            $rep->fill([
                'asset_category' => $item['asset_category'],
                'asset_name'     => $item['asset_name'],
                'reported_by'    => $item['reported_by'],
                'reported_type'  => $item['reported_type'],
                'status'         => $item['status'],
                'location'       => $item['location'],
                'action'         => $item['action'],
                'reported_date'  => $item['reported_date'],
            ]);
            $rep->saveQuietly();
            $this->applyArchiveMetadata($rep, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], "#{$rep->id}");
        }

        // ==========================================
        // 11. ARCHIVED LOCATIONS
        // ==========================================
        $archivedLocations = [
            [
                'name'             => 'Purok 7 Covered Court Staging Post (Temporary)',
                'type'             => 'field',
                'address'          => 'Purok 7 Riverside Covered Court',
                'storage_capacity' => 25,
                'deleted_by'       => $admin->id,
                'deleted_at'       => Carbon::now()->subDays(28),
                'deletion_reason'  => 'Temporary emergency relief distribution outpost deactivated and closed following the completion of recovery operations. Equipment consolidated into Main Depot.',
            ],
        ];

        foreach ($archivedLocations as $item) {
            $loc = Location::withTrashed()->firstOrNew(['name' => $item['name']]);
            $loc->fill([
                'type'             => $item['type'],
                'address'          => $item['address'],
                'storage_capacity' => $item['storage_capacity'],
            ]);
            $loc->saveQuietly();
            $this->applyArchiveMetadata($loc, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['name']);
        }

        // ==========================================
        // 12. ARCHIVED ASSET TYPES
        // ==========================================
        $archivedTypes = [
            [
                'name'            => 'Legacy Analog VHF Two-Way Radio (Obsolete)',
                'category_id'     => $category->id,
                'serialized'      => true,
                'borrowable'      => true,
                'deleted_by'      => $admin->id,
                'deleted_at'      => Carbon::now()->subDays(35),
                'deletion_reason' => 'Phased out analog radio category in compliance with national migration mandate to digital DMR Tier II encrypted channels.',
            ],
        ];

        foreach ($archivedTypes as $item) {
            $at = AssetType::withTrashed()->firstOrNew(['name' => $item['name']]);
            $at->fill([
                'category_id' => $item['category_id'],
                'serialized'  => $item['serialized'],
                'borrowable'  => $item['borrowable'],
            ]);
            $at->saveQuietly();
            $this->applyArchiveMetadata($at, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['name']);
        }

        // ==========================================
        // 13. ARCHIVED USERS
        // ==========================================
        $archivedUsers = [
            [
                'name'              => 'Intern Trainee Carlo Mendoza',
                'first_name'        => 'Carlo',
                'last_name'         => 'Mendoza',
                'username'          => 'cmendoza_trainee',
                'email'             => 'cmendoza_trainee@bdes.gov',
                'role'              => 'viewer',
                'password'          => Hash::make('password'),
                'is_active'         => false,
                'email_verified_at' => Carbon::now()->subDays(90),
                'deleted_by'        => $admin->id,
                'deleted_at'        => Carbon::now()->subDays(45),
                'deletion_reason'   => '3-month emergency volunteer internship contract concluded. User account deactivated and archived per security protocol.',
            ],
        ];

        foreach ($archivedUsers as $item) {
            $user = User::withTrashed()->firstOrNew(['username' => $item['username']]);
            $user->fill([
                'name'              => $item['name'],
                'first_name'        => $item['first_name'],
                'last_name'         => $item['last_name'],
                'email'             => $item['email'],
                'role'              => $item['role'],
                'password'          => $item['password'],
                'is_active'         => $item['is_active'],
                'email_verified_at' => $item['email_verified_at'],
            ]);
            $user->saveQuietly();
            $this->applyArchiveMetadata($user, $item['deleted_by'], $item['deletion_reason'], $item['deleted_at'], $item['username']);
        }
    }

    /**
     * Apply soft-deletion attributes, timestamps, and create corresponding historical audit trail entry.
     */
    protected function applyArchiveMetadata($model, int $deletedBy, string $reason, Carbon $deletedAt, string $identifier): void
    {
        $model->deleted_at = $deletedAt;
        $model->deleted_by = $deletedBy;
        $model->deletion_reason = $reason;
        $model->saveQuietly();

        $entityName = class_basename($model);

        // Ensure audit log exists for this archive event
        AuditLog::firstOrCreate(
            [
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'event'          => 'archived',
            ],
            [
                'user_id'     => $deletedBy,
                'description' => "{$entityName} [{$identifier}] archived. Reason: {$reason}",
                'old_values'  => $model->toArray(),
                'new_values'  => [
                    'deleted_at'      => $deletedAt->toDateTimeString(),
                    'deleted_by'      => $deletedBy,
                    'deletion_reason' => $reason,
                ],
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'BDES Seeder / System',
                'created_at'  => $deletedAt,
            ]
        );
    }
}
