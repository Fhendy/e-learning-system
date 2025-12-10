<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'attachment',
        'status',
        'score',
        'feedback',
        'submitted_at',
        'graded_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime'
    ];

    // Relasi ke assignment
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Relasi ke student
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Accessor untuk attachment URL
    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? Storage::url($this->attachment) : null;
    }

    // Scope untuk submission berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk submission berdasarkan siswa
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Method untuk mendapatkan nilai dalam persen
    public function getScorePercentageAttribute()
    {
        if (!$this->score || !$this->assignment) {
            return null;
        }
        
        return round(($this->score / $this->assignment->max_score) * 100, 1);
    }

    // Method untuk mendapatkan label status
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Dikumpulkan',
            'late' => 'Terlambat',
            'graded' => 'Dinilai',
            'rejected' => 'Ditolak'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    // Method untuk mendapatkan warna status
    public function getStatusColorAttribute()
    {
        $colors = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'late' => 'warning',
            'graded' => 'success',
            'rejected' => 'danger'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }
}