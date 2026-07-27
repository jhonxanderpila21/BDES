<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types')->onDelete('set null');
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
