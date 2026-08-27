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
        Schema::create('reported_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('asset_category');
            $table->string('asset_name');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->enum('reported_type', [
                'Damage', 'Missing', 'Malfunction', 'Maintenance', 'Replacement', 'Other',
            ]);
            $table->enum('status', [
                'Pending', 'For Review', 'Ongoing', 'Escalated', 'Done', 'Closed',
            ])->default('Pending');
            $table->string('location');
            $table->text('description')->nullable();
            $table->string('action')->nullable();
            $table->date('reported_date');
            $table->date('resolved_date')->nullable();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('reported_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reported_assets');
    }
};
