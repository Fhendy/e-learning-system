<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClassModel extends Model
{
    use HasFactory;
    
    protected $table = 'classes';
    
    // Use fillable for security
    protected $fillable = [
        'class_name',
        'class_code',
        'description',
        'teacher_id',
        'subject',
        'semester',
        'school_year',
        'academic_year',
        'is_active'
    ];
    
    // Cast attributes
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relasi dengan teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Relasi dengan students - SIMPLIFIED
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_student', 'class_id', 'student_id')
            ->withTimestamps();
    }

    // Relasi dengan QR codes (optional)
    public function qrCodes()
    {
        return $this->hasMany(QRCode::class, 'class_id');
    }

    // Relasi dengan attendances
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    // Relasi dengan assignments
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    // Scope untuk kelas aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get student count
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    // Check if student is enrolled
    public function hasStudent($studentId)
    {
        return $this->students()->where('users.id', $studentId)->exists();
    }

    // Add student to class
    public function addStudent($studentId)
    {
        if (!$this->hasStudent($studentId)) {
            $this->students()->attach($studentId);
            return true;
        }
        return false;
    }

    // Remove student from class
    public function removeStudent($studentId)
    {
        return $this->students()->detach($studentId);
    }

    // Accessor untuk status kelas
    public function getStatusAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    // Accessor untuk tahun ajaran format
    public function getAcademicYearFormattedAttribute()
    {
        if (!$this->academic_year && !$this->school_year) {
            return '-';
        }
        
        $year = $this->academic_year ?? $this->school_year;
        $years = explode('/', $year);
        
        if (count($years) === 2) {
            return "{$years[0]}/{$years[1]}";
        }
        
        return $year;
    }

    // Accessor untuk semester format
    public function getSemesterFormattedAttribute()
    {
        if (!$this->semester) {
            return '-';
        }
        
        return ucfirst($this->semester);
    }

    // Method untuk mendapatkan kode kelas yang valid
    public static function generateClassCode($className)
    {
        $code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $className));
        $code = substr($code, 0, 8);
        
        // Ensure uniqueness
        $counter = 1;
        $originalCode = $code;
        
        while (self::where('class_code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return $code;
    }
}