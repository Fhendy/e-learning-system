<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Halaman scan QR code untuk siswa
     */
    public function scanPage(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Hanya siswa yang dapat melakukan scan QR Code.');
        }
        
        $qrCodeParam = $request->get('qr_code');
        
        // Get active QR codes for student's classes today
        $activeQrCodes = QRCode::whereHas('class', function($query) use ($user) {
                $query->whereHas('students', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->where('is_active', true)
            ->whereDate('date', today())
            ->where('end_time', '>', now()->format('H:i:s'))
            ->with('class')
            ->get();
        
        // Check specific QR code if provided
        $specificQrCode = null;
        if ($qrCodeParam) {
            $specificQrCode = QRCode::where('code', $qrCodeParam)
                ->where('is_active', true)
                ->whereDate('date', today())
                ->where('end_time', '>', now()->format('H:i:s'))
                ->first();
            
            if ($specificQrCode) {
                $isInClass = $specificQrCode->class->students()
                    ->where('users.id', $user->id)
                    ->exists();
                
                if (!$isInClass) {
                    $specificQrCode = null;
                }
            }
        }
        
        // Check today's attendance
        $todayAttendance = Attendance::where('student_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();
        
        return view('attendance.student-scan', compact(
            'activeQrCodes',
            'specificQrCode',
            'todayAttendance',
            'qrCodeParam'
        ));
    }

    /**
     * Tampilkan halaman konfirmasi scan
     */
    public function showConfirm($code)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return redirect()->route('dashboard')->with('error', 'Hanya siswa yang dapat melakukan scan QR Code.');
        }
        
        $qrCode = QRCode::where('code', $code)
            ->where('is_active', true)
            ->with('class.teacher')
            ->first();
        
        if (!$qrCode) {
            return view('attendance.scan-error', [
                'message' => 'QR Code tidak ditemukan atau sudah tidak aktif.'
            ]);
        }
        
        // Validasi QR Code
        $validation = $this->validateQrCode($qrCode, $user);
        
        if (!$validation['valid']) {
            return view('attendance.scan-error', [
                'message' => $validation['message']
            ]);
        }
        
        $allowStatusSelection = false;
        
        return view('attendance.scan-confirm', compact('qrCode', 'allowStatusSelection'));
    }

/**
 * Proses scan QR Code dengan validasi lokasi
 */
public function processScan(Request $request)
{
    Log::info('ProcessScan called', [
        'qr_code' => $request->qr_code,
        'student_id' => $request->student_id,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude
    ]);

    $validator = Validator::make($request->all(), [
        'qr_code' => 'required|string',
        'student_id' => 'required|exists:users,id',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    DB::beginTransaction();

    try {
        $user = Auth::user();
        
        // Cek role
        if ($user->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya siswa yang dapat melakukan absensi'
            ], 403);
        }

        // Cek kecocokan student_id
        if ($user->id != $request->student_id) {
            return response()->json([
                'success' => false,
                'message' => 'ID siswa tidak sesuai'
            ], 403);
        }

        // Ekstrak kode QR
        $rawCode = trim($request->qr_code);
        $qrCodeValue = $this->extractQrCodeValue($rawCode);
        
        Log::info('Extracted QR code', [
            'raw' => $rawCode,
            'extracted' => $qrCodeValue
        ]);

        // Cari QR Code
        $qrCode = QRCode::where('code', $qrCodeValue)->first();

        if (!$qrCode) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan. Kode: "' . $qrCodeValue . '"'
            ], 404);
        }

        // Cek status aktif
        if (!$qrCode->is_active) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak aktif. Silakan hubungi guru.'
            ], 400);
        }

        // Validasi tanggal
        if (!$qrCode->date->isToday()) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'QR Code hanya berlaku untuk tanggal: ' . $qrCode->date->format('d F Y')
            ], 400);
        }

        // Validasi waktu
        $currentTime = now();
        $startTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->start_time);
        $endTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->end_time);

        if ($currentTime < $startTime) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'QR Code belum aktif. Akan aktif pada: ' . $startTime->format('H:i')
            ], 400);
        }

        if ($currentTime > $endTime) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'QR Code sudah kadaluarsa. Berakhir pada: ' . $endTime->format('H:i')
            ], 400);
        }

        // ==================== VALIDASI LOKASI ====================
        if ($qrCode->location_restricted && $qrCode->latitude && $qrCode->longitude) {
            // Ambil lokasi dari request
            $userLat = $request->latitude;
            $userLng = $request->longitude;
            
            // Cek apakah lokasi tersedia
            if (!$userLat || !$userLng) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi diperlukan untuk absensi ini. Silakan izinkan akses lokasi di browser Anda.'
                ], 400);
            }
            
            // Hitung jarak
            $distance = $this->calculateDistance($userLat, $userLng, $qrCode->latitude, $qrCode->longitude);
            
            Log::info('Location validation', [
                'user_location' => "{$userLat}, {$userLng}",
                'qr_location' => "{$qrCode->latitude}, {$qrCode->longitude}",
                'distance' => round($distance) . ' meters',
                'radius' => $qrCode->radius . ' meters'
            ]);
            
            // Cek apakah dalam radius
            if ($distance > $qrCode->radius) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Anda berada di luar radius absensi.\n📍 Jarak Anda: " . round($distance) . " meter\n📍 Radius yang diizinkan: {$qrCode->radius} meter\n\nSilakan mendekat ke lokasi absensi.",
                    'data' => [
                        'distance' => round($distance),
                        'radius' => $qrCode->radius,
                        'latitude' => $qrCode->latitude,
                        'longitude' => $qrCode->longitude
                    ]
                ], 400);
            }
        }

        // Cek keanggotaan kelas
        $isInClass = $qrCode->class->students()->where('users.id', $user->id)->exists();

        if (!$isInClass) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar di kelas ini. Silakan hubungi guru Anda.'
            ], 403);
        }

        // Cek apakah sudah absen
        $existingAttendance = Attendance::where('student_id', $user->id)
            ->where('qr_code_id', $qrCode->id)
            ->first();

        if ($existingAttendance) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi untuk QR Code ini',
                'data' => [
                    'attendance_id' => $existingAttendance->id,
                    'status' => $this->getStatusText($existingAttendance->status),
                    'time' => $existingAttendance->checked_in_at ? Carbon::parse($existingAttendance->checked_in_at)->format('H:i:s') : '-'
                ]
            ], 400);
        }

        // Tentukan status (present atau late)
        $status = 'present';
        $toleranceMinutes = 15;
        $lateTime = $startTime->copy()->addMinutes($toleranceMinutes);
        
        if ($currentTime > $lateTime && $currentTime <= $endTime) {
            $status = 'late';
        }

        // Buat absensi
        $attendance = Attendance::create([
            'student_id' => $user->id,
            'class_id' => $qrCode->class_id,
            'qr_code_id' => $qrCode->id,
            'attendance_date' => today(),
            'status' => $status,
            'checked_in_at' => $currentTime,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'marked_by' => $user->id,
        ]);

        // Update scan count
        $qrCode->increment('scan_count');

        DB::commit();

        Log::info('Attendance created successfully', [
            'attendance_id' => $attendance->id,
            'status' => $status,
            'location_verified' => $qrCode->location_restricted ? 'yes' : 'no'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'data' => [
                'attendance_id' => $attendance->id,
                'student_name' => $user->name,
                'class_name' => $qrCode->class->class_name,
                'status' => $this->getStatusText($status),
                'time' => $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->format('H:i:s') : '-',
                'date' => $attendance->attendance_date->format('d-m-Y'),
                'location_verified' => $qrCode->location_restricted ? true : false,
                'distance' => isset($distance) ? round($distance) : null
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error processing attendance', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Hitung jarak antara dua koordinat (dalam meter)
 * Menggunakan Haversine formula
 */
private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000; // meter
    
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($latFrom) * cos($latTo) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

/**
 * Ekstrak kode QR dari berbagai format input
 */
private function extractQrCodeValue($input)
{
    // Jika input adalah URL yang mengandung parameter qr_code
    if (strpos($input, 'qr_code=') !== false) {
        // Parse URL
        parse_str(parse_url($input, PHP_URL_QUERY) ?: '', $params);
        if (isset($params['qr_code'])) {
            return $params['qr_code'];
        }
        
        // Fallback: gunakan regex
        if (preg_match('/qr_code[=:]([A-Z0-9]+)/i', $input, $matches)) {
            return $matches[1];
        }
    }
    
    // Jika input adalah kode 8 karakter alfanumerik
    if (preg_match('/^[A-Z0-9]{6,10}$/i', $input)) {
        return strtoupper($input);
    }
    
    // Jika input adalah angka (ID)
    if (is_numeric($input)) {
        return $input;
    }
    
    return $input;
}

public function showResult($id)
{
    $attendance = Attendance::with(['class', 'qrCode'])->findOrFail($id);
    
    $user = Auth::user();
    
    // Admin dan guru bisa melihat semua
    if (in_array($user->role, ['admin', 'teacher', 'guru'])) {
        return view('attendance.scan-result', compact('attendance'));
    }
    
    // Siswa hanya bisa melihat absensinya sendiri
    if ($user->role === 'student' && $attendance->student_id != $user->id) {
        // Redirect ke dashboard dengan pesan error
        return redirect()->route('dashboard')
            ->with('error', 'Anda tidak memiliki akses ke data absensi ini.');
    }
    
    return view('attendance.scan-result', compact('attendance'));
}

    /**
     * Dashboard Absensi Siswa
     */
    public function indexStudent(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $classes = $user->studentClasses()->get();
        
        $attendances = Attendance::where('student_id', $user->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['class', 'qrCode'])
            ->orderBy('attendance_date', 'desc')
            ->paginate(10);
        
        return view('attendance.student-index', compact('attendances', 'month', 'year', 'classes'));
    }

    /**
     * Detail absensi siswa
     */
    public function showStudent(Attendance $attendance)
    {
        $user = Auth::user();
        
        if ($user->role === 'student' && $attendance->student_id !== $user->id) {
            abort(403);
        }
        
        $attendance->load(['class', 'student', 'qrCode']);
        
        return view('attendance.student-show', compact('attendance'));
    }

    /**
     * Dashboard Absensi Guru
     */
    public function indexTeacher(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        $classes = ClassModel::where('teacher_id', $user->id)
            ->withCount('students')
            ->get();
        
        $query = Attendance::with(['student', 'class'])
            ->whereHas('class', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }
        
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(15);
        
        return view('attendance.teacher-index', compact('classes', 'attendances'));
    }

    /**
     * Detail absensi kelas
     */
    public function showClass($classId, Request $request)
    {
        $user = Auth::user();
        
        $class = ClassModel::with('students')->findOrFail($classId);
        
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $classes = ClassModel::where('teacher_id', $user->id)->get();
        
        $dates = collect();
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->push($date->copy());
        }
        
        $students = $class->students()->with(['attendances' => function($q) use ($month, $year, $classId) {
            $q->whereMonth('attendance_date', $month)
              ->whereYear('attendance_date', $year)
              ->where('class_id', $classId);
        }])->get();
        
        $totalStats = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'sick' => 0,
            'permission' => 0,
        ];
        
        foreach ($students as $student) {
            foreach ($student->attendances as $attendance) {
                if (isset($totalStats[$attendance->status])) {
                    $totalStats[$attendance->status]++;
                }
            }
        }
        
        $totalSessions = $dates->count();
        $totalAttended = $totalStats['present'] + $totalStats['late'];
        $totalPossible = $totalSessions * $students->count();
        $attendanceRate = $totalPossible > 0 ? round(($totalAttended / $totalPossible) * 100, 1) : 0;
        
        return view('attendance.class-detail', compact(
            'class',
            'classes',
            'students',
            'dates',
            'totalStats',
            'totalSessions',
            'attendanceRate',
            'month',
            'year'
        ));
    }

    /**
     * Halaman manual entry absensi
     */
    public function createManual(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        $classes = ClassModel::where('teacher_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        $defaultDate = now()->format('Y-m-d');
        $defaultTime = now()->format('H:i');
        
        $students = collect();
        if ($request->has('class_id')) {
            $class = ClassModel::find($request->class_id);
            if ($class && $class->teacher_id === $user->id) {
                $students = $class->students()
                    ->orderBy('name')
                    ->get();
            }
        }
        
        return view('attendance.teacher.manual-create', compact(
            'classes',
            'students',
            'defaultDate',
            'defaultTime'
        ));
    }

    /**
     * Store manual attendance entry
     */
    public function storeManual(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'student_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,late,absent,sick,permission',
            'checked_in_at' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $class = ClassModel::find($request->class_id);
        if (!$class || ($class->teacher_id !== $user->id && $user->role !== 'admin')) {
            return redirect()->back()->with('error', 'Anda tidak mengajar kelas ini.');
        }
        
        $isInClass = $class->students()->where('users.id', $request->student_id)->exists();
        
        if (!$isInClass) {
            return redirect()->back()->with('error', 'Siswa tidak terdaftar di kelas ini.');
        }
        
        $checkedInAt = null;
        if ($request->checked_in_at) {
            $checkedInAt = Carbon::parse($request->attendance_date . ' ' . $request->checked_in_at);
        } elseif (in_array($request->status, ['present', 'late'])) {
            $checkedInAt = Carbon::now();
        }
        
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();
        
        if ($existingAttendance) {
            $existingAttendance->update([
                'status' => $request->status,
                'checked_in_at' => $checkedInAt,
                'notes' => $request->notes,
                'marked_by' => $user->id,
            ]);
            $message = 'Absensi berhasil diperbarui.';
        } else {
            Attendance::create([
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'attendance_date' => $request->attendance_date,
                'status' => $request->status,
                'checked_in_at' => $checkedInAt,
                'notes' => $request->notes,
                'marked_by' => $user->id
            ]);
            $message = 'Absensi berhasil dicatat.';
        }
        
        if ($request->has('submit_and_new')) {
            return redirect()->route('attendance.teacher.manual.create')
                ->with('success', $message . ' Silakan tambah absensi lainnya.');
        }
        
        return redirect()->route('attendance.teacher.index')
            ->with('success', $message);
    }

    /**
     * Bulk manual attendance entry
     */
    public function createBulkManual()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        $classes = ClassModel::where('teacher_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        $defaultDate = now()->format('Y-m-d');
        
        return view('attendance.teacher.manual-bulk', compact('classes', 'defaultDate'));
    }

    /**
     * Store bulk manual attendance
     */
    public function storeBulkManual(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'attendance_date' => 'required|date',
            'attendance_data' => 'required|array',
            'attendance_data.*.student_id' => 'required|exists:users,id',
            'attendance_data.*.status' => 'required|in:present,late,absent,sick,permission',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $class = ClassModel::find($request->class_id);
        if (!$class || ($class->teacher_id !== $user->id && $user->role !== 'admin')) {
            return redirect()->back()->with('error', 'Anda tidak mengajar kelas ini.');
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($request->attendance_data as $data) {
                $isInClass = $class->students()->where('users.id', $data['student_id'])->exists();
                
                if (!$isInClass) {
                    $errorCount++;
                    continue;
                }
                
                $checkedInAt = Carbon::parse($request->attendance_date . ' ' . now()->format('H:i:s'));
                
                $existingAttendance = Attendance::where('student_id', $data['student_id'])
                    ->where('class_id', $request->class_id)
                    ->whereDate('attendance_date', $request->attendance_date)
                    ->first();
                
                if ($existingAttendance) {
                    $existingAttendance->update([
                        'status' => $data['status'],
                        'checked_in_at' => $checkedInAt,
                        'marked_by' => $user->id,
                    ]);
                } else {
                    Attendance::create([
                        'student_id' => $data['student_id'],
                        'class_id' => $request->class_id,
                        'attendance_date' => $request->attendance_date,
                        'status' => $data['status'],
                        'checked_in_at' => $checkedInAt,
                        'marked_by' => $user->id
                    ]);
                }
                
                $successCount++;
            }
            
            DB::commit();
            
            $message = "Berhasil menyimpan {$successCount} absensi.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} absensi gagal.";
            }
            
            return redirect()->route('attendance.teacher.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing bulk attendance', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Edit absensi
     */
    public function edit(Attendance $attendance)
    {
        $user = Auth::user();
        
        if ($attendance->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $classes = ClassModel::where('teacher_id', $user->id)->get();
        $students = $attendance->class->students;
        
        $qrCodes = QRCode::where('class_id', $attendance->class_id)
            ->where('is_active', true)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
        
        return view('attendance.edit', compact('attendance', 'classes', 'students', 'qrCodes'));
    }

    /**
     * Update absensi
     */
    public function update(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        
        if ($attendance->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'student_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,late,absent,sick,permission',
            'checked_in_at' => 'nullable|date_format:H:i:s',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $checkedInAt = null;
        if ($request->checked_in_at) {
            $checkedInAt = Carbon::parse($request->attendance_date . ' ' . $request->checked_in_at);
        }
        
        $attendance->update([
            'class_id' => $request->class_id,
            'student_id' => $request->student_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'checked_in_at' => $checkedInAt,
            'notes' => $request->notes,
            'marked_by' => $user->id,
        ]);
        
        return redirect()->route('attendance.teacher.index')->with('success', 'Absensi berhasil diperbarui.');
    }

    /**
     * Hapus absensi
     */
    public function destroy(Attendance $attendance)
    {
        $user = Auth::user();
        
        if ($attendance->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $attendance->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Absensi berhasil dihapus.']);
        }
        
        return redirect()->route('attendance.teacher.index')->with('success', 'Absensi berhasil dihapus.');
    }

    /**
     * Mark attendance (AJAX)
     */
    public function markAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'status' => 'required|in:present,late,absent,sick,permission',
            'notes' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        
        $class = ClassModel::find($request->class_id);
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$class->students()->where('users.id', $request->student_id)->exists()) {
            return response()->json(['error' => 'Siswa tidak ditemukan di kelas ini'], 404);
        }

        $attendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->whereDate('attendance_date', $request->date)
            ->first();

        if ($attendance) {
            $attendance->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'marked_by' => $user->id
            ]);
            $message = 'Absensi berhasil diperbarui';
        } else {
            Attendance::create([
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'attendance_date' => $request->date,
                'status' => $request->status,
                'checked_in_at' => Carbon::now(),
                'notes' => $request->notes,
                'marked_by' => $user->id
            ]);
            $message = 'Absensi berhasil dicatat';
        }

        return response()->json([
            'success' => true, 
            'message' => $message,
            'data' => [
                'student_id' => $request->student_id,
                'status' => $request->status
            ]
        ]);
    }

    /**
     * Get class students (AJAX)
     */
    public function getClassStudents(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            return response()->json([], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid class'], 422);
        }
        
        $class = ClassModel::find($request->class_id);
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $students = $class->students()
            ->orderBy('name')
            ->get(['id', 'name', 'nis_nip', 'email'])
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'text' => $student->name . ' (' . $student->nis_nip . ')',
                    'nis_nip' => $student->nis_nip,
                    'name' => $student->name
                ];
            });
        
        return response()->json($students);
    }

