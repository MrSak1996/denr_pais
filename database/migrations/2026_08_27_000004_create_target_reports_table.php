<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One report per Protected Area per reporting period (e.g. FY 2026).
     * This is the "instance" of the template being filled in by the
     * Focal Person. All actual submitted numbers hang off this via
     * target_measure_values.
     */
    public function up(): void
    {
        Schema::create('target_reports', function (Blueprint $table) {
            $table->id();

          $table->integer('protected_area_id')
                ->constrained('tbl_protected_area')
                ->cascadeOnDelete();

            // Focal Person who owns/submits this report.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('fiscal_year'); // e.g. 2026
            $table->enum('status', ['draft', 'submitted', 'approved', 'returned'])
                ->default('draft');
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            // One report per PA per year.
            $table->unique(['protected_area_id', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_reports');
    }
};
