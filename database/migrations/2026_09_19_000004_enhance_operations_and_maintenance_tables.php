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
            if (!Schema::hasColumn('maintenances', 'work_order_number')) {
                $table->string('work_order_number', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('maintenances', 'priority')) {
                $table->string('priority', 20)->default('medium')->after('type');
            }
            if (!Schema::hasColumn('maintenances', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('estimated_duration');
            }
            if (!Schema::hasColumn('maintenances', 'parts_used')) {
                $table->text('parts_used')->nullable()->after('cost');
            }
            if (!Schema::hasColumn('maintenances', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('maintenances', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('technician');
            }
        });

        Schema::table('deployments', function (Blueprint $table) {
            if (!Schema::hasColumn('deployments', 'responsible_person')) {
                $table->string('responsible_person')->nullable()->after('assigned_team');
            }
            if (!Schema::hasColumn('deployments', 'condition_before')) {
                $table->string('condition_before', 50)->nullable()->after('purpose');
            }
            if (!Schema::hasColumn('deployments', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
        });

        Schema::table('returns', function (Blueprint $table) {
            if (!Schema::hasColumn('returns', 'received_by')) {
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete()->after('returned_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to');
            }
            $table->dropColumn(['work_order_number', 'priority', 'cost', 'parts_used', 'completed_at']);
        });

        Schema::table('deployments', function (Blueprint $table) {
            if (Schema::hasColumn('deployments', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            $table->dropColumn(['responsible_person', 'condition_before']);
        });

        Schema::table('returns', function (Blueprint $table) {
            if (Schema::hasColumn('returns', 'received_by')) {
                $table->dropConstrainedForeignId('received_by');
            }
        });
    }
};

