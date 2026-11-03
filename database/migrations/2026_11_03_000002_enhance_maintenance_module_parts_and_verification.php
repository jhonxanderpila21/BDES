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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('scheduled_date');
            $table->foreignId('inspection_id')->nullable()->after('asset_id')->constrained('inspections')->nullOnDelete();
            $table->string('final_condition', 50)->nullable()->after('completed_at');
            $table->foreignId('verified_by')->nullable()->after('final_condition')->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable()->after('verified_by');
        });

        Schema::create('maintenance_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->cascadeOnDelete();
            $table->string('inventory_type', 50)->nullable(); // 'attachment', 'accessory', 'custom'
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->string('part_name');
            $table->integer('quantity')->default(1);
            $table->string('unit', 50)->default('pcs');
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->decimal('total_cost', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_parts');

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['inspection_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'started_at',
                'inspection_id',
                'final_condition',
                'verified_by',
                'verification_notes',
            ]);
        });
    }
};

