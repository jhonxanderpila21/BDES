<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('asset_type');
            $table->string('requirement');
            $table->enum('unit', ['Liters', 'Units']);
            $table->decimal('maximum_capacity', 10, 2);
            $table->decimal('minimum_level', 10, 2)->default(0);
            $table->decimal('current_level', 10, 2)->default(0);
            $table->enum('status', ['Monitoring Only', 'Requires Maintenance', 'Under Maintenance'])
                  ->default('Monitoring Only');
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_needs');
    }
};
