<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('returned_by')->nullable();
            $table->date('return_date');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->text('damage_notes')->nullable();
            $table->text('missing_accessories')->nullable();
            $table->string('actual_location')->nullable();
            $table->enum('status', ['received', 'damage_noted', 'incomplete_accessories'])->default('received');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
