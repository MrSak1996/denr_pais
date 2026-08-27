<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Top-level grouping shown as the gray divider rows in the report
     * (e.g. OPERATIONS, SUPPORT TO OPERATIONS, GENERAL AND ADMINISTRATIVE
     * SUPPORT (GAS)). This is template/master data shared across all
     * Protected Areas and reporting periods.
     */
    public function up(): void
    {
        Schema::create('target_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_categories');
    }
};
