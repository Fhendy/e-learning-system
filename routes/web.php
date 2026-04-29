<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// =================== PUBLIC ROUTES ===================
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// =================== PROTECTED ROUTES ===================
Route::middleware('auth')->group(function () {
    
    // =================== AUTH ROUTES ===================
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // =================== PROFILE ROUTES ===================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });
    
    // =================== DASHBOARD ROUTES ===================
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if (in_array($user->role, ['admin', 'teacher', 'guru'])) {
            return redirect()->route('dashboard.teacher');
        } else {
            return redirect()->route('dashboard.student');
        }
    })->name('dashboard');
    
    Route::get('/home', function () {
        $user = auth()->user();
        
        if (in_array($user->role, ['admin', 'teacher', 'guru'])) {
            return redirect()->route('dashboard.teacher');
        } else {
            return redirect()->route('dashboard.student');
        }
    })->name('home');
    
    // Student Dashboard
    Route::get('/dashboard/student', [DashboardController::class, 'studentDashboard'])
        ->name('dashboard.student')->middleware('role:student');
    
    // Teacher Dashboard
    Route::get('/dashboard/teacher', [DashboardController::class, 'teacherDashboard'])
        ->name('dashboard.teacher')->middleware('role:teacher,guru,admin');
    
    // =================== ASSIGNMENT ROUTES ===================
    Route::prefix('assignments')->name('assignments.')->group(function () {
        // Untuk siswa
        Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
            Route::get('/', [AssignmentController::class, 'indexStudent'])->name('index');
            Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
        });
        
        // Untuk guru/admin
        Route::prefix('teacher')->name('teacher.')->middleware('role:teacher,guru,admin')->group(function () {
            Route::get('/', [AssignmentController::class, 'indexTeacher'])->name('index');
            Route::get('/create', [AssignmentController::class, 'create'])->name('create');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::get('/{assignment}/edit', [AssignmentController::class, 'edit'])->name('edit');
            Route::put('/{assignment}', [AssignmentController::class, 'update'])->name('update');
            Route::delete('/{assignment}', [AssignmentController::class, 'destroy'])->name('destroy');
            
            // Fitur tambahan untuk guru
            Route::post('/{assignment}/extend', [AssignmentController::class, 'extendDeadline'])
                ->name('extend');
            Route::post('/{assignment}/announce', [AssignmentController::class, 'sendAnnouncement'])
                ->name('announce');
            Route::get('/{assignment}/download-all', [AssignmentController::class, 'downloadAllSubmissions'])
                ->name('download-all');
            Route::post('/{assignment}/batch-grade', [AssignmentController::class, 'batchGrade'])
                ->name('batch-grade');
        });
        
        // Route umum (untuk semua role)
        Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
    });
    
    // =================== SUBMISSION ROUTES ===================
    Route::prefix('submissions')->name('submissions.')->group(function () {
        // Untuk siswa (mengumpulkan tugas)
        Route::middleware('role:student')->group(function () {
            Route::post('/{assignment}/submit', [SubmissionController::class, 'submit'])->name('submit');
            Route::post('/{assignment}/resubmit', [SubmissionController::class, 'resubmit'])->name('resubmit');
            Route::post('/{assignment}/save-draft', [SubmissionController::class, 'saveDraft'])->name('save-draft');
        });
        
        // Untuk guru/admin (menilai tugas)
        Route::middleware('role:teacher,guru,admin')->group(function () {
            Route::post('/{submission}/grade', [SubmissionController::class, 'grade'])->name('grade');
            Route::get('/{submission}', [SubmissionController::class, 'show'])->name('show');
            Route::get('/{assignment}/export-grades', [SubmissionController::class, 'exportGrades'])->name('export-grades');
            Route::post('/students/{student}/remind', [SubmissionController::class, 'remindStudent'])->name('remind-student');
        });
        
        // Route untuk mendapatkan submission (AJAX)
        Route::get('/{submission}/get', [SubmissionController::class, 'getSubmission'])
            ->name('get-submission');
    });
    
    // =================== ATTENDANCE ROUTES ===================
    Route::prefix('attendance')->name('attendance.')->middleware('auth')->group(function () {
        
        // ===== SCAN QR CODE ROUTES (SISWA) =====
        Route::middleware('role:student')->group(function () {
            Route::get('/scan-page', [AttendanceController::class, 'scanPage'])->name('scan.page');
            Route::get('/scan/{code}', [AttendanceController::class, 'showConfirm'])->name('scan.confirm');
            Route::post('/scan-process', [AttendanceController::class, 'processScan'])->name('scan.process');
            Route::get('/scan-result/{id}', [AttendanceController::class, 'showResult'])->name('scan.result');
            Route::get('/student', [AttendanceController::class, 'indexStudent'])->name('student.index');
            Route::get('/student/{attendance}', [AttendanceController::class, 'showStudent'])->name('student.show');
        });
        
        // ===== ROUTES UNTUK GURU =====
        Route::middleware('role:teacher,guru,admin')->group(function () {
            // Dashboard
            Route::get('/teacher', [AttendanceController::class, 'indexTeacher'])->name('teacher.index');
            
            // Detail kelas
            Route::get('/teacher/class/{classId}', [AttendanceController::class, 'showClass'])->name('teacher.class.show');
            
            // Mark attendance (AJAX)
            Route::post('/teacher/mark', [AttendanceController::class, 'markAttendance'])->name('teacher.mark');
            
            // Export
            Route::get('/teacher/export', [AttendanceController::class, 'export'])->name('teacher.export');
            
            // Manual entry
            Route::get('/teacher/manual/create', [AttendanceController::class, 'createManual'])->name('teacher.manual.create');
            Route::post('/teacher/manual/store', [AttendanceController::class, 'storeManual'])->name('teacher.manual.store');
            
            // Bulk manual
            Route::get('/teacher/manual/bulk', [AttendanceController::class, 'createBulkManual'])->name('teacher.manual.bulk');
            Route::post('/teacher/manual/bulk/store', [AttendanceController::class, 'storeBulkManual'])->name('teacher.manual.bulk.store');
            
            // Quick generate QR
            Route::post('/teacher/quick-generate/{classId}', [AttendanceController::class, 'quickGenerateQr'])->name('teacher.quick-generate');
            
            // Edit/Delete attendance
            Route::get('/teacher/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('teacher.attendance.edit');
            Route::put('/teacher/attendance/{attendance}', [AttendanceController::class, 'update'])->name('teacher.attendance.update');
            Route::delete('/teacher/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('teacher.attendance.destroy');
            
            // Get class students (AJAX)
            Route::get('/teacher/class-students/{classId}', [AttendanceController::class, 'getClassStudents'])->name('teacher.class-students');
            
            // Realtime
            Route::get('/teacher/realtime/{qrCodeId}', [AttendanceController::class, 'realtimeAttendance'])->name('teacher.realtime');
            Route::get('/teacher/realtime-data/{qrCodeId}', [AttendanceController::class, 'getRealtimeData'])->name('teacher.realtime.data');
            
            // Bulk delete
            Route::post('/teacher/bulk-delete', [AttendanceController::class, 'bulkDelete'])->name('teacher.bulk.delete');
        });
        
        // ===== API ROUTES =====
        Route::post('/api/scan-process', [AttendanceController::class, 'scanProcess'])->name('api.scan-process');
    });
    
    // =================== QR CODE ROUTES ===================
    Route::prefix('qr-codes')->name('qr-codes.')->middleware('role:teacher,guru,admin')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::get('/create', [QrCodeController::class, 'create'])->name('create');
        Route::post('/', [QrCodeController::class, 'store'])->name('store');
        Route::get('/{qrCode}', [QrCodeController::class, 'show'])->name('show');
        Route::get('/{qrCode}/edit', [QrCodeController::class, 'edit'])->name('edit');
        Route::put('/{qrCode}', [QrCodeController::class, 'update'])->name('update');
        Route::post('/{qrCode}/delete', [QrCodeController::class, 'destroy'])->name('delete');
        Route::get('/dashboard', [QrCodeController::class, 'dashboard'])->name('dashboard');
        Route::get('/{qrCode}/download', [QrCodeController::class, 'download'])->name('download');
        Route::post('/{qrCode}/regenerate-image', [QrCodeController::class, 'regenerateImage'])->name('regenerate-image');
        Route::post('/{qrCode}/activate', [QrCodeController::class, 'activate'])->name('activate');
        Route::post('/{qrCode}/deactivate', [QrCodeController::class, 'deactivate'])->name('deactivate');
        Route::post('/preview', [QrCodeController::class, 'preview'])->name('preview');
        Route::get('/active/date', [QrCodeController::class, 'getActiveForDate'])->name('active.date');
        Route::post('/generate-for-class', [QrCodeController::class, 'generateForClass'])->name('generate-for-class');
        Route::post('/quick-generate', [QrCodeController::class, 'quickGenerate'])->name('quick-generate');
        Route::get('/bulk-create', [QrCodeController::class, 'bulkGenerate'])->name('bulk-create');
        Route::post('/bulk-create', [QrCodeController::class, 'bulkGenerate'])->name('bulk-store');
        Route::get('/debug', [QrCodeController::class, 'debug'])->name('debug');
        Route::post('/generate-base64', [QrCodeController::class, 'generateBase64'])->name('generate-base64');
    });
    
    // =================== CLASS ROUTES ===================
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('index');
        
        Route::middleware(['role:teacher,guru,admin'])->group(function () {
            Route::get('/create', [ClassController::class, 'create'])->name('create');
            Route::post('/', [ClassController::class, 'store'])->name('store');
            Route::get('/import', [ClassController::class, 'import'])->name('import');
            Route::post('/import/process', [ClassController::class, 'processImport'])->name('import.process');
            Route::get('/import/template', [ClassController::class, 'downloadTemplate'])->name('import.template');
            Route::post('/{class}/activate', [ClassController::class, 'activate'])->name('activate');
            Route::post('/{class}/deactivate', [ClassController::class, 'deactivate'])->name('deactivate');
            Route::get('/{id}/edit', [ClassController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ClassController::class, 'update'])->name('update');
            Route::delete('/{id}', [ClassController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/add-student', [ClassController::class, 'addStudent'])->name('add-student');
            Route::delete('/{id}/remove-student/{student}', [ClassController::class, 'removeStudent'])->name('remove-student');
            Route::post('/{id}/bulk-add-students', [ClassController::class, 'bulkAddStudents'])->name('bulk-add-students');
            Route::get('/{id}/students', [ClassController::class, 'students'])->name('students');
            Route::get('/{id}/search-students', [ClassController::class, 'searchStudents'])->name('search-students');
            Route::get('/{id}/stats', [ClassController::class, 'stats'])->name('stats');
        });
        
        Route::get('/{id}', [ClassController::class, 'show'])->name('show');
    });
    
    // =================== STUDENT ROUTES ===================
    Route::prefix('students')->name('students.')->middleware('role:teacher,guru,admin')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{student}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        Route::get('/{student}/attendance', [StudentController::class, 'attendance'])->name('attendance');
        Route::get('/search', [StudentController::class, 'search'])->name('search');
        Route::get('/import', [StudentController::class, 'import'])->name('import');
        Route::post('/import/process', [StudentController::class, 'processImport'])->name('import.process');
        Route::get('/import/template', [StudentController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/export/excel', [StudentController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [StudentController::class, 'exportPdf'])->name('export.pdf');
    });
    
    // =================== AJAX API ROUTES ===================
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/classes/{class}/available-students', function($classId) {
            $class = \App\Models\ClassModel::findOrFail($classId);
            $availableStudents = $class->getAvailableStudents();
            return response()->json($availableStudents);
        })->name('classes.available-students');
        
        Route::get('/classes/{class}/student-count', function($classId) {
            $class = \App\Models\ClassModel::findOrFail($classId);
            return response()->json(['count' => $class->students()->count()]);
        })->name('class.student-count');
        
        Route::get('/students/{student}/details', function($studentId) {
            $student = \App\Models\User::findOrFail($studentId);
            return response()->json([
                'id' => $student->id,
                'name' => $student->name,
                'nis_nip' => $student->nis_nip,
                'email' => $student->email,
                'is_active' => $student->is_active
            ]);
        })->name('students.details');
        
        Route::get('/attendance/today-stats', [AttendanceController::class, 'getTodayStats'])->name('attendance.today-stats');
        Route::post('/attendance/scan-process', [AttendanceController::class, 'scanProcess'])->name('attendance.scan-process');
    });
    
    // =================== QUICK GENERATE QR ROUTE ===================
    Route::post('/attendance/quick-generate-qr/{classId}', [AttendanceController::class, 'quickGenerateQr'])
        ->name('attendance.quick-generate-qr')
        ->middleware('role:teacher,guru,admin');
    
    // =================== DEBUG ROUTES ===================
    Route::get('/test-qr/{code}', function($code) {
        $path = storage_path('app/public/qr-codes/' . $code . '.png');
        if (file_exists($path)) {
            return response()->file($path);
        }
        return "File not found: " . $path;
    });
    
    // =================== API CHART DATA ===================
    Route::get('/api/attendance/chart-data', function(Request $request) {
        $user = Auth::user();
        $period = $request->get('period', 7);
        $days = (int)$period;
        
        $labels = [];
        $presentData = [];
        $lateData = [];
        $absentData = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('D, d/m');
            
            $attendanceStats = \App\Models\Attendance::whereHas('class', function($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                })
                ->whereDate('attendance_date', $date)
                ->selectRaw("
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
                ")
                ->first();
            
            $presentData[] = $attendanceStats->present ?? 0;
            $lateData[] = $attendanceStats->late ?? 0;
            $absentData[] = $attendanceStats->absent ?? 0;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'present' => $presentData,
                'late' => $lateData,
                'absent' => $absentData,
            ]
        ]);
    })->middleware('auth')->name('api.attendance.chart');
    
    // =================== ERROR HANDLING ===================
    Route::get('/unauthorized', function () {
        return view('errors.unauthorized');
    })->name('unauthorized');
});

// =================== FALLBACK ROUTE ===================
Route::fallback(function () {
    if (request()->expectsJson() || request()->is('api/*')) {
        return response()->json([
            'success' => false,
            'message' => 'Halaman tidak ditemukan'
        ], 404);
    }
    
    // Cek jika request untuk file gambar di storage
    if (request()->is('storage/*')) {
        // Biarkan web server menangani file statis
        abort(404);
    }
    
    return response()->view('errors.404', [], 404);
});