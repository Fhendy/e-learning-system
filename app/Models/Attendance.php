<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'qr_code_id',
        'attendance_date',
        'status',
        'checked_in_at',
        'latitude',
        'longitude',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'checked_in_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = [
        'status_icon',
        'status_color',
        'status_text',
        'formatted_checked_in_at',
        'is_late',
    ];

    /**
     * Get the student associated with the attendance.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class associated with the attendance.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the QR code associated with the attendance.
     */
    public function qrCode()
    {
        return $this->belongsTo(QRCode::class, 'qr_code_id');
    }

    /**
     * Get the user who marked the attendance.
     */
    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // =================== ACCESSORS ===================

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        $icons = [
            'present' => 'fa-check-circle',
            'late' => 'fa-clock',
            'absent' => 'fa-times-circle',
            'sick' => 'fa-hospital',
            'permission' => 'fa-user-clock'
        ];
        
        return $icons[$this->status] ?? 'fa-question-circle';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'sick' => 'info',
            'permission' => 'secondary'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get status text in Indonesian
     */
    public function getStatusTextAttribute()
    {
        $texts = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin'
        ];
        
        return $texts[$this->status] ?? $this->status;
    }

    /**
     * Get formatted checked in time
     */
    public function getFormattedCheckedInAtAttribute()
    {
        if (!$this->checked_in_at) {
            return null;
        }
        
        return Carbon::parse($this->checked_in_at)->format('H:i:s');
    }

    /**
     * Check if attendance is late
     */
    public function getIsLateAttribute()
    {
        return $this->status === 'late';
    }

    /**
     * Get display time (HH:MM or - if null)
     */
    public function getDisplayTimeAttribute()
    {
        if ($this->checked_in_at) {
            return Carbon::parse($this->checked_in_at)->format('H:i');
        }
        
        return '-';
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->attendance_date->format('d/m/Y');
    }

    /**
     * Get full formatted date
     */
    public function getFullDateAttribute()
    {
        return $this->attendance_date->format('l, d F Y');
    }

    // =================== METHODS ===================

    /**
     * Get status icon (method version for compatibility)
     */
    public function getStatusIcon()
    {
        return $this->status_icon;
    }

    /**
     * Get status color (method version for compatibility)
     */
    public function getStatusColor()
    {
        return $this->status_color;
    }

    /**
     * Get status text (method version for compatibility)
     */
    public function getStatusText()
    {
        return $this->status_text;
    }

    /**
     * Check if attendance has location data
     */
    public function hasLocation()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get location data
     */
    public function getLocationData()
    {
        if (!$this->hasLocation()) {
            return null;
        }
        
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'formatted' => "{$this->latitude}, {$this->longitude}"
        ];
    }

    /**
     * Check if attendance was marked manually
     */
    public function isManual()
    {
        return !is_null($this->marked_by) && is_null($this->qr_code_id);
    }

    /**
     * Check if attendance was via QR code
     */
    public function isQrBased()
    {
        return !is_null($this->qr_code_id);
    }

    // =================== SCOPES ===================

    /**
     * Scope for present attendances
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope for late attendances
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope for absent attendances
     */
    public function scopeAbsent($query)  // PERBAIKAN: Tambahkan kurung kurawal pembuka
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope for sick attendances
     */
    public function scopeSick($query)
    {
        return $query->where('status', 'sick');
    }

    /**
     * Scope for permission attendances
     */
    public function scopePermission($query)
    {
        return $query->where('status', 'permission');
    }

    /**
     * Scope for today's attendances
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    /**
     * Scope for student's attendances
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope for class attendances
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }
}