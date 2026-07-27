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
        Schema::table('asset_types', function (Blueprint $table) {
            $table->boolean('has_accessories')->default(false)->after('requires_inspection');
            $table->boolean('has_attachments')->default(false)->after('has_accessories');
            $table->boolean('requires_operational_requirements')->default(false)->after('has_attachments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropColumn(['has_accessories', 'has_attachments', 'requires_operational_requirements']);
        });
    }
};
