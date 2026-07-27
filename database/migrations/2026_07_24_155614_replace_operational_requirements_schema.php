<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operational_requirements', function (Blueprint $table) {
            $table->dropColumn(['category', 'frequency', 'status']);
            $table->string('requirement_type')->after('asset_type_id');
            $table->string('unit')->after('requirement_type');
            $table->decimal('current_level', 10, 2)->default(0)->after('unit');
            $table->decimal('maximum_capacity', 10, 2)->after('current_level');
            $table->decimal('minimum_level', 10, 2)->default(0)->after('maximum_capacity');
        });

        DB::statement('ALTER TABLE operational_requirements MODIFY description TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operational_requirements', function (Blueprint $table) {
            $table->dropColumn(['requirement_type', 'unit', 'current_level', 'maximum_capacity', 'minimum_level']);
            $table->enum('category', ['safety', 'maintenance', 'inspection', 'documentation'])->default('maintenance');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'semi_annually', 'annually'])->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }
};
