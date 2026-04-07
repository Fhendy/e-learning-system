<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';
    
    protected $fillable = [
        'code',
        'class_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'location_restricted',
        'latitude',
        'longitude',
        'radius',
        'qr_code_image',
        'is_active',
        'scan_count',
        'notes',
        'created_by',
    ];
    
    protected $casts = [
        'date' => 'date',
        'location_restricted' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'scan_count' => 'integer',
        'duration_minutes' => 'integer',
        'start_time' => 'string',
        'end_time' => 'string',
    ];
    
    protected $appends = [
        'is_expired',
        'time_remaining',
        'status_text',
        'status_color',
        'formatted_time_range',
        'duration_minutes_calculated',
        'formatted_start_time',
        'formatted_end_time',
        'is_active_now',
        'time_until_start',
        'full_start_datetime',
        'full_end_datetime',
        'status_badge_class',
        'can_be_edited',  // Tambahkan ini
        'can_be_deleted', // Tambahkan ini
    ];
/**
 * Accessor for can_be_edited
 */
public function getCanBeEditedAttribute()
{
    return $this->canBeEdited();
}

/**
 * Accessor for can_be_deleted
 */
public function getCanBeDeletedAttribute()
{
    return $this->canBeDeleted();
}
    // Flag untuk mencegah infinite loop
    private static $processing = [];

    /**
     * Get the class that owns the QR code.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the user who created the QR code.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attendances for the QR code.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'qr_code_id');
    }

    /**
     * Normalize time string to HH:MM:SS format
     */
    private function normalizeTimeString($time)
    {
        if (empty($time)) {
            return '00:00:00';
        }
        
        // Jika ada spasi, ambil bagian terakhir
        if (strpos($time, ' ') !== false) {
            $parts = explode(' ', $time);
            $time = end($parts);
        }
        
        // Jika format HH:MM, tambahkan :00
        if (strlen($time) === 5 && preg_match('/^[0-9]{2}:[0-9]{2}$/', $time)) {
            return $time . ':00';
        }
        
        // Jika format HH:MM:SS sudah benar
        if (strlen($time) === 8 && preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $time)) {
            return $time;
        }
        
        // Default
        return '00:00:00';
    }

    // =================== ACCESSORS ===================

    /**
     * Accessor for formatted start time (HH:MM)
     */
    public function getFormattedStartTimeAttribute()
    {
        $time = $this->start_time;
        if (empty($time) || $time === '00:00:00') {
            return '00:00';
        }
        
        // Jika format sudah HH:MM
        if (strlen($time) === 5) {
            return $time;
        }
        
        // Jika format HH:MM:SS, ambil HH:MM
        if (strlen($time) === 8) {
            return substr($time, 0, 5);
        }
        
        return '00:00';
    }

    /**
     * Accessor for formatted end time (HH:MM)
     */
    public function getFormattedEndTimeAttribute()
    {
        $time = $this->end_time;
        if (empty($time) || $time === '00:00:00') {
            return '00:00';
        }
        
        // Jika format sudah HH:MM
        if (strlen($time) === 5) {
            return $time;
        }
        
        // Jika format HH:MM:SS, ambil HH:MM
        if (strlen($time) === 8) {
            return substr($time, 0, 5);
        }
        
        return '00:00';
    }

    /**
     * Get full start datetime - FIXED (no infinite loop)
     */
    public function getFullStartDatetimeAttribute()
    {
        $key = 'start_' . ($this->id ?? 'new');
        
        // Cegah infinite loop
        if (isset(self::$processing[$key])) {
            return null;
        }
        
        self::$processing[$key] = true;
        
        try {
            if (!$this->date || !$this->start_time) {
                self::$processing[$key] = false;
                unset(self::$processing[$key]);
                return null;
            }
            
            // Format tanggal
            if ($this->date instanceof Carbon) {
                $dateString = $this->date->format('Y-m-d');
            } else {
                $dateString = Carbon::parse($this->date)->format('Y-m-d');
            }
            
            // Format waktu - langsung dari start_time
            $startTime = $this->normalizeTimeString($this->start_time);
            
            $datetimeString = $dateString . ' ' . $startTime;
            $result = Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString);
            
            self::$processing[$key] = false;
            unset(self::$processing[$key]);
            
            return $result;
            
        } catch (\Exception $e) {
            self::$processing[$key] = false;
            unset(self::$processing[$key]);
            
            Log::warning('Error creating full_start_datetime', [
                'qr_code_id' => $this->id ?? 'new',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get full end datetime - FIXED (no infinite loop)
     */
    public function getFullEndDatetimeAttribute()
    {
        $key = 'end_' . ($this->id ?? 'new');
        
        // Cegah infinite loop
        if (isset(self::$processing[$key])) {
            return null;
        }
        
        self::$processing[$key] = true;
        
        try {
            if (!$this->date || !$this->end_time) {
                self::$processing[$key] = false;
                unset(self::$processing[$key]);
                return null;
            }
            
            // Format tanggal
            if ($this->date instanceof Carbon) {
                $dateString = $this->date->format('Y-m-d');
            } else {
                $dateString = Carbon::parse($this->date)->format('Y-m-d');
            }
            
            // Format waktu - langsung dari end_time
            $endTime = $this->normalizeTimeString($this->end_time);
            
            $datetimeString = $dateString . ' ' . $endTime;
            $result = Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString);
            
            self::$processing[$key] = false;
            unset(self::$processing[$key]);
            
            return $result;
            
        } catch (\Exception $e) {
            self::$processing[$key] = false;
            unset(self::$processing[$key]);
            
            Log::warning('Error creating full_end_datetime', [
                'qr_code_id' => $this->id ?? 'new',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute()
    {
        return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
    }

    /**
     * Get duration in minutes (calculated from times)
     */
    public function getDurationMinutesCalculatedAttribute()
    {
        try {
            $start = $this->full_start_datetime;
            $end = $this->full_end_datetime;
            
            if (!$start || !$end) {
                return $this->duration_minutes ?? 30;
            }
            
            return $end->diffInMinutes($start);
        } catch (\Exception $e) {
            return $this->duration_minutes ?? 30;
        }
    }

    /**
     * Get time remaining in minutes
     */
    public function getTimeRemainingAttribute()
    {
        try {
            $end = $this->full_end_datetime;
            $now = now();
            
            if (!$end) {
                return 0;
            }
            
            if ($now > $end) {
                return 0;
            }
            
            return max(0, $end->diffInMinutes($now));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if QR code is expired
     */
    public function getIsExpiredAttribute()
    {
        try {
            $end = $this->full_end_datetime;
            if (!$end) {
                return true;
            }
            return now() > $end;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Get time until start
     */
    public function getTimeUntilStartAttribute()
    {
        try {
            $start = $this->full_start_datetime;
            $now = now();
            
            if (!$start) {
                return 'Invalid start time';
            }
            
            if ($now > $start) {
                return 'Sudah dimulai';
            }
            
            $minutes = $start->diffInMinutes($now);
            
            if ($minutes < 60) {
                return $minutes . ' menit lagi';
            }
            
            return $start->diffForHumans($now, ['parts' => 2]);
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Get is_active_now attribute
     */
    public function getIsActiveNowAttribute()
    {
        if (!$this->is_active) {
            return false;
        }
        
        try {
            $start = $this->full_start_datetime;
            $end = $this->full_end_datetime;
            $now = now();
            
            if (!$start || !$end) {
                return false;
            }
            
            return $now >= $start && $now <= $end;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        if (!$this->is_active) {
            return 'Nonaktif';
        }
        
        if ($this->is_expired) {
            return 'Kadaluarsa';
        }
        
        if ($this->is_active_now) {
            return 'Aktif';
        }
        
        try {
            $start = $this->full_start_datetime;
            if ($start && now() < $start) {
                return 'Belum dimulai';
            }
        } catch (\Exception $e) {
            // ignore
        }
        
        return 'Selesai';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status_text) {
            'Aktif' => 'success',
            'Belum dimulai' => 'warning',
            'Kadaluarsa' => 'danger',
            'Nonaktif' => 'secondary',
            'Selesai' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status_color) {
            'success' => 'badge-success',
            'warning' => 'badge-warning',
            'danger' => 'badge-danger',
            'secondary' => 'badge-secondary',
            'info' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Get attendance count
     */
    public function getAttendanceCount()
    {
        return $this->attendances()->count();
    }

    /**
     * Get present attendance count
     */
    public function getPresentCount()
    {
        return $this->attendances()->where('status', 'present')->count();
    }

    /**
     * Get late attendance count
     */
    public function getLateCount()
    {
        return $this->attendances()->where('status', 'late')->count();
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStatistics()
    {
        $total = $this->attendances()->count();
        $present = $this->attendances()->where('status', 'present')->count();
        $late = $this->attendances()->where('status', 'late')->count();
        $absent = $this->attendances()->where('status', 'absent')->count();
        $sick = $this->attendances()->where('status', 'sick')->count();
        $permission = $this->attendances()->where('status', 'permission')->count();
        
        $attended = $present + $late;
        $percentage = $total > 0 ? round(($attended / $total) * 100, 1) : 0;
        
        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'sick' => $sick,
            'permission' => $permission,
            'attended' => $attended,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get total students in class
     */
    public function getTotalStudents()
    {
        return $this->class ? $this->class->students()->count() : 0;
    }

    /**
     * Get attendance summary
     */
    public function getAttendanceSummary()
    {
        $totalStudents = $this->getTotalStudents();
        $attended = $this->getPresentCount() + $this->getLateCount();
        
        return [
            'total_students' => $totalStudents,
            'attended' => $attended,
            'absent' => $totalStudents - $attended,
            'percentage' => $totalStudents > 0 ? round(($attended / $totalStudents) * 100, 1) : 0,
        ];
    }

    /**
     * Increment scan count
     */
    public function incrementScanCount()
    {
        $this->scan_count = $this->scan_count + 1;
        $this->saveQuietly(); // Gunakan saveQuietly untuk menghindari event loop
        
        return $this->scan_count;
    }

    /**
     * Check if location is restricted
     */
    public function hasLocationRestriction()
    {
        return $this->location_restricted && 
               $this->latitude !== null && 
               $this->longitude !== null && 
               $this->radius !== null;
    }

    /**
     * Get location data if restricted
     */
    public function getLocationData()
    {
        if (!$this->hasLocationRestriction()) {
            return null;
        }
        
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'radius_meters' => $this->radius,
            'radius_km' => round($this->radius / 1000, 2),
        ];
    }

    /**
     * Check if location is within radius
     */
    public function isLocationWithinRadius($latitude, $longitude)
    {
        if (!$this->hasLocationRestriction()) {
            return true;
        }
        
        $earthRadius = 6371000; // meters
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return $distance <= $this->radius;
    }

    // =================== SCOPES ===================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeUpcoming($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->where('date', '>', $now->format('Y-m-d'))
                  ->orWhere(function($q2) use ($now) {
                      $q2->whereDate('date', $now->format('Y-m-d'))
                         ->where('end_time', '>', $now->format('H:i:s'));
                  });
            });
    }

    public function scopeExpired($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->where('date', '<', $now->format('Y-m-d'))
              ->orWhere(function($q2) use ($now) {
                  $q2->whereDate('date', $now->format('Y-m-d'))
                     ->where('end_time', '<', $now->format('H:i:s'));
              });
        });
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->whereDate('date', $now->format('Y-m-d'))
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'));
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeWithLocationRestriction($query)
    {
        return $query->where('location_restricted', true);
    }

    /**
 * Check if QR code can be edited
 * 
 * @return bool
 */
public function canBeEdited()
{
    try {
        // Pastikan properti ada
        $isExpired = property_exists($this, 'is_expired') ? $this->is_expired : true;
        $isActive = $this->is_active ?? false;
        
        // QR code bisa diedit jika:
        // 1. Belum expired
        // 2. Aktif
        return $isActive && !$isExpired;
    } catch (\Exception $e) {
        Log::warning('Error checking if QR code can be edited', [
            'qr_code_id' => $this->id ?? null,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Check if QR code can be deleted
 * 
 * @return bool
 */
public function canBeDeleted()
{
    try {
        // QR code bisa dihapus jika belum ada absensi
        return $this->attendances()->count() === 0;
    } catch (\Exception $e) {
        Log::warning('Error checking if QR code can be deleted', [
            'qr_code_id' => $this->id ?? null,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}
    public function getImageUrl()
    {
        if (!$this->qr_code_image) {
            return null;
        }
        return asset('storage/' . $this->qr_code_image);
    }

    /**
     * Check if QR code has image
     */
    public function hasImage()
    {
        return !empty($this->qr_code_image);
    }

    /**
     * Get QR code data for scanning
     */
    public function getScanData()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'class_id' => $this->class_id,
            'class_name' => $this->class ? $this->class->class_name : 'N/A',
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'date_formatted' => $this->date ? $this->date->format('d F Y') : 'Invalid Date',
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'formatted_start_time' => $this->formatted_start_time,
            'formatted_end_time' => $this->formatted_end_time,
            'time_range' => $this->formatted_time_range,
            'is_active' => $this->is_active,
            'is_expired' => $this->is_expired,
            'is_active_now' => $this->is_active_now,
            'location_restricted' => $this->location_restricted,
            'attendance_count' => $this->getAttendanceCount(),
            'duration_minutes' => $this->duration_minutes_calculated,
            'notes' => $this->notes,
            'time_remaining' => $this->time_remaining,
            'status' => $this->status_text,
            'status_color' => $this->status_color,
        ];
    }

    /**
     * Get QR code validity information
     */
    public function getValidityInfo()
    {
        $now = now();
        $start = $this->full_start_datetime;
        $end = $this->full_end_datetime;
        
        return [
            'is_active' => $this->is_active,
            'is_active_now' => $this->is_active_now,
            'is_expired' => $this->is_expired,
            'is_future' => $start && $now < $start,
            'start_datetime' => $start ? $start->format('Y-m-d H:i:s') : null,
            'end_datetime' => $end ? $end->format('Y-m-d H:i:s') : null,
            'current_datetime' => $now->format('Y-m-d H:i:s'),
            'time_remaining' => $this->time_remaining,
            'time_until_start' => $this->time_until_start,
        ];
    }
    
    /**
     * Alias method for is_active_now
     */
    public function isActive()
    {
        return $this->is_active_now;
    }

    /**
     * Cek apakah QR code valid untuk scan sekarang
     */
    public function isValidForScan($studentId = null)
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'QR Code tidak aktif'];
        }
        
        if ($this->is_expired) {
            return ['valid' => false, 'message' => 'QR Code sudah kadaluarsa'];
        }
        
        if (!$this->is_active_now) {
            $start = $this->full_start_datetime;
            if ($start && now() < $start) {
                return [
                    'valid' => false, 
                    'message' => 'QR Code belum aktif. Akan aktif pada ' . $start->format('H:i')
                ];
            }
            return ['valid' => false, 'message' => 'QR Code sudah selesai'];
        }
        
        if ($studentId) {
            $existing = $this->attendances()->where('student_id', $studentId)->first();
            if ($existing) {
                return [
                    'valid' => false, 
                    'message' => 'Anda sudah melakukan absensi untuk QR Code ini',
                    'attendance' => $existing
                ];
            }
        }
        
        return ['valid' => true, 'message' => 'QR Code valid'];
    }
    
}