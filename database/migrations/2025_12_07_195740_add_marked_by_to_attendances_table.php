<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Tambahkan kolom marked_by jika belum ada
            if (!Schema::hasColumn('attendances', 'marked_by')) {
                $table->foreignId('marked_by')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->comment('User yang menandai absensi (guru atau siswa sendiri)');
            }
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['marked_by']);
            $table->dropColumn('marked_by');
        });
    }
};