<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('classes', function (Blueprint $table) {
        // Rename academic_year to school_year if exists
        if (Schema::hasColumn('classes', 'academic_year')) {
            $table->renameColumn('academic_year', 'school_year');
        }
        
        // Add is_active if not exists
        if (!Schema::hasColumn('classes', 'is_active')) {
            $table->boolean('is_active')->default(true);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
