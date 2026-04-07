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
Route::prefix('attendance')->name('attendance.')->group(function () {
    
    // ===== SCAN QR CODE ROUTES =====
    // Halaman scan QR code untuk siswa
    Route::get('/scan', [AttendanceController::class, 'scanPage'])
        ->name('scan.page')
        ->middleware('role:student');
    
    // Process QR code scan dengan code di URL
    Route::get('/scan/{code}', [AttendanceController::class, 'scanQrCode'])
        ->name('scan.code')
        ->middleware('role:student');
    
    // Process scan form submission
    Route::post('/scan/{code}', [AttendanceController::class, 'processScan'])
        ->name('scan.process')
        ->middleware('role:student');
        
    // Confirm attendance page
    Route::get('/scan/{code}/confirm', [AttendanceController::class, 'showConfirm'])
        ->name('scan.confirm')
        ->middleware('role:student');
    
    // ===== ROUTES UNTUK SEMUA ROLE =====
    // Class attendance (untuk guru melihat absensi kelas)
    Route::get('/class/{class}', [AttendanceController::class, 'classAttendance'])
        ->name('class.show')
        ->middleware('role:teacher,guru,admin');
    
    // Student attendance detail
    Route::get('/student/{class}/{student}', [AttendanceController::class, 'studentAttendance'])
        ->name('student.detail')
        ->middleware('role:teacher,guru,admin');
    
    // ===== ROUTES UNTUK GURU =====
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher,guru,admin')->group(function () {
        // Dashboard attendance guru
        Route::get('/', [AttendanceController::class, 'indexTeacher'])->name('index');
        
        // Class attendance detail (statistik)
        Route::get('/class-detail/{class}', [AttendanceController::class, 'showClass'])->name('class.detail');
        
        // Mark attendance (AJAX)
        Route::post('/mark', [AttendanceController::class, 'markAttendance'])->name('mark');
        
        // Export - ROUTE INI SUDAH ADA
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
        
        // Manual entry
        Route::get('/manual/create', [AttendanceController::class, 'createManual'])->name('manual.create');
        Route::post('/manual/store', [AttendanceController::class, 'storeManual'])->name('manual.store');
        Route::get('/manual', [AttendanceController::class, 'createManual'])->name('manual');
        
        // Edit/delete
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        
        // QR Code related
        Route::get('/qr/{qrCode}', [AttendanceController::class, 'viewQrAttendance'])->name('qr.view');
        
        // Bulk manual attendance
        Route::get('/manual/bulk', [AttendanceController::class, 'createBulkManual'])->name('manual.bulk');
        Route::post('/manual/bulk/store', [AttendanceController::class, 'storeBulkManual'])->name('manual.bulk.store');
        
        // Quick generate QR
        Route::post('/quick-generate/{classId}', [AttendanceController::class, 'quickGenerateQr'])
            ->name('quick-generate');
        
        // Get class students (AJAX)
        Route::get('/class-students/{classId}', [AttendanceController::class, 'getClassStudents'])
            ->name('class-students');
            
        // Realtime attendance monitoring
        Route::get('/realtime/{qrCodeId}', [AttendanceController::class, 'realtimeAttendance'])
            ->name('realtime');
    });
    
    // ===== ROUTES UNTUK SISWA =====
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/', [AttendanceController::class, 'indexStudent'])->name('index');
        Route::get('/{attendance}', [AttendanceController::class, 'showStudent'])->name('show');
        Route::get('/statistics', [AttendanceController::class, 'studentStatistics'])->name('statistics');
    });
});
    
    // =================== QR CODE ROUTES ===================
    Route::prefix('qr-codes')->name('qr-codes.')->middleware('role:teacher,guru,admin')->group(function () {
        // CRUD Routes
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::get('/create', [QrCodeController::class, 'create'])->name('create');
        Route::post('/', [QrCodeController::class, 'store'])->name('store');
        Route::get('/{qrCode}', [QrCodeController::class, 'show'])->name('show');
        Route::get('/{qrCode}/edit', [QrCodeController::class, 'edit'])->name('edit');
        Route::put('/{qrCode}', [QrCodeController::class, 'update'])->name('update');
        Route::delete('/{qrCode}', [QrCodeController::class, 'destroy'])->name('destroy');
        
        // Dashboard
        Route::get('/dashboard', [QrCodeController::class, 'dashboard'])->name('dashboard');
        
        // Download
        Route::get('/{qrCode}/download', [QrCodeController::class, 'download'])->name('download');
        
        // Activation/Deactivation
        Route::post('/{qrCode}/activate', [QrCodeController::class, 'activate'])->name('activate');
        Route::post('/{qrCode}/deactivate', [QrCodeController::class, 'deactivate'])->name('deactivate');
        
        // Preview (AJAX)
        Route::post('/preview', [QrCodeController::class, 'preview'])->name('preview');
        
        // Active for date (AJAX)
        Route::get('/active/date', [QrCodeController::class, 'getActiveForDate'])->name('active.date');
        
        // Generate for class
        Route::post('/generate-for-class', [QrCodeController::class, 'generateForClass'])
            ->name('generate-for-class');
        
        // Quick generate
        Route::post('/quick-generate', [QrCodeController::class, 'quickGenerate'])
            ->name('quick-generate');
        
        // Bulk generate
        Route::get('/bulk-create', [QrCodeController::class, 'bulkGenerate'])->name('bulk-create');
        Route::post('/bulk-create', [QrCodeController::class, 'bulkGenerate'])->name('bulk-store');
        
        // Debug
        Route::get('/debug', [QrCodeController::class, 'debug'])->name('debug');
        
        // Generate base64 (API)
        Route::post('/generate-base64', [QrCodeController::class, 'generateBase64'])->name('generate-base64');
    });
    
    // =================== CLASS ROUTES ===================
    Route::prefix('classes')->name('classes.')->group(function () {
        // Routes untuk semua user yang memiliki akses
        Route::get('/', [ClassController::class, 'index'])->name('index');
        
        // Routes hanya untuk teacher/guru/admin
        Route::middleware(['auth', 'role:teacher,guru,admin'])->group(function () {
            Route::get('/create', [ClassController::class, 'create'])->name('create');
            Route::post('/', [ClassController::class, 'store'])->name('store');
        });
        
        // Route dengan parameter HARUS DITEMPATKAN TERAKHIR
        Route::get('/{id}', [ClassController::class, 'show'])->name('show');
        
        // Routes untuk edit/update/delete (hanya teacher/admin)
        Route::middleware(['auth', 'role:teacher,guru,admin'])->group(function () {
            Route::get('/{id}/edit', [ClassController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ClassController::class, 'update'])->name('update');
            Route::delete('/{id}', [ClassController::class, 'destroy'])->name('destroy');
            
            // Student management
            Route::post('/{id}/add-student', [ClassController::class, 'addStudent'])->name('add-student');
            Route::delete('/{id}/remove-student/{student}', [ClassController::class, 'removeStudent'])->name('remove-student');
            Route::post('/{id}/bulk-add-students', [ClassController::class, 'bulkAddStudents'])->name('bulk-add-students');
            
            // Student list view
            Route::get('/{id}/students', [ClassController::class, 'students'])->name('students');
            
            // Activation
            Route::post('/{id}/activate', [ClassController::class, 'activate'])->name('activate');
            Route::post('/{id}/deactivate', [ClassController::class, 'deactivate'])->name('deactivate');
            
            // Search students (AJAX)
            Route::get('/{id}/search-students', [ClassController::class, 'searchStudents'])->name('search-students');
            
            // Statistics (AJAX)
            Route::get('/{id}/stats', [ClassController::class, 'stats'])->name('stats');
        });
    });
    
    // =================== STUDENT ROUTES ===================
    Route::prefix('students')->name('students.')->middleware('role:teacher,guru,admin')->group(function () {
        // CRUD routes
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{student}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        
        // Attendance routes
        Route::get('/{student}/attendance', [StudentController::class, 'attendance'])->name('attendance');
        
        // AJAX routes
        Route::get('/search', [StudentController::class, 'search'])->name('search');
        
        // IMPORT ROUTES (opsional - uncomment jika diperlukan)
        Route::get('/import', [StudentController::class, 'import'])->name('import');
        Route::post('/import/process', [StudentController::class, 'processImport'])->name('import.process');
        
        // EXPORT ROUTES (opsional)
        Route::get('/export/excel', [StudentController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [StudentController::class, 'exportPdf'])->name('export.pdf');
    });
    
    // =================== AJAX API ROUTES ===================
    Route::prefix('api')->name('api.')->group(function () {
        // Class API
        Route::get('/classes/{class}/available-students', function($classId) {
            $class = \App\Models\ClassModel::findOrFail($classId);
            $availableStudents = $class->getAvailableStudents();
            
            return response()->json($availableStudents);
        })->name('classes.available-students');
        
        Route::get('/classes/{class}/student-count', function($classId) {
            $class = \App\Models\ClassModel::findOrFail($classId);
            return response()->json(['count' => $class->students()->count()]);
        })->name('class.student-count');
        
        // Student API
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
        
        // QR Code API
        Route::get('/qr-codes/active', [QrCodeController::class, 'apiActive'])->name('qr-codes.active');
        Route::get('/qr-codes/stats', [QrCodeController::class, 'apiStats'])->name('qr-codes.stats');
        
        // Attendance API
        Route::get('/attendance/today-stats', [AttendanceController::class, 'getTodayStats'])
            ->name('attendance.today-stats');
            
        // Real-time attendance API
        Route::get('/attendance/realtime/{qrCodeId}', [AttendanceController::class, 'getRealtimeData'])
            ->name('attendance.realtime');
            
        // Scan process API
        Route::post('/attendance/scan-process', [AttendanceController::class, 'scanProcess'])
            ->name('attendance.scan-process');
    });
    
    // =================== DEBUG & UTILITY ROUTES ===================
    Route::middleware(['auth', 'role:teacher,guru,admin'])->group(function () {
        Route::get('/debug/test-attendance/{code}', function($code) {
            $qrCode = \App\Models\QRCode::where('code', $code)->first();
            
            if (!$qrCode) {
                return "QR Code tidak ditemukan";
            }
            
            return view('debug.test-attendance', compact('qrCode'));
        });
        
        Route::get('/attendance/scan-error', [AttendanceController::class, 'showScanError'])
            ->name('attendance.scan.error');
    });
    
    // =================== ERROR HANDLING ===================
    Route::get('/unauthorized', function () {
        return view('errors.unauthorized');
    })->name('unauthorized');
});

// =================== FALLBACK ROUTE ===================
Route::fallback(function () {
    return view('errors.404');
});