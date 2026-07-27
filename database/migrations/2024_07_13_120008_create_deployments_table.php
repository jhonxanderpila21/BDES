<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('destination');
            $table->string('assigned_team')->nullable();
            $table->date('deployment_date');
            $table->date('expected_return_date')->nullable();
            $table->text('purpose')->nullable();
            $table->enum('status', ['active', 'pending_return', 'returned'])->default('active');
            $table->timestamps();
            $table->index('asset_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
