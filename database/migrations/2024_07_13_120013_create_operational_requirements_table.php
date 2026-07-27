<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_type_id')->constrained('asset_types')->onDelete('cascade');
            $table->enum('category', ['safety', 'maintenance', 'inspection', 'documentation'])->default('maintenance');
            $table->text('description');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'semi_annually', 'annually'])->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_requirements');
    }
};
