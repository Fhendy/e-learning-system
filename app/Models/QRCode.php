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
        'start_time' => 'string', // Simpan sebagai string
        'end_time' => 'string',   // Simpan sebagai string
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
    ];

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

private function cleanTimeString($time)
{
    if (empty($time)) {
        return '00:00:00';
    }
    
    // Jika ada spasi, ambil bagian terakhir
    if (strpos($time, ' ') !== false) {
        $parts = explode(' ', $time);
        $time = end($parts); // Ambil bagian terakhir
    }
    
    // Normalisasi format waktu
    return $this->normalizeTime($time);
}
public function setStartTimeAttribute($value)
{
    try {
        if (empty($value)) {
            $this->attributes['start_time'] = '00:00:00';
            return;
        }
        
        // Bersihkan string dari spasi atau format salah
        $cleanedValue = $this->cleanTimeString($value);
        
        // Simpan dalam format HH:MM:SS
        $this->attributes['start_time'] = $cleanedValue;
        
    } catch (\Exception $e) {
        \Log::warning('Error setting start_time', [
            'value' => $value,
            'error' => $e->getMessage()
        ]);
        $this->attributes['start_time'] = '00:00:00';
    }
}

/**
 * Mutator untuk end_time - DIPERBAIKI
 */
public function setEndTimeAttribute($value)
{
    try {
        if (empty($value)) {
            $this->attributes['end_time'] = '00:00:00';
            return;
        }
        
        // Bersihkan string dari spasi atau format salah
        $cleanedValue = $this->cleanTimeString($value);
        
        // Simpan dalam format HH:MM:SS
        $this->attributes['end_time'] = $cleanedValue;
        
    } catch (\Exception $e) {
        \Log::warning('Error setting end_time', [
            'value' => $value,
            'error' => $e->getMessage()
        ]);
        $this->attributes['end_time'] = '00:00:00';
    }
}

    // =================== ACCESSORS ===================

    /**
     * Accessor for formatted start time (HH:MM)
     */
    public function getFormattedStartTimeAttribute()
    {
        try {
            $time = $this->start_time;
            if (empty($time) || $time === '00:00:00') {
                return '00:00';
            }
            
            // Jika format sudah HH:MM
            if (strlen($time) === 5) {
                return $time;
            }
            
            // Konversi dari HH:MM:SS ke HH:MM
            return Carbon::createFromFormat('H:i:s', $time)->format('H:i');
        } catch (\Exception $e) {
            Log::warning('Error formatting start time', [
                'time' => $this->start_time,
                'error' => $e->getMessage()
            ]);
            return '00:00';
        }
    }

    /**
     * Accessor for formatted end time (HH:MM)
     */
    public function getFormattedEndTimeAttribute()
    {
        try {
            $time = $this->end_time;
            if (empty($time) || $time === '00:00:00') {
                return '00:00';
            }
            
            // Jika format sudah HH:MM
            if (strlen($time) === 5) {
                return $time;
            }
            
            // Konversi dari HH:MM:SS ke HH:MM
            return Carbon::createFromFormat('H:i:s', $time)->format('H:i');
        } catch (\Exception $e) {
            Log::warning('Error formatting end time', [
                'time' => $this->end_time,
                'error' => $e->getMessage()
            ]);
            return '00:00';
        }
    }

    /**
     * Get full start datetime with safe parsing
     */
