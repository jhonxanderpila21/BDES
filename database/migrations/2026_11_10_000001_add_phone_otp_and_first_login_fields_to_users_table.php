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
            $table->string('email')->nullable()->change();

            if (! Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone_number');
            }

            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
        });

        // Ensure existing users are not forced into onboarding unless explicitly flagged
        DB::table('users')
            ->whereNull('must_change_password')
            ->update(['must_change_password' => false]);

        if (! Schema::hasTable('user_otps')) {
            Schema::create('user_otps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('otp_hash');
                $table->timestamp('expires_at');
                $table->timestamp('verified_at')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamps();

                $table->index(['user_id', 'expires_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otps');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
            if (Schema::hasColumn('users', 'phone_verified_at')) {
                $table->dropColumn('phone_verified_at');
            }
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropUnique(['phone_number']);
                $table->dropColumn('phone_number');
            }
        });
    }
};