/**
 * Generate QR Code cepat (30 menit)
 */
public function quickGenerateQr(Request $request, $classId)
{
    $user = Auth::user();
    
    if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak'
        ], 403);
    }

    $class = ClassModel::findOrFail($classId);

    if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak mengajar kelas ini'
        ], 403);
    }

    DB::beginTransaction();

    try {
        // Generate unique code
        $code = $this->generateUniqueCode();
        
        // Set waktu: 30 menit dari sekarang
        $startTime = now();
        $endTime = now()->addMinutes(30);
        
        // PERBAIKAN: Jika melewati tengah malam, tanggal tetap sama
        // Karena endTime bisa melewati tengah malam, tapi tanggal QR code tetap hari ini
        // Untuk validasi absensi nanti, kita akan cek waktu dengan tanggal yang sama

        // Create QR code record
        $qrCode = QRCode::create([
            'code' => $code,
            'class_id' => $class->id,
            'date' => today(),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'duration_minutes' => 30,
            'is_active' => true,
            'created_by' => $user->id,
            'scan_count' => 0,
        ]);

        // Generate QR code image
        $url = url('/attendance/scan-page') . '?qr_code=' . $code;
        $qrCodeImage = $this->generateQrCodeImage($url, $code);

        $imageName = 'qr-codes/quick-' . $code . '.png';
        
        if (!Storage::disk('public')->exists('qr-codes')) {
            Storage::disk('public')->makeDirectory('qr-codes');
        }
        
        Storage::disk('public')->put($imageName, $qrCodeImage);
        $qrCode->update(['qr_code_image' => $imageName]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil dibuat',
            'data' => [
                'id' => $qrCode->id,
                'code' => $code,
                'qr_image_url' => Storage::url($imageName),
                'class_name' => $class->class_name,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error quick generating QR code', [
            'error' => $e->getMessage(),
            'class_id' => $classId
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Export attendance to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:classes,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $query = Attendance::whereHas('class', function($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->with(['student', 'class']);
        
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        $query->whereBetween('attendance_date', [$request->start_date, $request->end_date]);
        
        $attendances = $query->get();
        
        if ($attendances->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada data absensi untuk diexport.');
        }
        
        $filename = 'absensi-' . now()->format('Y-m-d-H-i-s');
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Tanggal',
                'NIS/NIP',
                'Nama Siswa',
                'Kelas',
                'Status',
                'Waktu Absen',
                'Keterangan'
            ], ',');
            
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    Carbon::parse($attendance->attendance_date)->format('d/m/Y'),
                    $attendance->student->nis_nip ?? '-',
                    $attendance->student->name ?? '-',
                    $attendance->class->class_name ?? '-',
                    $this->getStatusText($attendance->status),
                    $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->format('H:i:s') : '-',
                    $attendance->notes ?? '-'
                ], ',');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =================== PRIVATE METHODS ===================

    /**
     * Generate unique QR code
     */
    private function generateUniqueCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $exists = QRCode::where('code', $code)->exists();
        } while ($exists);
        
        return $code;
    }

    /**
     * Generate QR code image
     */
    private function generateQrCodeImage($url, $code = null)
    {
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $imageData) {
                return $imageData;
            }
            
            throw new \Exception('Failed to fetch QR code');
        } catch (\Exception $e) {
            // Fallback: create simple image
            $size = 300;
            $image = imagecreatetruecolor($size, $size);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $gray = imagecolorallocate($image, 100, 100, 100);
            
            imagefilledrectangle($image, 0, 0, $size, $size, $white);
            imagerectangle($image, 5, 5, $size-5, $size-5, $gray);
            
            $font = 5;
            $text = "QR CODE";
            $textWidth = imagefontwidth($font) * strlen($text);
            $textX = ($size - $textWidth) / 2;
            $textY = ($size / 2) - 20;
            imagestring($image, $font, $textX, $textY, $text, $black);
            
            if ($code) {
                $codeText = $code;
                $codeWidth = imagefontwidth($font) * strlen($codeText);
                $codeX = ($size - $codeWidth) / 2;
                $codeY = $textY + 30;
                imagestring($image, $font, $codeX, $codeY, $codeText, $gray);
            }
            
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);
            
            return $imageData;
        }
    }

