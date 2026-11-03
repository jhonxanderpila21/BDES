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
        $tablesWithSoftDeletes = [
            'assets',
            'attachments',
            'operational_requirements',
            'maintenances',
            'inspections',
            'maintenance_schedules',
            'deployments',
            'reported_assets',
            'locations',
            'asset_types',
        ];

        foreach ($tablesWithSoftDeletes as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'deleted_at')) {
                        $table->softDeletes();
                    }
                });
            }
        }

        $allArchiveTables = [
            'assets',
            'attachments',
            'operational_requirements',
            'maintenances',
            'inspections',
            'maintenance_schedules',
            'deployments',
            'reported_assets',
            'locations',
            'asset_types',
            'asset_categories',
            'users',
        ];

        foreach ($allArchiveTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'deleted_by')) {
                        $table->unsignedBigInteger('deleted_by')->nullable()->index();
                    }
                    if (!Schema::hasColumn($tableName, 'deletion_reason')) {
                        $table->text('deletion_reason')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allArchiveTables = [
            'assets',
            'attachments',
            'operational_requirements',
            'maintenances',
            'inspections',
            'maintenance_schedules',
            'deployments',
            'reported_assets',
            'locations',
            'asset_types',
            'asset_categories',
            'users',
        ];

        foreach ($allArchiveTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $colsToDrop = [];
                    if (Schema::hasColumn($tableName, 'deleted_by')) {
                        $colsToDrop[] = 'deleted_by';
                    }
                    if (Schema::hasColumn($tableName, 'deletion_reason')) {
                        $colsToDrop[] = 'deletion_reason';
                    }
                    if (!empty($colsToDrop)) {
                        $table->dropColumn($colsToDrop);
                    }
                });
            }
        }

        $tablesWithSoftDeletes = [
            'assets',
            'attachments',
            'operational_requirements',
            'maintenances',
            'inspections',
            'maintenance_schedules',
            'deployments',
            'reported_assets',
            'locations',
            'asset_types',
        ];

        foreach ($tablesWithSoftDeletes as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};

