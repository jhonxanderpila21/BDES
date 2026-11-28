<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetFinancialsSeeder extends Seeder
{
    /**
     * Seed financial parameters (Acquisition Cost, Useful Life, Salvage Value, Appraisal Notes)
     * across inventory assets for live straight-line depreciation and salvage records.
     */
    public function run(): void
    {
        $financialProfiles = [
            'TRK-001' => [
                'acquisition_cost'  => 1850000.00,
                'useful_life_years' => 10,
                'salvage_value'     => 185000.00,
                'salvage_notes'     => 'Municipal fire apparatus statutory 10% residual value under COA guidelines.',
            ],
            'TRK-002' => [
                'acquisition_cost'  => 1450000.00,
                'useful_life_years' => 10,
                'salvage_value'     => 145000.00,
                'salvage_notes'     => 'Penetrator fire apparatus statutory 10% residual value under COA guidelines.',
            ],
            'FH-001' => [
                'acquisition_cost'  => 45500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '7 rolls of 1½-inch fire hoses (Tanker) — brass coupling salvage recovery.',
            ],
            'FH-002' => [
                'acquisition_cost'  => 39000.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '6 rolls of 1½-inch fire hoses (Penetrator) — brass coupling salvage recovery.',
            ],
            'FH-003' => [
                'purchase_date'     => '2020-05-10',
                'acquisition_cost'  => 28500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '3 rolls of 2½-inch fire hoses (Tanker) — reached end of 5-year lifespan (fully depreciated to 10% salvage value).',
            ],
            'FH-004' => [
                'acquisition_cost'  => 19000.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 rolls of 2½-inch fire hoses (Penetrator) — semi-expendable firefighting hose set.',
            ],
            'HP-001' => [
                'acquisition_cost'  => 17000.00,
                'useful_life_years' => 7,
                'salvage_value'     => null,
                'salvage_notes'     => '2 units of 2½-inch brass hose peed / wye (1 Tanker, 1 Penetrator).',
            ],
            'SPN-001' => [
                'acquisition_cost'  => 1300.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 units of hose spanner wrenches (1 Tanker, 1 Penetrator).',
            ],
            'YBT-001' => [
                'acquisition_cost'  => 2400.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 units of heavy-duty pipe wrenches / Yabie tubo (1 Tanker, 1 Penetrator).',
            ],
            'VGP-001' => [
                'acquisition_cost'  => 1500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 units of locking pliers / Vice grip (1 Tanker, 1 Penetrator).',
            ],
            'CPL-001' => [
                'acquisition_cost'  => 1100.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 units of diagonal cutter pliers (1 Tanker, 1 Penetrator).',
            ],
            'EPL-001' => [
                'acquisition_cost'  => 1300.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 units of insulated electrical pliers (1 Tanker, 1 Penetrator).',
            ],
            'JCK-001' => [
                'acquisition_cost'  => 11400.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '3 heavy-duty hydraulic jacks for vehicle stabilization and lifting.',
            ],
            'SAW-001' => [
                'acquisition_cost'  => 101000.00,
                'useful_life_years' => 5,
                'salvage_value'     => 10100.00,
                'salvage_notes'     => '4 Stihl chainsaws (2 large, 2 small) — engine block and guide bar salvage value.',
            ],
            'MCH-001' => [
                'acquisition_cost'  => 2400.00,
                'useful_life_years' => 3,
                'salvage_value'     => null,
                'salvage_notes'     => '4 clearing machetes (Sundang) — semi-expendable hand tools.',
            ],
            'ROP-001' => [
                'acquisition_cost'  => 10000.00,
                'useful_life_years' => 3,
                'salvage_value'     => null,
                'salvage_notes'     => '4 coils of rescue and utility ropes (Pisi).',
            ],
            'RCK-001' => [
                'acquisition_cost'  => 6300.00,
                'useful_life_years' => 3,
                'salvage_value'     => null,
                'salvage_notes'     => '14 steel clearing rakes (Rick) for post-disaster debris clearing.',
            ],
            'BLC-001' => [
                'acquisition_cost'  => 2800.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '1 heavy-duty 36-inch bull cutter (bolt cutter) for forcible entry.',
            ],
            'CPL-002' => [
                'acquisition_cost'  => 1100.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 station reserve cutter pliers.',
            ],
            'EPL-002' => [
                'acquisition_cost'  => 1300.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 station reserve electrical pliers.',
            ],
            'VGP-002' => [
                'acquisition_cost'  => 1500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 station reserve vice grips.',
            ],
            'YBT-002' => [
                'acquisition_cost'  => 2400.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => '2 station reserve pipe wrenches (Yabie tubo).',
            ],
            'FH-005' => [
                'acquisition_cost'  => 6500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => 'Damaged 1½-inch hose (1 unit) — brass couplings retained for salvage.',
            ],
            'FH-006' => [
                'acquisition_cost'  => 9500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => 'Damaged 2½-inch hose (1 unit) — brass couplings retained for salvage.',
            ],
            'GEN-001' => [
                'purchase_date'     => '2019-05-10',
                'acquisition_cost'  => 45000.00,
                'useful_life_years' => 7,
                'salvage_value'     => null,
                'salvage_notes'     => 'Fire station standby 5kVA generator — reached end of 7-year lifespan (fully depreciated to 10% salvage value).',
            ],
            'FE-001' => [
                'acquisition_cost'  => 3500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => 'Dry chemical fire extinguisher.',
            ],
            'RAD-001' => [
                'acquisition_cost'  => 16500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => 'VHF fire & rescue dispatch radio.',
            ],
        ];

        foreach ($financialProfiles as $code => $financials) {
            Asset::where('code', $code)->update($financials);
        }

        // Fallback for any other assets without costs
        Asset::whereNull('acquisition_cost')->update([
            'acquisition_cost'  => 5000.00,
            'useful_life_years' => 5,
            'salvage_value'     => null,
            'salvage_notes'     => 'Statutory 10% residual value under COA circular.',
        ]);
    }
}