/**
 * Validasi QR Code - FIXED
 */
private function validateQrCode($qrCode, $user)
{
    // Cek apakah QR code ada
    if (!$qrCode) {
        return [
            'valid' => false,
            'message' => 'QR Code tidak ditemukan.'
        ];
    }

    // Cek status aktif
    if (!$qrCode->is_active) {
        return [
            'valid' => false,
            'message' => 'QR Code tidak aktif. Silakan hubungi guru.'
        ];
    }

    // Cek tanggal
    if (!$qrCode->date->isToday()) {
        return [
            'valid' => false,
            'message' => 'QR Code hanya berlaku untuk tanggal: ' . $qrCode->date->format('d F Y')
        ];
    }

    // Cek waktu
    $currentTime = now();
    $startTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->start_time);
    $endTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->end_time);

    if ($currentTime < $startTime) {
        return [
            'valid' => false,
            'message' => 'QR Code belum aktif. Akan aktif pada: ' . $startTime->format('H:i')
        ];
    }

    if ($currentTime > $endTime) {
        return [
            'valid' => false,
            'message' => 'QR Code sudah kadaluarsa. Berakhir pada: ' . $endTime->format('H:i')
        ];
    }

    // Cek keanggotaan kelas
    $isInClass = $qrCode->class->students()->where('users.id', $user->id)->exists();
    
    if (!$isInClass) {
        return [
            'valid' => false,
            'message' => 'Anda tidak terdaftar di kelas ini. Silakan hubungi guru Anda.'
        ];
    }

    return ['valid' => true, 'message' => 'Valid'];
}

    /**
     * Get status text in Indonesian
     */
    private function getStatusText($status)
    {
        $statuses = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin'
        ];
        
        return $statuses[$status] ?? $status;
    }
    public function bulkDelete(Request $request)
{
    $ids = $request->ids;
    
    if (empty($ids) || !is_array($ids)) {
        return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
    }
    
    try {
        $deletedCount = Attendance::whereIn('id', $ids)->delete();
        return response()->json(['success' => true, 'message' => "{$deletedCount} data berhasil dihapus.", 'deleted_count' => $deletedCount]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
    }
}
}