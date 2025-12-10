<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'attachment',
        'notes',
        'score',
        'feedback',
        'status',
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
}