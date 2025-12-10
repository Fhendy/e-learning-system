<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Tambahkan kolom marked_by
            if (!Schema::hasColumn('attendances', 'marked_by')) {
                $table->foreignId('marked_by')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->comment('User yang menandai absensi');
            }
            
            // Tambahkan kolom accuracy jika belum ada
            if (!Schema::hasColumn('attendances', 'accuracy')) {
                $table->decimal('accuracy', 8, 2)
                    ->nullable()
                    ->after('longitude')
                    ->comment('Akurasi GPS dalam meter');
            }
            
            // Tambahkan kolom qr_code_id jika belum ada
            if (!Schema::hasColumn('attendances', 'qr_code_id')) {
                $table->foreignId('qr_code_id')
                    ->nullable()
                    ->constrained('qr_codes')
                    ->onDelete('set null')
                    ->after('class_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Hapus foreign key constraints
            if (Schema::hasColumn('attendances', 'marked_by')) {
                $table->dropForeign(['marked_by']);
                $table->dropColumn('marked_by');
            }
            
            if (Schema::hasColumn('attendances', 'qr_code_id')) {
                $table->dropForeign(['qr_code_id']);
                $table->dropColumn('qr_code_id');
            }
            
            if (Schema::hasColumn('attendances', 'accuracy')) {
                $table->dropColumn('accuracy');
            }
        });
    }
};