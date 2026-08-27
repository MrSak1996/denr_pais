<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Individual measure lines under a program, e.g. "6 signed PAMB
     * Resolutions approved by PAMB Chair...". A program can have more
     * than one measure (this is what drives the rowspan in the table).
     * Template/master data — no actual numbers live here.
     */
    public function up(): void
    {
        Schema::create('target_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_program_id')
                ->constrained('target_programs')
                ->cascadeOnDelete();
            $table->text('description');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_measures');
    }
};
