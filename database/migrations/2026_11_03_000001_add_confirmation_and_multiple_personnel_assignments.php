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
        // 1. Add confirmation fields to reported_assets
        Schema::table('reported_assets', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
        });

        // 2. Create reported_asset_user pivot table for multiple personnel
        Schema::create('reported_asset_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_asset_id')->constrained('reported_assets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['reported_asset_id', 'user_id']);
        });

        // 3. Add confirmation fields to maintenances
        Schema::table('maintenances', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
        });

        // 4. Create maintenance_user pivot table for multiple personnel
        Schema::create('maintenance_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['maintenance_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_user');

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmed_at', 'confirmed_by']);
        });

        Schema::dropIfExists('reported_asset_user');

        Schema::table('reported_assets', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmed_at', 'confirmed_by']);
        });
    }
};

