<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dapatkan daftar kolom yang ada di tabel attendances
        $columns = DB::select('DESCRIBE attendances');
        $existingColumns = array_column($columns, 'Field');
        
        // Kolom yang seharusnya ada berdasarkan model Attendance
        $requiredColumns = [
            'student_id',
            'class_id',
            'qr_code_id',
            'attendance_date',
            'status',
            'checked_in_at',
            'latitude',
            'longitude',
            'accuracy',
            'notes',
            'marked_by',
            'created_at',
            'updated_at'
        ];
        
        // Cek kolom yang belum ada
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        
        if (!empty($missingColumns)) {
            Schema::table('attendances', function (Blueprint $table) use ($missingColumns) {
                foreach ($missingColumns as $column) {
                    $this->addColumn($table, $column);
                }
            });
        }
    }
    
    private function addColumn(Blueprint $table, string $column): void
    {
        switch ($column) {
            case 'marked_by':
                $table->foreignId('marked_by')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null');
                break;
                
            case 'qr_code_id':
                $table->foreignId('qr_code_id')
                    ->nullable()
                    ->constrained('qr_codes')
                    ->onDelete('set null');
                break;
                
            case 'accuracy':
                $table->decimal('accuracy', 8, 2)->nullable();
                break;
                
            case 'latitude':
            case 'longitude':
                $table->decimal($column, 10, 8)->nullable();
                break;
                
            case 'attendance_date':
                $table->date('attendance_date');
                break;
                
            case 'checked_in_at':
                $table->timestamp('checked_in_at')->nullable();
                break;
                
            case 'status':
                $table->string('status', 20)->default('present');
                break;
                
            case 'notes':
                $table->text('notes')->nullable();
                break;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migration ini tidak bisa di-rollback karena hanya mengecek
        // Untuk rollback, gunakan migration sebelumnya
    }
};