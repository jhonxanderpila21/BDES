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
        Schema::create('asset_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('asset_type');
            $table->string('accessory_name');
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->enum('status', ['Available', 'Not Available'])->default('Not Available');
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('asset_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_accessories');
    }
};
