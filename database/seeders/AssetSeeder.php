<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typeId = function (string $name, int $fallback = 1): int {
            return AssetType::where('name', $name)->value('id') ?? $fallback;
        };

        $fireTruckType   = $typeId('Fire Truck', 11);
        $hoseType        = $typeId('Fire Hose & Fittings', 1);
        $chainsawType    = $typeId('Chainsaw', 7);
        $ropeType        = $typeId('Rescue Rope (100m)', 6);
        $jackType        = $typeId('Hydraulic & Lifting Tool', 6);
        $clearingType    = $typeId('Clearing & Cutting Tool', 6);
        $handToolType    = $typeId('Mechanical & Hand Tool', 6);
        $generatorType   = $typeId('Generator Set', 2);
        $extinguisherType = $typeId('Fire Extinguisher', 1);
        $radioType       = $typeId('Handheld Radio (VHF)', 8);

        $assets = [
            // =========================================================================
            // 1. PRIMARY FIRE APPARATUS (TANKER & PENETRATOR TRUCKS)
            // =========================================================================
            [
                'code'            => 'TRK-001',
                'name'            => 'Fire Truck – Tanker',
                'category_id'     => 5,
                'type_id'         => $fireTruckType,
                'serial_number'   => 'FT-TANKER-001',
                'manufacturer'    => 'Isuzu / Morita Fire Apparatus',
                'purchase_date'   => '2022-03-15',
                'warranty_expiry' => '2027-03-15',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => 'Primary water tanker fire truck equipped with 7x 1½-inch hoses, 3x 2½-inch hoses, 1x 2½-inch hose peed, and onboard tool kit.',
            ],
            [
                'code'            => 'TRK-002',
                'name'            => 'Fire Truck – Penetrator',
                'category_id'     => 5,
                'type_id'         => $fireTruckType,
                'serial_number'   => 'FT-PENETRATOR-002',
                'manufacturer'    => 'Hino / Rosenbauer Rapid Intervention',
                'purchase_date'   => '2023-01-20',
                'warranty_expiry' => '2028-01-20',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => 'Rapid-response penetrator fire truck for narrow barangay roads; equipped with 6x 1½-inch hoses, 2x 2½-inch hoses, 1x 2½-inch hose peed, and onboard tool kit.',
            ],

            // =========================================================================
            // 2. FIRE HOSES & HOSE FITTINGS (TANKER & PENETRATOR)
            // =========================================================================
            [
                'code'            => 'FH-001',
                'name'            => '1½-inch Fire Hoses – 7 (Tanker)',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'FH-15-TNK-SET7',
                'manufacturer'    => 'Key Hose / Angus Fire',
                'purchase_date'   => '2023-05-10',
                'warranty_expiry' => '2026-05-10',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '7 rolls of 1½-inch double-jacketed attack fire hoses assigned to Fire Truck – Tanker.',
            ],
            [
                'code'            => 'FH-002',
                'name'            => '1½-inch Fire Hoses – 6 (Penetrator)',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'FH-15-PEN-SET6',
                'manufacturer'    => 'Key Hose / Angus Fire',
                'purchase_date'   => '2023-05-10',
                'warranty_expiry' => '2026-05-10',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '6 rolls of 1½-inch double-jacketed attack fire hoses assigned to Fire Truck – Penetrator.',
            ],
            [
                'code'            => 'FH-003',
                'name'            => '2½-inch Fire Hoses – 3 (Tanker)',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'FH-25-TNK-SET3',
                'manufacturer'    => 'Key Hose / Angus Fire',
                'purchase_date'   => '2023-05-10',
                'warranty_expiry' => '2026-05-10',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '3 rolls of 2½-inch high-volume supply fire hoses assigned to Fire Truck – Tanker.',
            ],
            [
                'code'            => 'FH-004',
                'name'            => '2½-inch Fire Hoses – 2 (Penetrator)',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'FH-25-PEN-SET2',
                'manufacturer'    => 'Key Hose / Angus Fire',
                'purchase_date'   => '2023-05-10',
                'warranty_expiry' => '2026-05-10',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 rolls of 2½-inch high-volume supply fire hoses assigned to Fire Truck – Penetrator.',
            ],
            [
                'code'            => 'HP-001',
                'name'            => '2½-inch Hose Peed – 1 (each truck)',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'HP-25-TRK-SET2',
                'manufacturer'    => 'Akron Brass',
                'purchase_date'   => '2023-05-12',
                'warranty_expiry' => '2026-05-12',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 units total: 1 assigned to Tanker and 1 assigned to Penetrator for 2½-inch hose line division.',
            ],

            // =========================================================================
            // 3. TRUCK-ASSIGNED HAND TOOLS (1 EACH TRUCK: TANKER & PENETRATOR)
            // =========================================================================
            [
                'code'            => 'SPN-001',
                'name'            => 'Spanner – 1 (each truck)',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-SPN-TRK-02',
                'manufacturer'    => 'Akron Brass',
                'purchase_date'   => '2023-06-01',
                'warranty_expiry' => null,
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 fire hose coupling spanner wrenches: 1 on Tanker, 1 on Penetrator.',
            ],
            [
                'code'            => 'YBT-001',
                'name'            => 'Yabie tubo – 1 (each truck)',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-YBT-TRK-02',
                'manufacturer'    => 'Ridgid Tools',
                'purchase_date'   => '2023-06-01',
                'warranty_expiry' => null,
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 heavy-duty pipe wrenches (Yabie tubo) for hydrant and standpipe fittings: 1 on Tanker, 1 on Penetrator.',
            ],
            [
                'code'            => 'VGP-001',
                'name'            => 'Vice grip – 1 (each truck)',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-VGP-TRK-02',
                'manufacturer'    => 'Irwin Vise-Grip',
                'purchase_date'   => '2023-06-01',
                'warranty_expiry' => null,
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 locking pliers (Vice grip) onboard apparatus compartments: 1 on Tanker, 1 on Penetrator.',
            ],
            [
                'code'            => 'CPL-001',
                'name'            => 'Cutter pliers – 1 (each truck)',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-CPL-TRK-02',
                'manufacturer'    => 'Stanley Tools',
                'purchase_date'   => '2023-06-01',
                'warranty_expiry' => null,
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 heavy-duty diagonal wire cutter pliers: 1 on Tanker, 1 on Penetrator.',
            ],
            [
                'code'            => 'EPL-001',
                'name'            => 'Electrical pliers – 1 (each truck)',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-EPL-TRK-02',
                'manufacturer'    => 'Klein Tools',
                'purchase_date'   => '2023-06-01',
                'warranty_expiry' => null,
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 insulated linesman electrical pliers for scene utility shutoff: 1 on Tanker, 1 on Penetrator.',
            ],

            // =========================================================================
            // 4. RESCUE, CLEARING & STATION TOOLS
            // =========================================================================
            [
                'code'            => 'JCK-001',
                'name'            => 'Jack – 3',
                'category_id'     => 3,
                'type_id'         => $jackType,
                'serial_number'   => 'RES-JCK-SET3',
                'manufacturer'    => 'Torin Big Red',
                'purchase_date'   => '2023-04-15',
                'warranty_expiry' => '2025-04-15',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '3 heavy-duty hydraulic bottle/floor jacks for emergency vehicle stabilization and rescue lifting.',
            ],
            [
                'code'            => 'SAW-001',
                'name'            => 'Chainsaws – 4 (2 large, 2 small)',
                'category_id'     => 3,
                'type_id'         => $chainsawType,
                'serial_number'   => 'SAW-STIHL-SET4',
                'manufacturer'    => 'Stihl',
                'purchase_date'   => '2023-07-10',
                'warranty_expiry' => '2025-07-10',
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '4 chainsaw units total: 2 Large (Stihl MS 382 24" guide bar) and 2 Small (Stihl MS 180 16" guide bar) for disaster clearing.',
            ],
            [
                'code'            => 'MCH-001',
                'name'            => 'Machetes (Sundang) – 4',
                'category_id'     => 3,
                'type_id'         => $clearingType,
                'serial_number'   => 'CLR-MCH-SET4',
                'manufacturer'    => 'Tramontina / Local Forged',
                'purchase_date'   => '2023-08-01',
                'warranty_expiry' => null,
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '4 heavy-duty clearing bolos / machetes (Sundang) with protective sheaths for storm debris clearing.',
            ],
            [
                'code'            => 'ROP-001',
                'name'            => 'Ropes (Pisi) – 4',
                'category_id'     => 3,
                'type_id'         => $ropeType,
                'serial_number'   => 'RES-ROP-SET4',
                'manufacturer'    => 'Heavy Duty Rescues Ltd.',
                'purchase_date'   => '2023-06-10',
                'warranty_expiry' => '2028-06-10',
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '4 coils of high-strength braided utility & rescue ropes (Pisi) for securing, hauling, and water rescue.',
            ],
            [
                'code'            => 'RCK-001',
                'name'            => 'Rick – 14',
                'category_id'     => 3,
                'type_id'         => $clearingType,
                'serial_number'   => 'CLR-RCK-SET14',
                'manufacturer'    => 'Truper Heavy Duty',
                'purchase_date'   => '2023-08-05',
                'warranty_expiry' => null,
                'location_id'     => 4,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '14 steel-tine clearing rakes (Rick) for post-typhoon road clearing, drainage unclogging, and firebreak preparation.',
            ],
            [
                'code'            => 'BLC-001',
                'name'            => 'Bull cutter – 1',
                'category_id'     => 3,
                'type_id'         => $clearingType,
                'serial_number'   => 'RES-BLC-001',
                'manufacturer'    => 'Stanley / H.K. Porter',
                'purchase_date'   => '2023-06-15',
                'warranty_expiry' => null,
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '1 heavy-duty 36-inch hardened alloy steel bolt cutter (Bull cutter) for forcible entry and emergency extraction.',
            ],
            [
                'code'            => 'CPL-002',
                'name'            => 'Cutter pliers – 2',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-CPL-STN-02',
                'manufacturer'    => 'Stanley Tools',
                'purchase_date'   => '2023-06-15',
                'warranty_expiry' => null,
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 cutter pliers stored in the main station rescue tool locker.',
            ],
            [
                'code'            => 'EPL-002',
                'name'            => 'Electrical pliers – 2',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-EPL-STN-02',
                'manufacturer'    => 'Klein Tools',
                'purchase_date'   => '2023-06-15',
                'warranty_expiry' => null,
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 insulated electrical pliers stored in the main station rescue tool locker.',
            ],
            [
                'code'            => 'VGP-002',
                'name'            => 'Vice grips – 2',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-VGP-STN-02',
                'manufacturer'    => 'Irwin Vise-Grip',
                'purchase_date'   => '2023-06-15',
                'warranty_expiry' => null,
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 heavy-duty locking pliers (Vice grips) stored in the main station rescue tool locker.',
            ],
            [
                'code'            => 'YBT-002',
                'name'            => 'Yabie tubo – 2',
                'category_id'     => 3,
                'type_id'         => $handToolType,
                'serial_number'   => 'TL-YBT-STN-02',
                'manufacturer'    => 'Ridgid Tools',
                'purchase_date'   => '2023-06-15',
                'warranty_expiry' => null,
                'location_id'     => 1,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => '2 heavy-duty pipe wrenches (Yabie tubo) stored in the main station rescue tool locker.',
            ],

            // =========================================================================
            // 5. FIRE HOSES FLAGGED THROUGH DAMAGE FLOW (POOR / OUT OF SERVICE)
            // =========================================================================
            [
                'code'            => 'FH-005',
                'name'            => '1½-inch Fire Hose – 1',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'FH-15-008',
                'manufacturer'    => 'Key Hose / Angus Fire',
                'purchase_date'   => '2021-11-10',
                'warranty_expiry' => '2024-11-10',
                'location_id'     => 2,
                'condition'       => 'poor',
                'status'          => 'out_of_service',
                'notes'           => 'Returned from fire suppression deployment with damage noted (punctured rubber liner); flagged Poor & Out of Service pending repair work order.',
            ],
            [
                'code'            => 'FH-006',
                'name'            => '2½-inch Fire Hose – 1',
                'category_id'     => 1,
                'type_id'         => $hoseType,
                'serial_number'   => 'FH-25-004',
                'manufacturer'    => 'Key Hose / Angus Fire',
                'purchase_date'   => '2021-11-10',
                'warranty_expiry' => '2024-11-10',
                'location_id'     => 2,
                'condition'       => 'poor',
                'status'          => 'out_of_service',
                'notes'           => 'Returned from hydrant relay deployment with damage noted (burst seam near coupling); flagged Poor & Out of Service pending repair work order.',
            ],

            // =========================================================================
            // 6. RELATED STATION SUPPORT EQUIPMENT (GENERATOR, EXTINGUISHER, RADIO)
            // =========================================================================
            [
                'code'            => 'GEN-001',
                'name'            => 'Fire Station Standby Generator 5kVA',
                'category_id'     => 1,
                'type_id'         => $generatorType,
                'serial_number'   => 'GEN-HONDA-5K-099',
                'manufacturer'    => 'Honda',
                'purchase_date'   => '2022-05-10',
                'warranty_expiry' => '2025-05-10',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => 'Emergency backup power generator for Purok A Fire Station and floodlight operations.',
            ],
            [
                'code'            => 'FE-001',
                'name'            => 'Dry Chemical Fire Extinguisher (20 lbs)',
                'category_id'     => 1,
                'type_id'         => $extinguisherType,
                'serial_number'   => 'SN-FE-100234',
                'manufacturer'    => 'Safety First Inc.',
                'purchase_date'   => '2023-01-15',
                'warranty_expiry' => '2026-01-15',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => 'ABC Dry Chemical extinguisher mounted on Fire Truck – Tanker.',
            ],
            [
                'code'            => 'RAD-001',
                'name'            => 'VHF Fire & Rescue Dispatch Radio',
                'category_id'     => 4,
                'type_id'         => $radioType,
                'serial_number'   => 'RAD-MOT-VHF-01',
                'manufacturer'    => 'Motorola',
                'purchase_date'   => '2023-02-15',
                'warranty_expiry' => '2026-02-15',
                'location_id'     => 2,
                'condition'       => 'good',
                'status'          => 'available',
                'notes'           => 'Primary two-way radio communication between Tanker, Penetrator, and Barangay Command.',
            ],
        ];

        foreach ($assets as $asset) {
            Asset::updateOrCreate(['code' => $asset['code']], $asset);
        }
    }
}
