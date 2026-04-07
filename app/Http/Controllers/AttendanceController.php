<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\QRCode;
use App\Models\QrCode as QrCodeModel; // Alias untuk menghindari konflik
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    // =================== ABSENSI KELAS (UNTUK GURU) ===================

    /**
     * Menampilkan halaman absensi untuk kelas tertentu
     */
    public function classAttendance($classId)
    {
        $user = Auth::user();
        
        // Ambil kelas
        $class = ClassModel::with('students')->findOrFail($classId);
        
        // Cek apakah user adalah guru kelas ini
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke absensi kelas ini.');
        }

        $students = $class->students;
        $today = Carbon::today()->toDateString();
        
        // Ambil absensi hari ini
        $todayAttendance = Attendance::whereDate('attendance_date', $today)
            ->whereIn('student_id', $students->pluck('id'))
            ->where('class_id', $classId)
            ->get()
            ->keyBy('student_id');

        return view('attendance.class', compact('class', 'students', 'todayAttendance', 'today'));
    }

    /**
     * Menampilkan riwayat absensi siswa dalam kelas
     */
    public function studentAttendance($classId, $studentId)
    {
        $user = Auth::user();
        
        $class = ClassModel::findOrFail($classId);
        $student = User::findOrFail($studentId);
        
        // Cek apakah user adalah guru kelas ini
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke data absensi ini.');
        }

        // Cek apakah siswa berada di kelas ini
        if (!$class->students()->where('users.id', $student->id)->exists()) {
            abort(404, 'Siswa tidak ditemukan di kelas ini.');
        }

        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->where('class_id', $classId)
            ->orderBy('attendance_date', 'desc')
            ->paginate(20);

        return view('attendance.student', compact('class', 'student', 'attendanceRecords'));
    }

    /**
     * Menandai absensi (untuk guru) - AJAX
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
        
        // Cek apakah kelas dimiliki oleh guru yang login
        $class = ClassModel::find($request->class_id);
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Cek apakah siswa berada di kelas
        if (!$class->students()->where('users.id', $request->student_id)->exists()) {
            return response()->json(['error' => 'Siswa tidak ditemukan di kelas ini'], 404);
        }

        // Cek apakah absensi sudah ada
        $attendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->whereDate('attendance_date', $request->date)
            ->first();

        if ($attendance) {
            // Update absensi yang sudah ada
            $attendance->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'marked_by' => $user->id
            ]);
            $message = 'Absensi berhasil diperbarui';
        } else {
            // Buat absensi baru
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
     * Dashboard Absensi Guru
     */
    public function indexTeacher(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        // Get classes taught by teacher
        $classes = ClassModel::where('teacher_id', $user->id)
            ->withCount('students')
            ->get();
        
        // Base query for attendance
        $query = Attendance::with(['student', 'class'])
            ->whereHas('class', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        
        // Apply filters
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }
        
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('attendance_date', [$request->start_date, $request->end_date]);
        }
        
        // Today's stats
        $todayStats = Attendance::whereHas('class', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->whereDate('attendance_date', now())
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission
            ")
            ->first();
        
        // Calculate percentage
        if ($todayStats) {
            $todayStats->percentage = $todayStats->total > 0 
                ? round((($todayStats->present + $todayStats->late) / $todayStats->total) * 100) 
                : 0;
        } else {
            $todayStats = (object) [
                'total' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'sick' => 0,
                'permission' => 0,
                'percentage' => 0
            ];
        }
        
        // Get active QR codes for this teacher
        $activeQrCodes = QRCode::whereHas('class', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->where('is_active', true)
            ->whereDate('date', today())
            ->where('end_time', '>', now()->format('H:i:s'))
            ->with('class')
            ->get();
        
        // Paginate attendance records
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(15);
        
        return view('attendance.teacher-index', compact(
            'classes',
            'todayStats',
            'activeQrCodes',
            'attendances'
        ));
    }

    /**
     * Buat absensi manual (guru)
     */
    public function manualEntry(Request $request)
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Cek apakah guru mengajar kelas ini
        $class = ClassModel::find($request->class_id);
        if (!$class || ($class->teacher_id !== $user->id && $user->role !== 'admin')) {
            return redirect()->back()
                ->with('error', 'Anda tidak mengajar kelas ini.');
        }
        
        // Cek apakah siswa berada di kelas ini
        $isInClass = $class->students()->where('users.id', $request->student_id)->exists();
        
        if (!$isInClass) {
            return redirect()->back()
                ->with('error', 'Siswa tidak terdaftar di kelas ini.');
        }
        
        // Format checked_in_at
        $checkedInAt = null;
        if ($request->checked_in_at) {
            $checkedInAt = Carbon::parse($request->attendance_date . ' ' . $request->checked_in_at);
        } elseif (in_array($request->status, ['present', 'late'])) {
            $checkedInAt = Carbon::now();
        }
        
        // Cek apakah sudah ada absensi untuk hari ini
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();
        
        if ($existingAttendance) {
            // Update existing attendance
            $existingAttendance->update([
                'status' => $request->status,
                'checked_in_at' => $checkedInAt,
                'notes' => $request->notes,
            ]);
            
            $message = 'Absensi berhasil diperbarui.';
        } else {
            // Create new attendance
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
        
        return redirect()->route('attendance.teacher.index')
            ->with('success', $message);
    }

    /**
     * Edit absensi
     */
    public function edit(Attendance $attendance)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($attendance->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $classes = ClassModel::where('teacher_id', $user->id)->get();
        $students = $attendance->class->students;
        
        // Get QR codes for this class
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
        
        // Authorization check
        if ($attendance->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'student_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,late,absent,sick,permission',
            'checked_in_at' => 'nullable|date_format:H:i:s',
            'qr_code_id' => 'nullable|exists:qr_codes,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Format checked_in_at
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
            'qr_code_id' => $request->qr_code_id,
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('attendance.teacher.index')
            ->with('success', 'Absensi berhasil diperbarui.');
    }

    /**
     * Hapus absensi
     */
    public function destroy(Attendance $attendance)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($attendance->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $attendance->delete();
        
        return redirect()->route('attendance.teacher.index')
            ->with('success', 'Absensi berhasil dihapus.');
    }

    /**
     * Export data absensi
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Query data
        $query = Attendance::whereHas('class', function($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->with(['student', 'class']);
        
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        $query->whereBetween('attendance_date', [
            $request->start_date,
            $request->end_date
        ]);
        
        $attendances = $query->get();
        
        if ($attendances->isEmpty()) {
            return redirect()->back()
                ->with('warning', 'Tidak ada data absensi untuk diexport.');
        }
        
        // Generate filename
        $filename = 'absensi-' . now()->format('Y-m-d-H-i-s');
        
        return $this->exportToCsv($attendances, $filename);
    }

    /**
     * Detail kelas absensi
     */
    public function showClass($classId, Request $request)
    {
        $user = Auth::user();
        
        $class = ClassModel::with('students')->findOrFail($classId);
        
        // Authorization check
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        // Filter bulan/tahun
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        // Generate semua tanggal dalam bulan
        $dates = collect();
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->push($date->copy());
        }
        
        // Ambil semua siswa
        $students = $class->students()->with(['attendances' => function($q) use ($month, $year, $classId) {
            $q->whereMonth('attendance_date', $month)
              ->whereYear('attendance_date', $year)
              ->where('class_id', $classId);
        }])->get();
        
        // Hitung statistik
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
            'students',
            'dates',
            'totalStats',
            'totalSessions',
            'attendanceRate',
            'month',
            'year'
        ));
    }

    // =================== ABSENSI SISWA ===================

    /**
     * Dashboard Absensi Siswa
     */
    public function indexStudent(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        // Filter berdasarkan bulan
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        // Ambil semua kelas siswa
        $classes = $user->studentClasses()
            ->with('teacher')
            ->withCount('students')
            ->get();
        
        // Statistik bulan ini
        $monthStats = $this->getStudentMonthlyStats($user, $month, $year);
        
        // Riwayat absensi bulan ini
        $attendances = Attendance::where('student_id', $user->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['class', 'qrCode'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('checked_in_at', 'desc')
            ->paginate(10);
        
        // Get today's attendance status
        $todayAttendance = Attendance::where('student_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();
        
        // Get active QR codes for student's classes
        $recentQrCodes = QRCode::whereHas('class', function($query) use ($user) {
                $query->whereHas('students', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->where('is_active', true)
            ->whereDate('date', today())
            ->where('end_time', '>', now()->format('H:i:s'))
            ->with('class')
            ->get();
        
        return view('attendance.student-index', compact(
            'classes',
            'monthStats',
            'attendances',
            'month',
            'year',
            'todayAttendance',
            'recentQrCodes'
        ));
    }

    /**
     * Detail absensi siswa
     */
    public function showStudent(Attendance $attendance)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'student' && $attendance->student_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat absensi Anda sendiri.');
        }
        
        $attendance->load(['class', 'student', 'qrCode']);
        
        return view('attendance.student-show', compact('attendance'));
    }

    /**
     * Statistik absensi siswa
     */
    public function studentStatistics(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        // Filter tahun
        $year = $request->get('year', now()->year);
        
        // Statistik per bulan
        $monthlyStats = [];
        for ($month = 1; $month <= 12; $month++) {
            $stats = $this->getStudentMonthlyStats($user, $month, $year);
            $monthlyStats[] = [
                'month' => Carbon::create()->month($month)->translatedFormat('F'),
                'stats' => $stats
            ];
        }
        
        // Total statistik tahun ini
        $yearStats = Attendance::where('student_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission
            ")
            ->first();
        
        if ($yearStats->total > 0) {
            $yearStats->attendance_rate = round((($yearStats->present + $yearStats->late) / $yearStats->total) * 100, 1);
        } else {
            $yearStats->attendance_rate = 0;
        }
        
        return view('attendance.student-statistics', compact(
            'monthlyStats',
            'yearStats',
            'year'
        ));
    }

    // =================== HELPER METHODS ===================

    /**
     * Get student monthly statistics
     */
    private function getStudentMonthlyStats($user, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $stats = Attendance::where('student_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $total = array_sum($stats);
        $presentCount = ($stats['present'] ?? 0) + ($stats['late'] ?? 0);
        $percentage = $total > 0 ? round(($presentCount / $total) * 100) : 0;
        
        return [
            'present' => $stats['present'] ?? 0,
            'late' => $stats['late'] ?? 0,
            'absent' => $stats['absent'] ?? 0,
            'sick' => $stats['sick'] ?? 0,
            'permission' => $stats['permission'] ?? 0,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($attendances, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Header row
            fputcsv($file, [
                'Tanggal',
                'NIS/NIP',
                'Nama Siswa',
                'Kelas',
                'Status',
                'Waktu Absen',
                'Keterangan'
            ], ',');
            
            // Data rows
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

    /**
     * Halaman scan QR code untuk siswa
     */
    public function scanPage(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Hanya siswa yang dapat melakukan scan QR Code.');
        }
        
        // Get QR code from query parameter if provided
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
        
        // If specific QR code provided in URL, check it
        $specificQrCode = null;
        if ($qrCodeParam) {
            $specificQrCode = QRCode::where('code', $qrCodeParam)
                ->where('is_active', true)
                ->whereDate('date', today())
                ->where('end_time', '>', now()->format('H:i:s'))
                ->first();
            
            if ($specificQrCode) {
                // Check if student is in the class
                $isInClass = $specificQrCode->class->students()
                    ->where('users.id', $user->id)
                    ->exists();
                
                if (!$isInClass) {
                    $specificQrCode = null;
                }
            }
        }
        
        // Get today's attendance status
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

    public function scanQrCode($code)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return redirect()->route('dashboard')
                ->with('error', 'Hanya siswa yang dapat melakukan scan QR Code.');
        }
        
        try {
            // Find QR code
            $qrCode = QRCode::where('code', $code)
                ->where('is_active', true)
                ->first();
            
            if (!$qrCode) {
                return view('attendance.scan-error', [
                    'message' => 'QR Code tidak ditemukan atau sudah tidak aktif.'
                ]);
            }
            
            // Check if date is today
            if (!$qrCode->date->isToday()) {
                return view('attendance.scan-error', [
                    'message' => 'QR Code ini hanya dapat digunakan pada tanggal ' . 
                                 $qrCode->date->format('d F Y') . '.'
                ]);
            }
            
            // Check if current time is within valid range
            $currentTime = Carbon::now();
            $startDateTime = Carbon::parse($qrCode->date . ' ' . $qrCode->start_time);
            $endDateTime = Carbon::parse($qrCode->date . ' ' . $qrCode->end_time);
            
            if ($currentTime < $startDateTime) {
                return view('attendance.scan-error', [
                    'message' => 'QR Code ini belum dapat digunakan. Akan aktif pada ' . 
                                 $startDateTime->format('H:i') . '.'
                ]);
            }
            
            if ($currentTime > $endDateTime) {
                return view('attendance.scan-error', [
                    'message' => 'QR Code ini sudah kadaluarsa. Berakhir pada ' . 
                                 $endDateTime->format('H:i') . '.'
                ]);
            }
            
            // Check if student is in the class
            $isInClass = $qrCode->class->students()->where('users.id', $user->id)->exists();
            
            if (!$isInClass) {
                return view('attendance.scan-error', [
                    'message' => 'Anda tidak terdaftar di kelas ini.'
                ]);
            }
            
            // Check if already attended
            $existingAttendance = Attendance::where('student_id', $user->id)
                ->where('qr_code_id', $qrCode->id)
                ->first();
            
            if ($existingAttendance) {
                return view('attendance.scan-result', [
                    'qrCode' => $qrCode,
                    'attendance' => $existingAttendance,
                    'message' => 'Anda sudah melakukan absensi untuk sesi ini.',
                    'is_already_attended' => true
                ]);
            }
            
            return view('attendance.scan', compact('qrCode'));
            
        } catch (\Exception $e) {
            Log::error('Error loading QR code scan page', [
                'error' => $e->getMessage(),
                'code' => $code
            ]);
            
            return view('attendance.scan-error', [
                'message' => 'Terjadi kesalahan saat memproses QR Code.'
            ]);
        }
    }

// AttendanceController.php - Perbaiki method processScan

public function processScan($code, Request $request)
{
    DB::beginTransaction();
    
    try {
        Log::info('Processing attendance for QR code', [
            'code' => $code,
            'user_id' => Auth::id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);
        
        $qrCode = QRCode::where('code', $code)->first();
        
        if (!$qrCode) {
            return redirect()->back()->with('error', 'QR Code tidak ditemukan.');
        }
        
        // Cek validitas QR Code - PERBAIKI PARSING DATETIME
        if (!$qrCode->is_active) {
            return redirect()->back()->with('error', 'QR Code tidak aktif.');
        }
        
        // Cek apakah QR Code sudah expired
        $currentTime = now();
        $endDateTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->end_time);
        
        if ($currentTime > $endDateTime) {
            return redirect()->back()->with('error', 'QR Code sudah kadaluarsa.');
        }
        
        // Cek apakah QR Code sudah aktif
        $startDateTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->start_time);
        
        if ($currentTime < $startDateTime) {
            return redirect()->back()->with('error', 'QR Code belum aktif. Akan aktif pada: ' . $startDateTime->format('H:i'));
        }
        
        // Cek apakah sudah absen
        $existingAttendance = Attendance::where('qr_code_id', $qrCode->id)
            ->where('student_id', Auth::id())
            ->first();
            
        if ($existingAttendance) {
            return redirect()->route('attendance.student.show', $existingAttendance->id)
                ->with('info', 'Anda sudah melakukan absensi untuk QR Code ini.');
        }
        
        // Buat absensi baru
        $attendance = Attendance::create([
            'student_id' => Auth::id(),
            'class_id' => $qrCode->class_id,
            'qr_code_id' => $qrCode->id,
            'attendance_date' => $qrCode->date,
            'checked_in_at' => now(),
            'status' => $request->status ?? 'present',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'notes' => $request->notes,
            'marked_by' => Auth::id(),
        ]);
        
        // Update scan count
        $qrCode->increment('scan_count');
        
        DB::commit();
        
        Log::info('Attendance created successfully', [
            'attendance_id' => $attendance->id,
            'qr_code_id' => $qrCode->id
        ]);
        
        return redirect()->route('attendance.student.show', $attendance->id)
            ->with('success', 'Absensi berhasil dicatat!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Error processing attendance', [
            'code' => $code,
            'user_id' => Auth::id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Terjadi kesalahan saat memproses absensi: ' . $e->getMessage())
            ->withInput();
    }
}

    public function createManual(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        // Get teacher's classes
        $classes = ClassModel::where('teacher_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        // Set default date to today
        $defaultDate = now()->format('Y-m-d');
        $defaultTime = now()->format('H:i');
        
        // Get students if class is selected
        $students = collect();
        if ($request->has('class_id')) {
            $class = ClassModel::find($request->class_id);
            if ($class && $class->teacher_id === $user->id) {
                $students = $class->students()
                    ->orderBy('name')
                    ->get();
            }
        }
        
        return view('attendance.manual-create', compact(
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Cek apakah guru mengajar kelas ini
        $class = ClassModel::find($request->class_id);
        if (!$class || ($class->teacher_id !== $user->id && $user->role !== 'admin')) {
            return redirect()->back()
                ->with('error', 'Anda tidak mengajar kelas ini.')
                ->withInput();
        }
        
        // Cek apakah siswa berada di kelas ini
        $isInClass = $class->students()->where('users.id', $request->student_id)->exists();
        
        if (!$isInClass) {
            return redirect()->back()
                ->with('error', 'Siswa tidak terdaftar di kelas ini.')
                ->withInput();
        }
        
        // Format checked_in_at
        $checkedInAt = null;
        if ($request->checked_in_at) {
            $checkedInAt = Carbon::parse($request->attendance_date . ' ' . $request->checked_in_at);
        } elseif (in_array($request->status, ['present', 'late'])) {
            $checkedInAt = Carbon::now();
        }
        
        // Cek apakah sudah ada absensi untuk hari ini
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();
        
        if ($existingAttendance) {
            // Update existing attendance
            $existingAttendance->update([
                'status' => $request->status,
                'checked_in_at' => $checkedInAt,
                'notes' => $request->notes,
                'marked_by' => $user->id,
            ]);
            
            $message = 'Absensi berhasil diperbarui.';
        } else {
            // Create new attendance
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
        
        // Redirect based on request
        if ($request->has('submit_and_new')) {
            return redirect()->route('attendance.manual.create')
                ->with('success', $message . ' Silakan tambah absensi lainnya.');
        }
        
        return redirect()->route('attendance.teacher.index')
            ->with('success', $message);
    }

    /**
     * Get students for selected class (AJAX)
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
        
        // Cek apakah guru mengajar kelas ini
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
                    'text' => $student->name . ' (' . $student->nis_nip . ')'
                ];
            });
        
        return response()->json($students);
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
        
        // Get teacher's classes
        $classes = ClassModel::where('teacher_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        // Set default date to today
        $defaultDate = now()->format('Y-m-d');
        
        return view('attendance.manual-bulk', compact('classes', 'defaultDate'));
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
            'attendance_data.*.checked_in_at' => 'nullable|date_format:H:i',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Cek apakah guru mengajar kelas ini
        $class = ClassModel::find($request->class_id);
        if (!$class || ($class->teacher_id !== $user->id && $user->role !== 'admin')) {
            return redirect()->back()
                ->with('error', 'Anda tidak mengajar kelas ini.')
                ->withInput();
        }
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($request->attendance_data as $index => $data) {
                // Cek apakah siswa berada di kelas ini
                $isInClass = $class->students()->where('users.id', $data['student_id'])->exists();
                
                if (!$isInClass) {
                    $errors[] = "Siswa ID {$data['student_id']} tidak terdaftar di kelas ini.";
                    $errorCount++;
                    continue;
                }
                
                // Format checked_in_at
                $checkedInAt = null;
                if (!empty($data['checked_in_at'])) {
                    $checkedInAt = Carbon::parse($request->attendance_date . ' ' . $data['checked_in_at']);
                } elseif (in_array($data['status'], ['present', 'late'])) {
                    $checkedInAt = Carbon::now();
                }
                
                // Cek apakah sudah ada absensi untuk hari ini
                $existingAttendance = Attendance::where('student_id', $data['student_id'])
                    ->where('class_id', $request->class_id)
                    ->whereDate('attendance_date', $request->attendance_date)
                    ->first();
                
                if ($existingAttendance) {
                    // Update existing attendance
                    $existingAttendance->update([
                        'status' => $data['status'],
                        'checked_in_at' => $checkedInAt,
                        'marked_by' => $user->id,
                    ]);
                } else {
                    // Create new attendance
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
            
            return redirect()->route('attendance.teacher.index')
                ->with('success', $message)
                ->with('errors', $errors);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error storing bulk attendance', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'class_id' => $request->class_id
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan absensi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Proses absensi dari scan QR Code
     */
    public function processAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|exists:qr_codes,code',
            'student_id' => 'required|exists:users,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        DB::beginTransaction();

        try {
            $user = Auth::user();
            
            // Cek apakah user adalah siswa
            if ($user->role !== 'student') {
                return response()->json(['error' => 'Hanya siswa yang dapat melakukan absensi'], 403);
            }

            // Cek apakah student_id sesuai dengan user yang login
            if ($user->id != $request->student_id) {
                return response()->json(['error' => 'ID siswa tidak sesuai'], 403);
            }

            // Cari QR Code
            $qrCode = QRCode::where('code', $request->qr_code)
                ->where('is_active', true)
                ->first();

            if (!$qrCode) {
                return response()->json(['error' => 'QR Code tidak valid atau tidak aktif'], 404);
            }

            // Cek tanggal
            $today = now()->toDateString();
            $qrDate = $qrCode->date->toDateString();
            
            if ($today != $qrDate) {
                return response()->json([
                    'error' => 'QR Code hanya berlaku untuk tanggal: ' . $qrCode->date->format('d-m-Y')
                ], 400);
            }

            // Cek waktu
            $currentTime = now();
            $startTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->start_time);
            $endTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->end_time);

            if ($currentTime < $startTime) {
                return response()->json([
                    'error' => 'QR Code belum aktif. Akan aktif pada: ' . $startTime->format('H:i')
                ], 400);
            }

            if ($currentTime > $endTime) {
                return response()->json([
                    'error' => 'QR Code sudah kadaluarsa. Berakhir pada: ' . $endTime->format('H:i')
                ], 400);
            }

            // Cek apakah siswa berada di kelas tersebut
            $isInClass = $qrCode->class->students()->where('users.id', $user->id)->exists();
            
            if (!$isInClass) {
                return response()->json(['error' => 'Anda tidak terdaftar di kelas ini'], 403);
            }

            // Cek apakah sudah absen hari ini
            $existingAttendance = Attendance::where('student_id', $user->id)
                ->where('class_id', $qrCode->class_id)
                ->whereDate('attendance_date', $today)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'error' => 'Anda sudah melakukan absensi hari ini',
                    'data' => $existingAttendance
                ], 400);
            }

            // Tentukan status (present atau late)
            $status = 'present';
            
            // Jika ada waktu toleransi, bisa ditambahkan logika terlambat
            $toleranceMinutes = 15; // toleransi 15 menit
            $lateTime = $startTime->copy()->addMinutes($toleranceMinutes);
            
            if ($currentTime > $lateTime && $currentTime <= $endTime) {
                $status = 'late';
            }

            // Buat absensi
            $attendance = Attendance::create([
                'student_id' => $user->id,
                'class_id' => $qrCode->class_id,
                'qr_code_id' => $qrCode->id,
                'attendance_date' => $today,
                'status' => $status,
                'checked_in_at' => $currentTime,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'marked_by' => $user->id, // self-marked
            ]);

            // Update scan count
            $qrCode->increment('scan_count');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'student_name' => $user->name,
                    'class_name' => $qrCode->class->class_name,
                    'status' => $this->getStatusText($attendance->status),
                    'time' => $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->format('H:i:s') : '-',
                    'date' => $attendance->attendance_date->format('d-m-Y'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses absensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk scan QR Code (mobile/siswa)
     */
    public function apiScan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $qrCode = QRCode::where('code', $request->qr_code)
            ->where('is_active', true)
            ->first();

        if (!$qrCode) {
            return response()->json(['error' => 'QR Code tidak ditemukan'], 404);
        }

        // Cek validitas QR Code
        $currentTime = now();
        $startDateTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->start_time);
        $endDateTime = Carbon::parse($qrCode->date->format('Y-m-d') . ' ' . $qrCode->end_time);

        if ($currentTime < $startDateTime) {
            return response()->json([
                'error' => 'QR Code belum aktif',
                'will_active_at' => $startDateTime->format('Y-m-d H:i:s')
            ], 400);
        }

        if ($currentTime > $endDateTime) {
            return response()->json([
                'error' => 'QR Code sudah kadaluarsa',
                'expired_at' => $endDateTime->format('Y-m-d H:i:s')
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => $qrCode->code,
                'class_id' => $qrCode->class_id,
                'class_name' => $qrCode->class->class_name,
                'teacher_name' => $qrCode->class->teacher->name ?? 'N/A',
                'date' => $qrCode->date->format('d-m-Y'),
                'time_range' => $qrCode->start_time . ' - ' . $qrCode->end_time,
                'location_restricted' => $qrCode->location_restricted,
                'notes' => $qrCode->notes,
            ]
        ]);
    }

    public function scanProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'student_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = Auth::user();
            $qrCode = $request->qr_code;
            
            // Find QR Code
            $qrCodeRecord = QRCode::where('code', $qrCode)
                ->with('class')
                ->first();

            if (!$qrCodeRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid'
                ], 404);
            }

            // Check if QR Code is active
            if (!$qrCodeRecord->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak aktif'
                ], 400);
            }

            // Check date
            $today = now()->toDateString();
            if ($qrCodeRecord->date->toDateString() != $today) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code hanya berlaku untuk tanggal: ' . $qrCodeRecord->date->format('d/m/Y')
                ], 400);
            }

            // Check time
            $currentTime = now();
            $startTime = Carbon::parse($qrCodeRecord->date->format('Y-m-d') . ' ' . $qrCodeRecord->start_time);
            $endTime = Carbon::parse($qrCodeRecord->date->format('Y-m-d') . ' ' . $qrCodeRecord->end_time);

            if ($currentTime < $startTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code belum aktif. Aktif pada: ' . $startTime->format('H:i')
                ], 400);
            }

            if ($currentTime > $endTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code sudah kadaluarsa. Berakhir pada: ' . $endTime->format('H:i')
                ], 400);
            }

            // Check if student is in the class
            $isInClass = $qrCodeRecord->class->students()
                ->where('users.id', $request->student_id)
                ->exists();

            if (!$isInClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak terdaftar di kelas ini'
                ], 403);
            }

            // Check if already attended today
            $existingAttendance = Attendance::where('student_id', $request->student_id)
                ->where('class_id', $qrCodeRecord->class_id)
                ->whereDate('attendance_date', $today)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi hari ini',
                    'data' => [
                        'attendance_id' => $existingAttendance->id,
                        'status' => $this->getStatusText($existingAttendance->status),
                        'time' => $existingAttendance->checked_in_at ? Carbon::parse($existingAttendance->checked_in_at)->format('H:i:s') : '-'
                    ]
                ], 400);
            }

            // Determine status
            $status = 'present';
            $toleranceMinutes = 15;
            $lateTime = $startTime->copy()->addMinutes($toleranceMinutes);
            
            if ($currentTime > $lateTime && $currentTime <= $endTime) {
                $status = 'late';
            }

            // Create attendance
            $attendance = Attendance::create([
                'student_id' => $request->student_id,
                'class_id' => $qrCodeRecord->class_id,
                'qr_code_id' => $qrCodeRecord->id,
                'attendance_date' => $today,
                'status' => $status,
                'checked_in_at' => $currentTime,
                'marked_by' => $user->id,
            ]);

            // Update scan count
            $qrCodeRecord->increment('scan_count');

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'student_name' => $user->name,
                    'class_name' => $qrCodeRecord->class->class_name,
                    'status' => $this->getStatusText($attendance->status),
                    'time' => $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->format('H:i:s') : '-',
                    'date' => $attendance->attendance_date->format('d/m/Y'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing QR code scan', [
                'error' => $e->getMessage(),
                'qr_code' => $request->qr_code,
                'student_id' => $request->student_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman real-time attendance untuk guru
     */
    public function realtimeAttendance($qrCodeId)
    {
        $user = Auth::user();
        $qrCode = QRCode::with(['class', 'class.students', 'attendances.student'])
            ->findOrFail($qrCodeId);

        // Authorization check
        if ($qrCode->class->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // Get all students in the class with their attendance status
        $students = $qrCode->class->students()
            ->with(['attendances' => function($q) use ($qrCode) {
                $q->where('qr_code_id', $qrCode->id);
            }])
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $totalStudents = $students->count();
        $attendedStudents = $qrCode->attendances()->count();
        $attendancePercentage = $totalStudents > 0 
            ? round(($attendedStudents / $totalStudents) * 100, 1) 
            : 0;

        return view('attendance.realtime', compact(
            'qrCode',
            'students',
            'totalStudents',
            'attendedStudents',
            'attendancePercentage'
        ));
    }

    /**
     * API untuk data real-time (AJAX)
     */
    public function getRealtimeData($qrCodeId)
    {
        $user = Auth::user();
        $qrCode = QRCode::with(['class', 'attendances.student'])
            ->findOrFail($qrCodeId);

        // Authorization check
        if ($qrCode->class->teacher_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $students = $qrCode->class->students()
            ->with(['attendances' => function($q) use ($qrCode) {
                $q->where('qr_code_id', $qrCode->id);
            }])
            ->orderBy('name')
            ->get()
            ->map(function($student) {
                $attendance = $student->attendances->first();
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nis_nip' => $student->nis_nip,
                    'has_attended' => $attendance ? true : false,
                    'attendance_status' => $attendance ? $this->getStatusText($attendance->status) : 'Belum Absen',
                    'attendance_time' => $attendance && $attendance->checked_in_at ? 
                        Carbon::parse($attendance->checked_in_at)->format('H:i:s') : null,
                    'status_color' => $attendance ? $this->getStatusColor($attendance->status) : 'secondary',
                ];
            });

        $totalStudents = $students->count();
        $attendedStudents = $students->where('has_attended', true)->count();
        $attendancePercentage = $totalStudents > 0 
            ? round(($attendedStudents / $totalStudents) * 100, 1) 
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => [
                    'id' => $qrCode->id,
                    'code' => $qrCode->code,
                    'class_name' => $qrCode->class->class_name,
                    'date' => $qrCode->date->format('d F Y'),
                    'time_range' => $qrCode->start_time . ' - ' . $qrCode->end_time,
                    'time_remaining' => $this->getTimeRemaining($qrCode),
                    'is_active' => $qrCode->is_active && $this->isQrCodeActiveNow($qrCode),
                ],
                'students' => $students,
                'statistics' => [
                    'total_students' => $totalStudents,
                    'attended_students' => $attendedStudents,
                    'remaining_students' => $totalStudents - $attendedStudents,
                    'attendance_percentage' => $attendancePercentage,
                ],
                'timestamp' => now()->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Helper untuk waktu remaining
     */
    private function getTimeRemaining($qrCode)
    {
        $endDateTime = Carbon::parse($qrCode->date . ' ' . $qrCode->end_time);
        $currentTime = now();
        
        if ($currentTime > $endDateTime) {
            return 'Berakhir';
        }
        
        $diffInMinutes = $currentTime->diffInMinutes($endDateTime);
        
        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' menit lagi';
        } else {
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;
            return $hours . ' jam ' . $minutes . ' menit lagi';
        }
    }

    /**
     * Helper untuk cek apakah QR code aktif sekarang
     */
    private function isQrCodeActiveNow($qrCode)
    {
        $currentTime = now();
        $startDateTime = Carbon::parse($qrCode->date . ' ' . $qrCode->start_time);
        $endDateTime = Carbon::parse($qrCode->date . ' ' . $qrCode->end_time);
        
        return $currentTime >= $startDateTime && $currentTime <= $endDateTime;
    }

    /**
     * Helper untuk warna status
     */
    private function getStatusColor($status)
    {
        $colors = [
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'sick' => 'info',
            'permission' => 'secondary',
        ];
        
        return $colors[$status] ?? 'secondary';
    }
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

    // Authorization check
    if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak mengajar kelas ini'
        ], 403);
    }

    DB::beginTransaction();

    try {
        // Generate unique code
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Set waktu: 30 menit dari sekarang
        $startTime = now();
        $endTime = now()->addMinutes(30);

        // Create QR code
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

        // Generate QR code URL
        $url = url('/attendance/scan-page') . '?qr_code=' . $code;
        
        // Generate QR code image
        $qrCodeImage = $this->generateQrCodeImage($url, $code);

        $imageName = 'qr-codes/quick-' . $code . '.png';
        
        // Ensure directory exists
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
                'realtime_url' => route('attendance.realtime', $qrCode->id),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error quick generating QR code', [
            'error' => $e->getMessage(),
            'class_id' => $classId,
            'user_id' => $user->id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
        ], 500);
    }
}
    private function generateQrCodeImage($url, $code = null)
    {
        // Gunakan Google Charts API untuk generate QR code
        $googleChartsUrl = 'https://chart.googleapis.com/chart?' . http_build_query([
            'chs' => '300x300',
            'cht' => 'qr',
            'chl' => urlencode($url),
            'choe' => 'UTF-8',
        ]);
        
        try {
            $imageData = file_get_contents($googleChartsUrl);
            if ($imageData === false) {
                throw new \Exception('Failed to fetch QR code image');
            }
            return $imageData;
        } catch (\Exception $e) {
            // Fallback: create simple image with GD
            $size = 300;
            $image = imagecreatetruecolor($size, $size);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            
            imagefilledrectangle($image, 0, 0, $size, $size, $white);
            
            // Add border
            imagerectangle($image, 5, 5, $size-5, $size-5, $black);
            
            // Add text
            $text = "QR CODE\nKode: " . ($code ?: 'N/A');
            $lines = explode("\n", $text);
            $font = 5;
            $lineHeight = 30;
            $startY = ($size - (count($lines) * $lineHeight)) / 2;
            
            foreach ($lines as $i => $line) {
                $textWidth = imagefontwidth($font) * strlen($line);
                $x = ($size - $textWidth) / 2;
                $y = $startY + ($i * $lineHeight);
                imagestring($image, $font, $x, $y, $line, $black);
            }
            
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);
            
            return $imageData;
        }
    }
}