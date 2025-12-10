<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Cek jika tabel class_student sudah ada
        if (Schema::hasTable('class_student')) {
            // Tambah kolom enrolled_at jika belum ada
            if (!Schema::hasColumn('class_student', 'enrolled_at')) {
                Schema::table('class_student', function (Blueprint $table) {
                    $table->timestamp('enrolled_at')->nullable()->after('student_id');
                });
            }
            
            // Update data yang sudah ada
            DB::table('class_student')->whereNull('enrolled_at')->update([
                'enrolled_at' => now()
            ]);
        } else {
            // Buat tabel class_student jika belum ada
            Schema::create('class_student', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('enrolled_at')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                
                $table->unique(['class_id', 'student_id']);
            });
        }
    }

    public function down()
    {
        // Hapus kolom enrolled_at jika rollback
        if (Schema::hasTable('class_student') && Schema::hasColumn('class_student', 'enrolled_at')) {
            Schema::table('class_student', function (Blueprint $table) {
                $table->dropColumn('enrolled_at');
            });
        }
    }
};