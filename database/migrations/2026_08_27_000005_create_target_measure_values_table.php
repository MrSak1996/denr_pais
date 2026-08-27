<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The actual submitted data for one measure, within one report.
     * This is what changes per Protected Area / per year — the
     * category/program/measure text itself never lives here.
     *
     * first_sem/second_sem/total_target are stored as strings because
     * the source report mixes plain counts (e.g. "2") with percentages
     * (e.g. "100%") in the same column.
     */
    public function up(): void
    {
        Schema::create('target_measure_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('target_report_id')
                ->constrained('target_reports')
                ->cascadeOnDelete();

            $table->foreignId('target_measure_id')
                ->constrained('target_measures')
                ->cascadeOnDelete();

            $table->string('first_sem')->nullable();
            $table->string('second_sem')->nullable();
            $table->string('total_target')->nullable();

            $table->string('q1_rating')->nullable();
            $table->string('q2_rating')->nullable();
            $table->string('q3_rating')->nullable(); // labeled "T3" in the source report

            $table->text('remarks')->nullable();

            $table->timestamps();

            // A measure can only be filled in once per report.
            $table->unique(['target_report_id', 'target_measure_id'], 'target_values_report_measure_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_measure_values');
    }
};
