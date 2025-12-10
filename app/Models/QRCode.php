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

    // =================== ACCESSORS ===================

    /**
     * Accessor for formatted start time
     */
    public function getFormattedStartTimeAttribute()
    {
        try {
            // Handle time format HH:mm or HH:mm:ss
            $time = $this->start_time;
            if (strlen($time) === 5) {
                return $time; // Already HH:mm
            }
            return Carbon::parse($time)->format('H:i');
        } catch (\Exception $e) {
            return '00:00';
        }
    }

    /**
     * Accessor for formatted end time
     */
    public function getFormattedEndTimeAttribute()
    {
        try {
            // Handle time format HH:mm or HH:mm:ss
            $time = $this->end_time;
            if (strlen($time) === 5) {
                return $time; // Already HH:mm
            }
            return Carbon::parse($time)->format('H:i');
        } catch (\Exception $e) {
            return '00:00';
        }
    }

    /**
     * Get full start datetime
     */
    public function getFullStartDatetimeAttribute()
    {
        try {
            if (!$this->date || !$this->start_time) {
                Log::warning('Missing date or start_time for QR Code', [
                    'qr_code_id' => $this->id,
                    'date' => $this->date,
                    'start_time' => $this->start_time
                ]);
                return now()->addYear(); // Return future date if data invalid
            }
            
            // Format waktu dengan benar
            $startTime = $this->start_time;
            if (strlen($startTime) === 5) {
                $startTime .= ':00'; // Convert HH:mm to HH:mm:ss
            }
            
            // Pastikan format tanggal benar
            $dateString = $this->date instanceof Carbon 
                ? $this->date->format('Y-m-d') 
                : $this->date;
                
            return Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $startTime);
            
        } catch (\Exception $e) {
            Log::error('Error creating full_start_datetime', [
                'qr_code_id' => $this->id,
                'date' => $this->date,
                'start_time' => $this->start_time,
                'error' => $e->getMessage()
            ]);
            return now()->addYear();
        }
    }

    /**
     * Get full end datetime
     */
    public function getFullEndDatetimeAttribute()
    {
        try {
            if (!$this->date || !$this->end_time) {
                Log::warning('Missing date or end_time for QR Code', [
                    'qr_code_id' => $this->id,
                    'date' => $this->date,
                    'end_time' => $this->end_time
                ]);
                return now()->subYear(); // Return past date if data invalid
            }
            
            // Format waktu dengan benar
            $endTime = $this->end_time;
            if (strlen($endTime) === 5) {
                $endTime .= ':00'; // Convert HH:mm to HH:mm:ss
            }
            
            // Pastikan format tanggal benar
            $dateString = $this->date instanceof Carbon 
                ? $this->date->format('Y-m-d') 
                : $this->date;
                
            return Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $endTime);
            
        } catch (\Exception $e) {
            Log::error('Error creating full_end_datetime', [
                'qr_code_id' => $this->id,
                'date' => $this->date,
                'end_time' => $this->end_time,
                'error' => $e->getMessage()
            ]);
            return now()->subYear();
        }
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute()
    {
        try {
            return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
        } catch (\Exception $e) {
            Log::error('Error formatting time range', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 'Invalid Time';
        }
    }

    /**
     * Get duration in minutes (alias untuk compatibilitas)
     */
    public function getDurationMinutes()
    {
        return $this->duration_minutes_calculated;
    }

    /**
     * Get duration in minutes (accessor)
     */
    public function getDurationMinutesCalculatedAttribute()
    {
        try {
            // Gunakan full datetime untuk menghitung durasi
            $start = $this->full_start_datetime;
            $end = $this->full_end_datetime;
            
            if (!$start || !$end) {
                return $this->duration_minutes ?? 15;
            }
            
            return $end->diffInMinutes($start);
        } catch (\Exception $e) {
            Log::error('Error calculating duration', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return $this->duration_minutes ?? 15;
        }
    }

    /**
     * Get time remaining in minutes
     */
    public function getTimeRemainingAttribute()
    {
        try {
            $endDateTime = $this->full_end_datetime;
            $now = now();
            
            if (!$endDateTime) {
                return 0;
            }
            
            if ($now > $endDateTime) {
                return 0;
            }
            
            return $endDateTime->diffInMinutes($now);
        } catch (\Exception $e) {
            Log::error('Error calculating time remaining', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Check if QR code is expired
     */
    public function getIsExpiredAttribute()
    {
        try {
            $now = now();
            $endDateTime = $this->full_end_datetime;
            
            if (!$endDateTime) {
                Log::warning('No valid end datetime for QR Code expiry check', [
                    'qr_code_id' => $this->id
                ]);
                return true;
            }
            
            // Debug log untuk melihat perbandingan waktu
            Log::debug('QR Code Expiry Check', [
                'qr_code_id' => $this->id,
                'code' => $this->code,
                'date' => $this->date,
                'end_time' => $this->end_time,
                'formatted_end' => $this->formatted_end_time,
                'full_end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                'now' => $now->format('Y-m-d H:i:s'),
                'is_expired_calc' => $now > $endDateTime
            ]);
            
            return $now > $endDateTime;
            
        } catch (\Exception $e) {
            Log::error('Error checking if QR code is expired', [
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
            $startDateTime = $this->full_start_datetime;
            $now = now();
            
            if (!$startDateTime) {
                return 'Invalid start time';
            }
            
            if ($now > $startDateTime) {
                return 'Sudah dimulai';
            }
            
            return $startDateTime->diffForHumans($now, ['parts' => 2]);
        } catch (\Exception $e) {
            Log::error('Error calculating time until start', [
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
            $now = now();
            $startDateTime = $this->full_start_datetime;
            $endDateTime = $this->full_end_datetime;
            
            // Pastikan waktu valid
            if (!$startDateTime || !$endDateTime) {
                Log::warning('Invalid datetime for QR Code activity check', [
                    'qr_code_id' => $this->id,
                    'start_datetime' => $startDateTime,
                    'end_datetime' => $endDateTime
                ]);
                return false;
            }
            
            // Debug log
            Log::debug('QR Code Activity Check', [
                'qr_code_id' => $this->id,
                'code' => $this->code,
                'date' => $this->date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'full_start' => $startDateTime->format('Y-m-d H:i:s'),
                'full_end' => $endDateTime->format('Y-m-d H:i:s'),
                'now' => $now->format('Y-m-d H:i:s'),
                'is_between' => $now->between($startDateTime, $endDateTime)
            ]);
            
            return $now->between($startDateTime, $endDateTime);
            
        } catch (\Exception $e) {
            Log::error('Error checking QR code activity', [
                'qr_code_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
        
        $startDateTime = $this->full_start_datetime;
        if ($startDateTime && now() < $startDateTime) {
            return 'Belum dimulai';
        }
        
        return 'Selesai';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        if (!$this->is_active) {
            return 'secondary'; // Abu-abu untuk nonaktif
        }
        
        if ($this->is_expired) {
            return 'dark'; // Hitam untuk kadaluarsa
        }
        
        if ($this->is_active_now) {
            return 'success'; // Hijau untuk aktif
        }
        
        $startDateTime = $this->full_start_datetime;
        if ($startDateTime && now() < $startDateTime) {
            return 'warning'; // Kuning untuk belum dimulai
        }
        
        return 'info'; // Biru untuk selesai
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        $colors = [
            'Nonaktif' => 'badge-secondary',
            'Kadaluarsa' => 'badge-dark',
            'Aktif' => 'badge-success',
            'Belum dimulai' => 'badge-warning',
            'Selesai' => 'badge-info',
        ];
        
        return $colors[$this->status_text] ?? 'badge-secondary';
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
        
        $earthRadius = 6371000; // meters
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + 
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earthRadius;
        
        return $distance <= $this->radius;
    }

    // =================== SCOPES ===================

    /**
     * Scope for active QR codes
     */
    public function scopeActive($query)
    {
        $now = now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        return $query->where('is_active', true)
            ->whereDate('date', $today)
            ->where('end_time', '>', $currentTime);
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
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        return $query->where('is_active', true)
            ->where(function($q) use ($today, $currentTime) {
                $q->whereDate('date', '>', $today)
                  ->orWhere(function($q2) use ($today, $currentTime) {
                      $q2->whereDate('date', $today)
                         ->where('start_time', '>', $currentTime);
                  });
            })
            ->orderBy('date')
            ->orderBy('start_time');
    }

    /**
     * Scope for expired QR codes
     */
    public function scopeExpired($query)
    {
        $now = now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        return $query->where(function($q) use ($today, $currentTime) {
            $q->whereDate('date', '<', $today)
              ->orWhere(function($q2) use ($today, $currentTime) {
                  $q2->whereDate('date', $today)
                     ->where('end_time', '<', $currentTime);
              });
        });
    }

    /**
     * Scope for active QR codes (for current time)
     */
    public function scopeCurrentlyActive($query)
    {
        $now = now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        return $query->where('is_active', true)
            ->whereDate('date', $today)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime);
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
        return !empty($this->qr_code_image) && file_exists(storage_path('app/public/' . $this->qr_code_image));
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
            'date' => $this->date->format('Y-m-d'),
            'date_formatted' => $this->date->format('d F Y'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'time_range' => $this->formatted_time_range,
            'is_active' => $this->is_active,
            'is_expired' => $this->is_expired,
            'is_active_now' => $this->is_active_now,
            'location_restricted' => $this->location_restricted,
            'attendance_count' => $this->getAttendanceCount(),
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
        ];
    }

    /**
     * Get QR code validity information
     */
    public function getValidityInfo()
    {
        $now = now();
        $startDateTime = $this->full_start_datetime;
        $endDateTime = $this->full_end_datetime;
        
        return [
            'is_active' => $this->is_active,
            'is_active_now' => $this->is_active_now,
            'is_expired' => $this->is_expired,
            'is_future' => $startDateTime && $now < $startDateTime,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'current_datetime' => $now,
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
            'date' => $qr->date,
            'start_time' => $qr->start_time,
            'end_time' => $qr->end_time,
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
}