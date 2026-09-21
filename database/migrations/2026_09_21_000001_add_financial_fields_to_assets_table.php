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
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'useful_life_years')) {
                $table->integer('useful_life_years')->default(5)->after('acquisition_cost');
            }
            if (!Schema::hasColumn('assets', 'salvage_value')) {
                $table->decimal('salvage_value', 12, 2)->nullable()->after('useful_life_years');
            }
            if (!Schema::hasColumn('assets', 'salvage_notes')) {
                $table->text('salvage_notes')->nullable()->after('salvage_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'salvage_notes')) {
                $table->dropColumn('salvage_notes');
            }
            if (Schema::hasColumn('assets', 'salvage_value')) {
                $table->dropColumn('salvage_value');
            }
            if (Schema::hasColumn('assets', 'useful_life_years')) {
                $table->dropColumn('useful_life_years');
            }
        });
    }
};

