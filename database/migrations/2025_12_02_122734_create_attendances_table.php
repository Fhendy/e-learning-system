<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->unsignedBigInteger('qr_code_id')->nullable(); // Tidak pakai foreign key dulu
            $table->date('attendance_date');
            $table->enum('status', ['present', 'late', 'absent', 'sick', 'permission']);
            $table->time('checked_in_at')->nullable();
            $table->time('checked_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'class_id', 'attendance_date']);
            $table->index(['class_id', 'attendance_date']);
            $table->index(['qr_code_id', 'attendance_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};