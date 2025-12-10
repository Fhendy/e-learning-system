<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'class_id',
        'teacher_id',
        'due_date',
        'max_score',
        'attachment',
    ];

    public function isPastDue()
    {
        if (!$this->due_date) {
            return false;
        }
        
        $dueDate = $this->due_date instanceof Carbon 
            ? $this->due_date 
            : Carbon::parse($this->due_date);
            
        return now()->greaterThan($dueDate);
    }

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship Methods
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function submissionByStudent($studentId)
    {
        return $this->submissions()->where('student_id', $studentId)->first();
    }

    public function getSubmissionStats()
    {
        $total = $this->class ? $this->class->students->count() : 0;
        $submitted = $this->submissions->count();
        $graded = $this->submissions()->whereNotNull('score')->count();
        $late = $this->submissions()->where('status', 'late')->count();
        
        return [
            'total' => $total,
            'submitted' => $submitted,
            'graded' => $graded,
            'late' => $late,
            'submission_percentage' => $total > 0 ? round(($submitted / $total) * 100) : 0,
            'graded_percentage' => $submitted > 0 ? round(($graded / $submitted) * 100) : 0,
        ];
    }

    public function getDueDateStatus()
    {
        // Pastikan due_date adalah Carbon instance
        $dueDate = $this->due_date instanceof Carbon 
            ? $this->due_date 
            : Carbon::parse($this->due_date);
            
        if ($this->isPastDue()) {
            $daysLate = now()->diffInDays($dueDate);
            return [
                'status' => 'past',
                'label' => 'Selesai',
                'color' => 'danger',
                'days_left' => $daysLate > 0 ? 'Terlambat ' . $daysLate . ' hari' : 'Selesai hari ini'
            ];
        }
        
        $daysLeft = now()->diffInDays($dueDate, false);
        
        if ($daysLeft <= 3 && $daysLeft >= 0) {
            return [
                'status' => 'urgent',
                'label' => 'Mendesak',
                'color' => 'warning',
                'days_left' => $daysLeft . ' hari lagi'
            ];
        }
        
        return [
            'status' => 'active',
            'label' => 'Aktif',
            'color' => 'success',
            'days_left' => $daysLeft > 0 ? $daysLeft . ' hari lagi' : 'Hari ini'
        ];
    }

    // Helper method untuk mendapatkan due_date yang sudah diparse
    public function getParsedDueDate()
    {
        return $this->due_date instanceof Carbon 
            ? $this->due_date 
            : Carbon::parse($this->due_date);
    }

    // Format due_date untuk display
    public function getFormattedDueDate($format = 'd F Y, H:i')
    {
        if (!$this->due_date) {
            return 'Belum ditentukan';
        }
        
        $dueDate = $this->due_date instanceof Carbon 
            ? $this->due_date 
            : Carbon::parse($this->due_date);
            
        return $dueDate->format($format);
    }

    // Cek apakah student sudah submit
    public function hasStudentSubmitted($studentId)
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'late', 'graded'])
            ->exists();
    }
}