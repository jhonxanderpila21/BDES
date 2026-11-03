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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 100)->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name', 100)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 100)->nullable()->after('middle_name');
            }
            if (!Schema::hasColumn('users', 'suffix')) {
                $table->string('suffix', 20)->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Backfill existing users' first_name and last_name from name
        DB::table('users')->whereNull('first_name')->orderBy('id')->get()->each(function ($user) {
            $raw = trim($user->name ?? '');
            if (!$raw) {
                return;
            }
            $parts = explode(' ', $raw);
            if (count($parts) === 1) {
                $first = $parts[0];
                $last = $parts[0];
            } else {
                $last = array_pop($parts);
                $first = implode(' ', $parts);
            }

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['first_name', 'middle_name', 'last_name', 'suffix']);
        });
    }
};

