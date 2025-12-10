<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            // Tambahkan kolom subject jika belum ada
            if (!Schema::hasColumn('classes', 'subject')) {
                $table->string('subject')->nullable()->after('description');
            }
            
            // Tambahkan kolom lain yang mungkin belum ada
            if (!Schema::hasColumn('classes', 'semester')) {
                $table->enum('semester', ['ganjil', 'genap'])->nullable()->after('subject');
            }
            
            if (!Schema::hasColumn('classes', 'academic_year')) {
                $table->string('academic_year')->nullable()->after('semester');
            }
            
            if (!Schema::hasColumn('classes', 'school_year')) {
                $table->string('school_year')->nullable()->after('academic_year');
            }
            
            if (!Schema::hasColumn('classes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('teacher_id');
            }
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            // Tidak perlu rollback jika ingin tetap ada
        });
    }
};