public function getFullStartDatetimeAttribute()
{
    try {
        // Pastikan data yang diperlukan ada
        if (!$this->date || !$this->start_time) {
            return null;
        }
        
        // Pastikan format tanggal benar
        $dateString = $this->date instanceof Carbon 
            ? $this->date->format('Y-m-d') 
            : Carbon::parse($this->date)->format('Y-m-d');
        
        // Ambil start_time yang sudah diformat (HH:MM)
        $startTime = $this->formatted_start_time;
        
        // Debug: Log data untuk troubleshooting
        \Log::debug('Creating full_start_datetime', [
            'qr_code_id' => $this->id,
            'date' => $dateString,
            'start_time_raw' => $this->start_time,
            'formatted_start_time' => $startTime,
        ]);
        
        // Pastikan format waktu benar (HH:MM:SS)
        // Jika hanya HH:MM, tambahkan :00
        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }
        
        // Gabungkan tanggal dan waktu
        $datetimeString = $dateString . ' ' . $startTime;
        
        // Parse dengan format yang tepat
        return Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString);
        
    } catch (\Exception $e) {
        \Log::error('Error creating full_start_datetime', [
            'qr_code_id' => $this->id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'formatted_start_time' => $this->formatted_start_time ?? 'N/A',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return null;
    }
}

    /**
     * Get full end datetime with safe parsing
     */
public function getFullEndDatetimeAttribute()
{
    try {
        // Pastikan data yang diperlukan ada
        if (!$this->date || !$this->end_time) {
            return null;
        }
        
        // Pastikan format tanggal benar
        $dateString = $this->date instanceof Carbon 
            ? $this->date->format('Y-m-d') 
            : Carbon::parse($this->date)->format('Y-m-d');
        
        // Ambil end_time yang sudah diformat (HH:MM)
        $endTime = $this->formatted_end_time;
        
        // Debug: Log data untuk troubleshooting
        \Log::debug('Creating full_end_datetime', [
            'qr_code_id' => $this->id,
            'date' => $dateString,
            'end_time_raw' => $this->end_time,
            'formatted_end_time' => $endTime,
        ]);
        
        // Pastikan format waktu benar (HH:MM:SS)
        // Jika hanya HH:MM, tambahkan :00
        if (strlen($endTime) === 5) {
            $endTime .= ':00';
        }
        
        // Gabungkan tanggal dan waktu
        $datetimeString = $dateString . ' ' . $endTime;
        
        // Parse dengan format yang tepat
        return Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString);
        
    } catch (\Exception $e) {
        \Log::error('Error creating full_end_datetime', [
            'qr_code_id' => $this->id,
            'date' => $this->date,
            'end_time' => $this->end_time,
            'formatted_end_time' => $this->formatted_end_time ?? 'N/A',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return null;
    }
}
public function debugTimeInfo()
{
    return [
        'id' => $this->id,
        'code' => $this->code,
        'date' => $this->date ? $this->date->format('Y-m-d') : null,
        'start_time_raw' => $this->start_time,
        'end_time_raw' => $this->end_time,
        'formatted_start_time' => $this->formatted_start_time,
        'formatted_end_time' => $this->formatted_end_time,
        'full_start_datetime' => $this->full_start_datetime ? $this->full_start_datetime->format('Y-m-d H:i:s') : null,
        'full_end_datetime' => $this->full_end_datetime ? $this->full_end_datetime->format('Y-m-d H:i:s') : null,
        'is_active' => $this->is_active,
        'is_active_now' => $this->is_active_now,
        'is_expired' => $this->is_expired,
    ];
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
                return $this->duration_minutes ?? 30; // Default 30 menit
            }
            
            return $end->diffInMinutes($start);
        } catch (\Exception $e) {
            Log::warning('Error calculating duration', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
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
            
            $remaining = $end->diffInMinutes($now);
            return max(0, $remaining);
        } catch (\Exception $e) {
            Log::warning('Error calculating time remaining', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get time remaining in human readable format
     */
    public function getTimeRemainingHumanAttribute()
    {
        $minutes = $this->time_remaining;
        
        if ($minutes === 0) {
            return 'Berakhir';
        }
        
        if ($minutes < 60) {
            return $minutes . ' menit lagi';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes === 0) {
            return $hours . ' jam lagi';
        }
        
        return $hours . ' jam ' . $remainingMinutes . ' menit lagi';
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
            Log::warning('Error checking expiry', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
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
            Log::warning('Error calculating time until start', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
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
            Log::warning('Error checking active status', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
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
        
        // Cek apakah belum dimulai
        try {
            $start = $this->full_start_datetime;
            if ($start && now() < $start) {
                return 'Belum dimulai';
            }
        } catch (\Exception $e) {
            Log::warning('Error checking if not started', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return 'Selesai';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status_text) {
            case 'Aktif':
                return 'success';
            case 'Belum dimulai':
                return 'warning';
            case 'Kadaluarsa':
                return 'danger';
            case 'Nonaktif':
                return 'secondary';
            case 'Selesai':
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status_color) {
            case 'success':
                return 'badge-success';
            case 'warning':
                return 'badge-warning';
            case 'danger':
                return 'badge-danger';
            case 'secondary':
                return 'badge-secondary';
            case 'info':
                return 'badge-info';
            default:
                return 'badge-secondary';
        }
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
     * Get attendance percentage
     */
    public function getAttendancePercentage()
    {
        $stats = $this->getAttendanceStatistics();
        return $stats['percentage'];
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
        $this->save();
        
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
        
        // Haversine formula untuk menghitung jarak dalam meter
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

    /**
     * Scope for active QR codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for today's QR codes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope for upcoming QR codes
     */
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

    /**
     * Scope for expired QR codes
     */
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

    /**
     * Scope for currently active QR codes
     */
    public function scopeCurrentlyActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->whereDate('date', $now->format('Y-m-d'))
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'));
    }

    /**
     * Scope for QR codes by class
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for QR codes by creator
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope for QR codes with location restriction
     */
    public function scopeWithLocationRestriction($query)
    {
        return $query->where('location_restricted', true);
    }

    /**
     * Check if QR code can be edited
     */
    public function canBeEdited()
    {
        return !$this->is_expired && $this->is_active;
    }

    /**
     * Check if QR code can be deleted
     */
    public function canBeDeleted()
    {
        return $this->attendances()->count() === 0;
    }

    /**
     * Get QR code image URL
     */
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
            'time_remaining_human' => $this->time_remaining_human ?? $this->time_remaining . ' menit',
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
            'time_remaining_human' => $this->time_remaining_human ?? $this->time_remaining . ' menit',
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
     * Static method untuk debugging
     */
    public static function debugLatest()
    {
        $qr = self::latest()->first();
        if (!$qr) {
            return "No QR Code found";
        }
        
        $now = now();
        $start = $qr->full_start_datetime;
        $end = $qr->full_end_datetime;
        
        return [
            'id' => $qr->id,
            'code' => $qr->code,
            'date' => $qr->date ? $qr->date->format('Y-m-d') : null,
            'start_time_raw' => $qr->start_time,
            'end_time_raw' => $qr->end_time,
            'formatted_start' => $qr->formatted_start_time,
            'formatted_end' => $qr->formatted_end_time,
            'full_start' => $start ? $start->format('Y-m-d H:i:s') : 'Invalid',
            'full_end' => $end ? $end->format('Y-m-d H:i:s') : 'Invalid',
            'now' => $now->format('Y-m-d H:i:s'),
            'is_active' => $qr->is_active,
            'is_active_now' => $qr->is_active_now,
            'is_expired' => $qr->is_expired,
            'status_text' => $qr->status_text,
            'is_future' => $start && $now < $start,
            'is_past' => $end && $now > $end,
            'is_between' => $start && $end && $now->between($start, $end),
        ];
    }

    /**
     * Helper method untuk memastikan format waktu konsisten
     */
    public static function normalizeTime($time)
    {
        if (empty($time)) {
            return '00:00:00';
        }
        
        try {
            if (strlen($time) === 5) { // HH:MM
                return $time . ':00';
            } elseif (strlen($time) === 8) { // HH:MM:SS
                // Validasi format
                if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time)) {
                    return $time;
                } else {
                    // Coba parse
                    return Carbon::parse($time)->format('H:i:s');
                }
            } else {
                // Coba parse format lain
                return Carbon::parse($time)->format('H:i:s');
            }
        } catch (\Exception $e) {
            Log::warning('Error normalizing time', [
                'time' => $time,
                'error' => $e->getMessage()
            ]);
            return '00:00:00';
        }
    }

    /**
     * Validate waktu mulai dan selesai
     */
    public function validateTimes()
    {
        try {
            $start = $this->full_start_datetime;
            $end = $this->full_end_datetime;
            
            if (!$start || !$end) {
                return false;
            }
            
            return $end > $start;
        } catch (\Exception $e) {
            Log::warning('Error validating times', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cek apakah QR code valid untuk scan sekarang
     */
    public function isValidForScan($studentId = null)
    {
        // Cek apakah aktif
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'QR Code tidak aktif'];
        }
        
        // Cek apakah sudah expired
        if ($this->is_expired) {
            return ['valid' => false, 'message' => 'QR Code sudah kadaluarsa'];
        }
        
        // Cek apakah sudah aktif
        if (!$this->is_active_now) {
            $start = $this->full_start_datetime;
            if ($start && now() < $start) {
                return [
                    'valid' => false, 
                    'message' => 'QR Code belum aktif. Akan aktif pada ' . $start->format('H:i')
                ];
            } else {
                return ['valid' => false, 'message' => 'QR Code sudah selesai'];
            }
        }
        
        // Cek apakah siswa sudah absen
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