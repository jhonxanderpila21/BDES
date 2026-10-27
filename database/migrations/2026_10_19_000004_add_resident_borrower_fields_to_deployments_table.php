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
        Schema::table('deployments', function (Blueprint $table) {
            if (!Schema::hasColumn('deployments', 'borrower_type')) {
                $table->string('borrower_type', 20)->default('personnel')->after('assigned_team');
            }
            if (!Schema::hasColumn('deployments', 'borrower_name')) {
                $table->string('borrower_name', 255)->nullable()->after('borrower_type');
            }
            if (!Schema::hasColumn('deployments', 'borrower_contact')) {
                $table->string('borrower_contact', 50)->nullable()->after('borrower_name');
            }
            if (!Schema::hasColumn('deployments', 'borrower_address')) {
                $table->string('borrower_address', 255)->nullable()->after('borrower_contact');
            }
            if (!Schema::hasColumn('deployments', 'borrower_id_type')) {
                $table->string('borrower_id_type', 50)->nullable()->after('borrower_address');
            }
            if (!Schema::hasColumn('deployments', 'borrower_id_number')) {
                $table->string('borrower_id_number', 100)->nullable()->after('borrower_id_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropColumn([
                'borrower_type',
                'borrower_name',
                'borrower_contact',
                'borrower_address',
                'borrower_id_type',
                'borrower_id_number',
            ]);
        });
    }
};

