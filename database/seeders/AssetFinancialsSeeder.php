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
            'FE-001' => [
                'acquisition_cost'  => 3500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null, // defaults to COA 10% (350.00)
                'salvage_notes'     => 'Standard 10% statutory residual allowance under COA disaster guidelines.',
            ],
            'FE-002' => [
                'acquisition_cost'  => 3500.00,
                'useful_life_years' => 5,
                'salvage_value'     => null,
                'salvage_notes'     => 'Standard 10% statutory residual allowance under COA disaster guidelines.',
            ],
            'GEN-001' => [
                'acquisition_cost'  => 45000.00,
                'useful_life_years' => 7,
                'salvage_value'     => 6500.00,
                'salvage_notes'     => 'Appraised scrap copper winding and motor block residual floor upon decommissioning.',
            ],
            'GEN-002' => [
                'acquisition_cost'  => 38000.00,
                'useful_life_years' => 7,
                'salvage_value'     => 5000.00,
                'salvage_notes'     => 'Estimated generator frame and parts recovery scrap appraisal value.',
            ],

            'AED-001' => [
                'acquisition_cost'  => 95000.00,
                'useful_life_years' => 5,
                'salvage_value'     => 12000.00,
                'salvage_notes'     => 'Manufacturer core trade-in appraisal credit for life-support electronics.',
            ],
            'STR-001' => [
                'acquisition_cost'  => 6500.00,
                'useful_life_years' => 5,
                'salvage_value'     => 650.00,
                'salvage_notes'     => 'Aluminum frame scrap recovery value under LGU disposal procedures.',
            ],
            'ROP-001' => [
                'acquisition_cost'  => 12000.00,
                'useful_life_years' => 3,
                'salvage_value'     => 1200.00,
                'salvage_notes'     => 'Secondary utility use residual allowance.',
            ],
            'SAW-001' => [
                'acquisition_cost'  => 28000.00,
                'useful_life_years' => 5,
                'salvage_value'     => 4000.00,
                'salvage_notes'     => 'Parts salvage valuation (engine block, guide bar, and chassis assembly).',
            ],
            'RAD-001' => [
                'acquisition_cost'  => 16500.00,
                'useful_life_years' => 5,
                'salvage_value'     => 2000.00,
                'salvage_notes'     => 'VHF transmitter chassis appraisal and telecom components residual recovery.',
            ],
            'RAD-002' => [
                'acquisition_cost'  => 16500.00,
                'useful_life_years' => 5,
                'salvage_value'     => 2000.00,
                'salvage_notes'     => 'VHF transmitter chassis appraisal and telecom components residual recovery.',
            ],
            'MEG-001' => [
                'acquisition_cost'  => 4500.00,
                'useful_life_years' => 4,
                'salvage_value'     => 450.00,
                'salvage_notes'     => 'Standard 10% statutory residual allowance.',
            ],
            'BOT-001' => [
                'acquisition_cost'  => 285000.00,
                'useful_life_years' => 10,
                'salvage_value'     => 35000.00,
                'salvage_notes'     => 'Marine survey appraisal for heavy-duty fiberglass hull and motor components.',
            ],
            'VEH-001' => [
                'acquisition_cost'  => 320000.00,
                'useful_life_years' => 8,
                'salvage_value'     => 48000.00,
                'salvage_notes'     => 'Municipal vehicle residual scrap metal, engine, and drive-train appraisal.',
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

