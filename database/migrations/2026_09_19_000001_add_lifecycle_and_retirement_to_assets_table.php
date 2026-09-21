<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'retirement_date')) {
                $table->date('retirement_date')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('assets', 'retirement_reason')) {
                $table->text('retirement_reason')->nullable()->after('retirement_date');
            }
            if (!Schema::hasColumn('assets', 'retired_by')) {
                $table->foreignId('retired_by')->nullable()->constrained('users')->nullOnDelete()->after('retirement_reason');
            }
            if (!Schema::hasColumn('assets', 'acquisition_cost')) {
                $table->decimal('acquisition_cost', 12, 2)->nullable()->after('purchase_date');
            }
        });

        // Ensure status column accepts extended lifecycle states: available, deployed, under_maintenance, out_of_service, lost, retired
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('available', 'deployed', 'under_maintenance', 'out_of_service', 'lost', 'retired') NOT NULL DEFAULT 'available'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'retired_by')) {
                $table->dropConstrainedForeignId('retired_by');
            }
            if (Schema::hasColumn('assets', 'retirement_date')) {
                $table->dropColumn('retirement_date');
            }
            if (Schema::hasColumn('assets', 'retirement_reason')) {
                $table->dropColumn('retirement_reason');
            }
            if (Schema::hasColumn('assets', 'acquisition_cost')) {
                $table->dropColumn('acquisition_cost');
            }
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('available', 'deployed', 'under_maintenance', 'out_of_service') NOT NULL DEFAULT 'available'");
        }
    }
};

