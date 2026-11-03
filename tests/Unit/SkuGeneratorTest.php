<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Location;
use App\Services\SkuGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkuGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_derive_prefix_from_type_name(): void
    {
        $this->assertEquals('FE', SkuGenerator::derivePrefix('Fire Extinguisher ABC'));
        $this->assertEquals('GEN', SkuGenerator::derivePrefix('Diesel Generator'));
        $this->assertEquals('FAK', SkuGenerator::derivePrefix('First Aid Trauma Kit'));
        $this->assertEquals('AED', SkuGenerator::derivePrefix('Automated External Defibrillator'));
        $this->assertEquals('STR', SkuGenerator::derivePrefix('Folding Stretcher'));
    }

    public function test_derive_prefix_falls_back_to_category_or_name(): void
    {
        $this->assertEquals('VEH', SkuGenerator::derivePrefix(null, 'Rescue Vehicles'));
        $this->assertEquals('ROP', SkuGenerator::derivePrefix(null, null, 'Nylon Rescue Rope'));
        $this->assertEquals('AST', SkuGenerator::derivePrefix(null, null, null));
    }

    public function test_generate_batch_creates_sequential_unique_skus(): void
    {
        $category = AssetCategory::create(['name' => 'Generators', 'status' => 'active']);
        $location = Location::create(['name' => 'Storage Room', 'status' => 'active']);

        // Pre-create GEN-001 and GEN-002
        Asset::create([
            'name'             => 'Old Generator 1',
            'code'             => 'GEN-001',
            'category_id'      => $category->id,
            'location_id'      => $location->id,
            'condition'        => 'good',
            'status'           => 'available',
        ]);
        Asset::create([
            'name'             => 'Old Generator 2',
            'code'             => 'GEN-002',
            'category_id'      => $category->id,
            'location_id'      => $location->id,
            'condition'        => 'good',
            'status'           => 'available',
        ]);

        $batch = SkuGenerator::generateBatch(3, $category->id, null, 'Portable Generator');

        $this->assertCount(3, $batch);
        $this->assertEquals(['GEN-003', 'GEN-004', 'GEN-005'], $batch);
    }
}

