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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('asset_type');
            $table->string('maintenance_task');
            $table->enum('interval_type', ['Monthly', 'Quarterly', 'Yearly']);
            $table->integer('interval_days');
            $table->date('last_maintenance_date');
            $table->date('next_due_date');
            $table->enum('status', ['Monitoring Only', 'Maintenance Due', 'Overdue'])->default('Monitoring Only');
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('interval_type');
            $table->index('next_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
