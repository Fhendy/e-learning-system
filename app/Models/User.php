<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nis_nip',
        'role',
        'is_active',
        // HAPUS 'profile_photo_path' dari fillable jika kolom tidak ada
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // HAPUS $appends jika kolom tidak ada
    // protected $appends = [
    //     'profile_photo_url',
    // ];

    // =================== RELATIONSHIPS ===================

    /**
     * Get classes taught by teacher
     */
    public function teacherClasses()
    {
        return $this->hasMany(ClassModel::class, 'teacher_id');
    }

    /**
     * Get classes where student is enrolled
     */
    public function classesAsStudent()
    {
        return $this->belongsToMany(ClassModel::class, 'class_student', 'student_id', 'class_id')
            ->withTimestamps();
    }

    /**
     * Alias for backward compatibility
     */
/**
 * Get classes where student is enrolled
 */
public function studentClasses()
{
    return $this->belongsToMany(ClassModel::class, 'class_student', 'student_id', 'class_id')
        ->withTimestamps();
}

    /**
     * Get first class for backward compatibility
     */
    public function getClassAttribute()
    {
        return $this->classesAsStudent->first();
    }

    /**
     * Get attendance records as student
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * Get QR codes created by this user
     */
    public function createdQrCodes()
    {
        return $this->hasMany(QRCode::class, 'created_by');
    }

    /**
     * Get submissions as student
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    // =================== SCOPES ===================

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for students
     */
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    /**
     * Scope for teachers
     */
    public function scopeTeachers($query)
    {
        return $query->whereIn('role', ['teacher', 'guru']);
    }

    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // =================== METHODS ===================

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher()
    {
        return in_array($this->role, ['teacher', 'guru']);
    }

    /**
     * Check if user is student
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Get role text in Indonesian
     */
    public function getRoleTextAttribute()
    {
        $roles = [
            'admin' => 'Administrator',
            'teacher' => 'Guru',
            'guru' => 'Guru',
            'student' => 'Siswa',
        ];

        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Get profile photo URL (MODIFIKASI: handle jika kolom tidak ada)
     */
    public function getProfilePhotoUrlAttribute()
    {
        // Cek jika kolom profile_photo_path ada di tabel
        if (property_exists($this, 'profile_photo_path') && $this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return $this->defaultProfilePhotoUrl();
    }

    /**
     * Get default profile photo URL
     */
    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get total classes count for student
     */
    public function getTotalClassesAttribute()
    {
        if ($this->isStudent()) {
            return $this->classesAsStudent()->count();
        } elseif ($this->isTeacher()) {
            return $this->teacherClasses()->count();
        }

        return 0;
    }

    /**
     * Get total attendance count for student
     */
    public function getTotalAttendanceAttribute()
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->attendances()->count();
    }

    /**
     * Get attendance statistics for student
     */
    public function getAttendanceStatisticsAttribute()
    {
        if (!$this->isStudent()) {
            return null;
        }

        $total = $this->attendances()->count();
        $present = $this->attendances()->where('status', 'present')->count();
        $late = $this->attendances()->where('status', 'late')->count();
        $absent = $this->attendances()->where('status', 'absent')->count();

        $attended = $present + $late;
        $percentage = $total > 0 ? round(($attended / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'attended' => $attended,
            'percentage' => $percentage,
        ];
    }

    /**
     * Check if user can access a specific class
     */
    public function canAccessClass($classId)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isTeacher()) {
            return $this->teacherClasses()->where('id', $classId)->exists();
        }

        if ($this->isStudent()) {
            return $this->classesAsStudent()->where('classes.id', $classId)->exists();
        }

        return false;
    }
}