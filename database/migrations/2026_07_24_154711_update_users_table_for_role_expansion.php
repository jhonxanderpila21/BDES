<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        // Backfill username from the email local-part for existing rows.
        DB::table('users')->whereNull('username')->orderBy('id')->get()->each(function ($user) {
            $base = Str::slug(explode('@', $user->email)[0], '_');
            $username = $base;
            $suffix = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.'_'.$suffix++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        // Map the old two-value role vocabulary onto the new six-value one.
        DB::table('users')->where('role', 'admin')->update(['role' => 'administrator']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'viewer']);

        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER TABLE MODIFY; rebuild the column.
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['administrator', 'barangay_captain', 'inventory_officer', 'maintenance_officer', 'bdrrmc_officer', 'viewer'])->default('viewer')->after('password');
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY role ENUM('administrator', 'barangay_captain', 'inventory_officer', 'maintenance_officer', 'bdrrmc_officer', 'viewer') NOT NULL DEFAULT 'viewer'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->whereNotIn('role', ['admin', 'staff'])->update(['role' => 'staff']);
        DB::table('users')->where('role', 'administrator')->update(['role' => 'admin']);

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
