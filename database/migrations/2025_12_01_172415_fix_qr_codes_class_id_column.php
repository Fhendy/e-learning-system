<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jika ada kolom class_model_id, rename ke class_id
        if (Schema::hasColumn('qr_codes', 'class_model_id')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->renameColumn('class_model_id', 'class_id');
            });
        }
        
        // Jika kolom class_id belum ada, tambahkan
        if (!Schema::hasColumn('qr_codes', 'class_id')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->foreignId('class_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Rollback
        if (Schema::hasColumn('qr_codes', 'class_id')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->renameColumn('class_id', 'class_model_id');
            });
        }
    }
};