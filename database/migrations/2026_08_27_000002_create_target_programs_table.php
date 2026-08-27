<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Program/Project/Activity (P/AP/) rows, e.g. "PAMB Resolutions",
     * "Hiring of Staff". Each belongs to one category and can have
     * multiple measures. Template/master data.
     */
    public function up(): void
    {
        Schema::create('target_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_category_id')
                ->constrained('target_categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_programs');
    }
};
